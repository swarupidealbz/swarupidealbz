<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PeppertypeAuthentication;
use App\Models\PeppertypeRequestData;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PeppertypeController extends BaseController
{
    const PEPPERTYPE_IDEA_CTYPEID = '963dcb33-294d-4333-a5e3-743628dd8123';
    const PEPPERTYPE_INTRO_CTYPEID = '2b5f8679-71cb-4a97-9f95-94f74baaedba';
    const PEPPERTYPE_OUTLINE_CTYPEID = 'dc45b8aa-cb45-4c7c-ac3d-3f33d057b201';
    const PEPPERTYPE_CONCLUSION_CTYPEID = '1671a1f2-f267-454a-845b-cba2c6933978';

    public function request($uri = '', $method = 'get', $param = [], $isToken = false, $token = '')
    {
        try {                
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; rv:94.0) Gecko/20100101 Firefox/94.0',
                'Referer' => 'https://app.peppertype.ai',
                'Origin' => 'https://app.peppertype.ai',
                'Content-Type' => 'application/json',
            ];

            $request = Http::withHeaders($headers);
            if($isToken) {
                $request = $request->withToken($token);
            }

            if($method == 'post') {
                $result = $request->post($uri,                 
                    $param
                );
            }
            elseif($method == 'get') {
                $result = $request->get($uri,                 
                    $param
                );
            }        

            return $result;
        }
        catch(Exception $e) 
        {
            logger('request method error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function login()
    {       
        try {                
            $loginUser = Auth::user(); 
            $credentials = [
                'username' => env('PEPPERTYPE_USERNAME'),
                'password' => env('PEPPERTYPE_PASSWORD')
            ];
            $response = $this->request('https://api.peppertype.ai/auth/login', 'post', $credentials);      

            if($response->successful()) {
                $result = json_decode($response->getBody(), true);
                $time = Carbon::now()->toDateTimeString();
                $insertData = [
                    'username' => env('PEPPERTYPE_USERNAME'),
                    'idToken' => $result['data']['idToken'],
                    'refreshToken' => $result['data']['refreshToken'],
                    'accessToken' => $result['data']['accessToken'],
                    'response' => json_encode($result),
                    'user_id' => $loginUser->id,
                    'created_at' => $time,
                    'updated_at' => $time
                ];
                PeppertypeAuthentication::create($insertData);
            }
            //['status' => '', 'message' => '', 'data' => ['idToken' => 'send this token', 'refreshToken' => '', 'accessToken' => '']]
        }
        catch(Exception $e) 
        {
            logger('peppertype login error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function createIdeas(Request $request)
    {
        try {
            $input = $request->only('product_name', 'product_description');
            
            $validator = Validator::make($input,['product_name' => 'required', 'product_description' => 'required']);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            
            $loginUser = Auth::user();
            $titleUri = 'https://api.peppertype.ai/gpt/titles?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $uri = 'https://api.peppertype.ai/gpt/engines/davinci/suggestions?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $tokenRow = PeppertypeAuthentication::where('username', 'like', env('PEPPERTYPE_USERNAME'))->latest()->first();
            $token = $tokenRow->idToken;
            $titleParam = ['text' => $request->product_name];
            $titleId = '';
            //fetch title record
            $titleResponse = $this->request(
                $titleUri,
                'get',
                [],
                true,
                $token
            );
            // check if authorization failed
            if($titleResponse->status() == 401 ) {
                $this->login();
                $titleResponse = $this->request(
                    $titleUri,
                    'get',
                    [],
                    true,
                    $token
                );
            }
            if($titleResponse->successful()) {
                $titleFetchResult = json_decode($titleResponse->getBody(), true);
                $sk = '';
                //if title matched from record
                foreach($titleFetchResult['data'] as $titleObject) {
                    if($titleObject['text'] == $request->product_name) {
                        $sk = $titleObject['sk'];
                    }
                }
                //if title not matched from record then create new
                if(blank($sk)) {
                    $titleResponse = $this->request(
                        $titleUri,
                        'post',
                        $titleParam,
                        true,
                        $token
                    );
                    $titleResult = json_decode($titleResponse->getBody(), true);
                    $sk = $titleResult['data']['sk'];
                }
                $titleId = str_replace('TITLE#', '', $sk);
            }
            if($titleId) {
                
                $parameters = [
                    'description' => [
                        $request->product_description
                    ]
                ];
                $param = [
                    'cTypeId' => self::PEPPERTYPE_IDEA_CTYPEID,
                    'product' => $request->product_name,
                    'parameters' => $parameters,
                    'titleId' => $titleId
                ];
                $ideaResponse = $this->request(
                    $uri,
                    'post',
                    $param,
                    true,
                    $token
                );
                if($ideaResponse->successful()) {
                    $ideaResult = json_decode($ideaResponse->getBody(), true);
                    $time = Carbon::now()->toDateTimeString();
                    $insertData = [
                        'username' => env('PEPPERTYPE_USERNAME'),
                        'ideas_product_name' => $request->product_name,
                        'ideas_product_description' => $request->product_description,
                        'user_id' => $loginUser->id,
                        'created_at' => $time,
                        'updated_at' => $time
                    ];
                    $pepper = PeppertypeRequestData::create($insertData);
                    $response = [
                        'pepper_id' => $pepper->id,
                        'response' => $ideaResult
                    ];
                    return $this->handleResponse($response, 'Success');
                }
                return $this->handleError('Blog ideas could not created', [], 500);
            }
            return $this->handleError('Title could not created', [], 500);
        }
        catch(Exception $e) 
        {
            logger('create ideas error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function createIntro(Request $request)
    {
        try {
            $input = $request->only('product_name', 'product_description', 'pepper_id');
            
            $validator = Validator::make($input,
            [
                'product_name' => 'required', 
                'product_description' => 'required',
                'pepper_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            
            $loginUser = Auth::user();
            $titleUri = 'https://api.peppertype.ai/gpt/titles?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $uri = 'https://api.peppertype.ai/gpt/engines/davinci/suggestions?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $tokenRow = PeppertypeAuthentication::where('username', 'like', env('PEPPERTYPE_USERNAME'))->latest()->first();
            $token = $tokenRow->idToken;
            $titleParam = ['text' => $request->product_name];
            $titleId = '';
            //fetch title record
            $titleResponse = $this->request(
                $titleUri,
                'get',
                [],
                true,
                $token
            );
            // check if authorization failed
            if($titleResponse->status() == 401 ) {
                $this->login();
                $titleResponse = $this->request(
                    $titleUri,
                    'get',
                    [],
                    true,
                    $token
                );
            }
            if($titleResponse->successful()) {
                $titleFetchResult = json_decode($titleResponse->getBody(), true);
                $sk = '';
                //if title matched from record
                foreach($titleFetchResult['data'] as $titleObject) {
                    if($titleObject['text'] == $request->product_name) {
                        $sk = $titleObject['sk'];
                    }
                }
                //if title not matched from record then create new
                if(blank($sk)) {
                    $titleResponse = $this->request(
                        $titleUri,
                        'post',
                        $titleParam,
                        true,
                        $token
                    );
                    $titleResult = json_decode($titleResponse->getBody(), true);
                    $sk = $titleResult['data']['sk'];
                }
                $titleId = str_replace('TITLE#', '', $sk);
            }
            if($titleId) {
                
                $parameters = [
                    'description' => [
                        $request->product_description
                    ]
                ];
                $param = [
                    'cTypeId' => self::PEPPERTYPE_INTRO_CTYPEID,
                    'product' => $request->product_name,
                    'parameters' => $parameters,
                    'titleId' => $titleId
                ];
                $ideaResponse = $this->request(
                    $uri,
                    'post',
                    $param,
                    true,
                    $token
                );
                if($ideaResponse->successful()) {
                    $ideaResult = json_decode($ideaResponse->getBody(), true);
                    $time = Carbon::now()->toDateTimeString();
                    $insertData = [
                        'intro_product_name' => $request->product_name,
                        'intro_product_description' => $request->product_description,
                        'updated_at' => $time
                    ];
                    $pepper = PeppertypeRequestData::where('id', $request->pepper_id)->update($insertData);
                    $response = [
                        'pepper_id' => $request->pepper_id,
                        'response' => $ideaResult
                    ];
                    return $this->handleResponse($response, 'Success');
                }
                return $this->handleError('Blog intro could not created', [], 500);
            }
            return $this->handleError('Title could not created', [], 500);
        }
        catch(Exception $e) 
        {
            logger('create intro error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function createOutline(Request $request)
    {
        try {
            $input = $request->only('product_name', 'product_description', 'pepper_id');
            
            $validator = Validator::make($input,
            [
                'product_name' => 'required', 
                'product_description' => 'required',
                'pepper_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            
            $loginUser = Auth::user();
            $titleUri = 'https://api.peppertype.ai/gpt/titles?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $uri = 'https://api.peppertype.ai/gpt/engines/davinci/suggestions?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $tokenRow = PeppertypeAuthentication::where('username', 'like', env('PEPPERTYPE_USERNAME'))->latest()->first();
            $token = $tokenRow->idToken;
            $titleParam = ['text' => $request->product_name];
            $titleId = '';
            //fetch title record
            $titleResponse = $this->request(
                $titleUri,
                'get',
                [],
                true,
                $token
            );
            // check if authorization failed
            if($titleResponse->status() == 401 ) {
                $this->login();
                $titleResponse = $this->request(
                    $titleUri,
                    'get',
                    [],
                    true,
                    $token
                );
            }
            if($titleResponse->successful()) {
                $titleFetchResult = json_decode($titleResponse->getBody(), true);
                $sk = '';
                //if title matched from record
                foreach($titleFetchResult['data'] as $titleObject) {
                    if($titleObject['text'] == $request->product_name) {
                        $sk = $titleObject['sk'];
                    }
                }
                //if title not matched from record then create new
                if(blank($sk)) {
                    $titleResponse = $this->request(
                        $titleUri,
                        'post',
                        $titleParam,
                        true,
                        $token
                    );
                    $titleResult = json_decode($titleResponse->getBody(), true);
                    $sk = $titleResult['data']['sk'];
                }
                $titleId = str_replace('TITLE#', '', $sk);
            }
            if($titleId) {
                
                $parameters = [
                    'description' => [
                        $request->product_description
                    ]
                ];
                $param = [
                    'cTypeId' => self::PEPPERTYPE_OUTLINE_CTYPEID,
                    'product' => $request->product_name,
                    'parameters' => $parameters,
                    'titleId' => $titleId
                ];
                $ideaResponse = $this->request(
                    $uri,
                    'post',
                    $param,
                    true,
                    $token
                );
                if($ideaResponse->successful()) {
                    $ideaResult = json_decode($ideaResponse->getBody(), true);
                    $time = Carbon::now()->toDateTimeString();
                    $insertData = [
                        'outline_product_name' => $request->product_name,
                        'outline_product_description' => $request->product_description,
                        'updated_at' => $time
                    ];
                    $pepper = PeppertypeRequestData::where('id', $request->pepper_id)->update($insertData);
                    $response = [
                        'pepper_id' => $request->pepper_id,
                        'response' => $ideaResult
                    ];
                    return $this->handleResponse($response, 'Success');
                }
                return $this->handleError('Blog outline could not created', [], 500);
            }
            return $this->handleError('Title could not created', [], 500);
        }
        catch(Exception $e) 
        {
            logger('create outline error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function createConclusion(Request $request)
    {
        try {
            $input = $request->only('product_name', 'product_description', 'pepper_id');
            
            $validator = Validator::make($input,
            [
                'product_name' => 'required', 
                'product_description' => 'required',
                'pepper_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            
            $loginUser = Auth::user();
            $titleUri = 'https://api.peppertype.ai/gpt/titles?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $uri = 'https://api.peppertype.ai/gpt/engines/davinci/suggestions?teamId=857d8401-a161-4831-b4a0-68994706047b';
            $tokenRow = PeppertypeAuthentication::where('username', 'like', env('PEPPERTYPE_USERNAME'))->latest()->first();
            $token = $tokenRow->idToken;
            $titleParam = ['text' => $request->product_name];
            $titleId = '';
            //fetch title record
            $titleResponse = $this->request(
                $titleUri,
                'get',
                [],
                true,
                $token
            );
            // check if authorization failed
            if($titleResponse->status() == 401 ) {
                $this->login();
                $titleResponse = $this->request(
                    $titleUri,
                    'get',
                    [],
                    true,
                    $token
                );
            }
            if($titleResponse->successful()) {
                $titleFetchResult = json_decode($titleResponse->getBody(), true);
                $sk = '';
                //if title matched from record
                foreach($titleFetchResult['data'] as $titleObject) {
                    if($titleObject['text'] == $request->product_name) {
                        $sk = $titleObject['sk'];
                    }
                }
                //if title not matched from record then create new
                if(blank($sk)) {
                    $titleResponse = $this->request(
                        $titleUri,
                        'post',
                        $titleParam,
                        true,
                        $token
                    );
                    $titleResult = json_decode($titleResponse->getBody(), true);
                    $sk = $titleResult['data']['sk'];
                }
                $titleId = str_replace('TITLE#', '', $sk);
            }
            if($titleId) {
                
                $parameters = [
                    'description' => [
                        $request->product_description
                    ]
                ];
                $param = [
                    'cTypeId' => self::PEPPERTYPE_CONCLUSION_CTYPEID,
                    'product' => $request->product_name,
                    'parameters' => $parameters,
                    'titleId' => $titleId
                ];
                $ideaResponse = $this->request(
                    $uri,
                    'post',
                    $param,
                    true,
                    $token
                );
                if($ideaResponse->successful()) {
                    $ideaResult = json_decode($ideaResponse->getBody(), true);
                    $time = Carbon::now()->toDateTimeString();
                    $insertData = [
                        'conclusion_product_name' => $request->product_name,
                        'conclusion_product_description' => $request->product_description,
                        'updated_at' => $time
                    ];
                    $pepper = PeppertypeRequestData::where('id', $request->pepper_id)->update($insertData);
                    $response = [
                        'pepper_id' => $request->pepper_id,
                        'response' => $ideaResult
                    ];
                    return $this->handleResponse($response, 'Success');
                }
                return $this->handleError('Blog conclusion could not created', [], 500);
            }
            return $this->handleError('Title could not created', [], 500);
        }
        catch(Exception $e) 
        {
            logger('create conclusion error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }
    
}
