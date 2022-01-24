<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\Topics;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Websites;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PrimaryTopicController extends BaseController
{
    public function index()
    {
        try {                
            $list = Topics::where('is_primary_topic', 1)->get();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('primary topic list error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Topics $primaryTopic)
    {
        try {
                
            if(empty($primaryTopic)) {
                return $this->handleError([], 'Data not found', 404);
            }
            if(!optional($primaryTopic)->is_primary_topic) {
                return $this->handleError([], 'Primary Data not found', 404);
            }
            return $this->handleResponse($primaryTopic, 'Success');
        }
        catch(Exception $e) 
        {
            logger('primary topic show error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }
	
	public function create(Request $request)
    {
        try {
                
            $user = Auth::user();
            $time = now()->toDateTimeString();
            $input = $request->only('website', 'is_primary', 'topic_name', 'description', 'image');
                    
            $validator = Validator::make($input,[
                'website' => 'required',
                'is_primary' => 'required',
                'topic_name' => 'required',
                'description' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            ]);
			
			if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $imageName = time().'.'.$request->image->extension(); 
            $path = $request->image->move(public_path('images'), $imageName); //public/images/filename
            $inputData = [
                'website_id' => $request->website,
                'is_primary' => $request->is_primary,
                'topic' => $request->topic_name,
                'description' => $request->description,
                'image' => $request->$path,
                'created_by_id' => $user->id,
                'updated_by_id' => $user->id,
                'created_at' => $time,
                'updated_at' => $time
            ];

            $topic = Topics::create($inputData);

            return $this->handleResponse($topic, 'Topic created successfully.');
        }
        catch(Exception $e) 
        {
            logger('create topic show error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function primaryTopicByRole()
    {
        try {
                
            $loginUser = Auth::user();
            if($loginUser->role == 'client') {
                $topicList = Topics::where('is_primary_topic', 1)->whereHas('website',function($website) use($loginUser){
                    $owner = is_array($website->owners) ? $website->owners : json_decode($website->owners, true);
                    return in_array($loginUser->id, $owner);
                })->get();
            }
            else {
                $topicList = Topics::where('is_primary_topic', 1)->get();
            }

            return $this->handleResponse($topicList, 'Fetched matched lists.');
        }
        catch(Exception $e) 
        {
            logger('primary topic by role error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }   
    }

    public function primaryTopicByWebsite(Request $request)
    {
        try {
                
            $input = $request->only('website');
                
            $validator = Validator::make($input,['website' => 'required']);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $loginUser = Auth::user();
            $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            //if($loginUser->role == 'client') {
                $topicList = Topics::where('is_primary_topic', 1)->whereIn('website_id', $website)->with(['groups.group'])->get()
				->map(function($topic) use($loginUser){
					if($topic->usersFavorite()->where(['user_id' => $loginUser->id])->exists()) {
						$topic->is_favorite = true;
					}
					else {
                        $topic->is_favorite = false; 
                    }
					return $topic;
				});
				
				$selectedGroups = [
                    Topics::STATUS_APPROVED => [
                        'id' => Topics::STATUS_APPROVED,
                        'name' => ucwords(Topics::STATUS_APPROVED),
                    ],
                    Topics::STATUS_REJECTED => [
                        'id' => Topics::STATUS_REJECTED,
                        'name' => ucwords(Topics::STATUS_REJECTED)
                    ]
                ];
                $topicList->each(function($topic) use(&$selectedGroups){
                    $topic->groups->each(function($group) use(&$selectedGroups){
                        $selectedGroups[$group->group_id] = $group->group;
                    });
                });
                $response = [
                    'topics' => $topicList,
                    'groups' => $selectedGroups
                ];
            /*}
            else {
                $topicList = Topics::where('is_primary_topic', 1)->get()->map(function($topic) use($loginUser){
					if($topic->usersFavorite()->where(['user_id' => $loginUser->id])->exists()) {
						$topic->is_favorite = true;
					}
					return $topic;
				});
            }*/

            return $this->handleResponse($response, 'Fetched matched website lists.');
        }
        catch(Exception $e) 
        {
            logger('primary topic by website error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function primaryTopicByUser(Request $request)
    {
        try {
             
            $loginUser = Auth::user();
            $listWebsite = Websites::get()->filter(function($q) use($loginUser){
                $owner = is_array($q->owners) ? $q->owners : explode(',', $q->owners);
                return in_array($loginUser->id, $owner);
            });
            $website = [$listWebsite->first()->id];
            if($request->website) {
                $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            }
            $topicList = Topics::where('is_primary_topic', 1)->whereIn('website_id', $website)->get()
			->map(function($topic) use($loginUser){
					if($topic->usersFavorite()->where(['user_id' => $loginUser->id])->exists()) {
						$topic->is_favorite = true;
					}
					else {
                        $topic->is_favorite = false; 
                    }
					return $topic;
				});
            
            $response = [
                'websites' => $listWebsite,
                'topics' => $topicList
            ];

            return $this->handleResponse($response, 'Fetched matched primary topic lists.');
        }
        catch(Exception $e) 
        {
           logger('primary topic by user error: '.$e->getMessage());
           return $this->handleError('Something went wrong', [], 500);
        }
    }
	
	public function updateStatus(Request $request)
    {
        try {
                
            $loginUser = Auth::user();
            $input = $request->only('website', 'topic', 'status');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer',
                'topic' => 'required', 
                'status' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
			$requestTopic = explode(',', $request->topic);
            $topics = Topics::where(['website_id' => $request->website])->whereIn('id', $requestTopic)
            ->get();
            $status = strtolower(trim($request->status));

            if($topics->count() == 0) {
                return $this->handleError('Invalid topic selection', $validator->errors()->all(), 403);
            }
            if(!in_array($status, [Topics::STATUS_APPROVED, Topics::STATUS_OPEN, Topics::STATUS_REJECTED])) {
                return $this->handleError('Invalid status to update topic', $validator->errors()->all(), 403);
            }

            Topics::where(['website_id' => $request->website])->whereIn('id', $requestTopic)->update(['status' => $status]);

            return $this->handleResponse($topics->fresh(), 'Successfully updated.');
        }
        catch(Exception $e) 
        {
            logger('update status error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }
	
	public function topicBySort(Request $request)
    {
        try {
                
            $input = $request->only('website');
                
            $validator = Validator::make($input,['website' => 'required']);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $loginUser = Auth::user();
            $sort = $request->order;
            $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            $topicList = Topics::where('is_primary_topic', 1)->whereIn('website_id', $website)
            ->when($sort, function($q) use($sort) {
                if(in_array($sort, [Topics::STATUS_APPROVED, Topics::STATUS_REJECTED])) {
                    return $q->where('status', strtolower($sort));
                }
                else {
                    return $q->whereHas('groups', function($groups) use($sort){
                        return $groups->where('group_id', $sort);
                    });
                }
            })->get();
           
            return $this->handleResponse($topicList, 'Fetched matched website lists.');
        }
        catch(Exception $e) 
        {
            logger('topic by sort param error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }
	
	public function favoriteTopicList(Request $request)
    {
        try {
                
            $input = $request->only('website');
                
            $validator = Validator::make($input,['website' => 'required']);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $loginUser = Auth::user();
            $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            $topicList = Topics::whereIn('website_id', $website)
			->whereHas('usersFavorite', function($fav)use($loginUser){
				return $fav->where('user_id', $loginUser->id);
			})->get();

            return $this->handleResponse($topicList, 'Fetched matched website lists.');
        }
        catch(Exception $e) 
        {
            logger('topic by favorite error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }
}
