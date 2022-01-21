<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Topics;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChildTopicController extends BaseController
{
    //approved, rejected, open
    public function index()
    {
        try {
                
            $list = Topics::where('is_primary_topic', 0)->get();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('child topic list error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Topics $childTopic)
    {
        try {
                
            if(empty($childTopic)) {
                return $this->handleError([], 'Data not found', 404);
            }
            return $this->handleResponse($childTopic, 'Success');
        }
        catch(Exception $e) 
        {
            logger('child topic show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function childTopicByRole(Request $request)
    {
        try {
                
            $website = $primaryTopic = '';
            if($request->website) {
                $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            }
            if($request->primary_topic) {
                $primaryTopic = is_array($request->primary_topic) ? $request->primary_topic : explode(',',$request->primary_topic);
            }
            $loginUser = Auth::user();
            if($loginUser->role == 'client') {
                $topicList = Topics::where('is_primary_topic', 0)->where('status', 'like', Topics::STATUS_APPROVED);
                if($website) {
                    $topicList = $topicList->whereIn('website_id', $website);
                }
                if($primaryTopic) {
                    $topicList = $topicList->whereIn('primary_topic_id', $primaryTopic);
                }
                $topicList = $topicList->get();
            }
            else {
                $topicList = Topics::where('is_primary_topic', 0)->where('status', 'like', Topics::STATUS_APPROVED)->get();
            }

            return $this->handleResponse($topicList, 'Fetched matched lists.');
        }
        catch(Exception $e) 
        {
            logger('child topic by role error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function openChildTopicByRole(Request $request)
    {
        try {
                
            $website = $primaryTopic = '';
            if($request->website) {
                $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            }
            if($request->primary_topic) {
                $primaryTopic = is_array($request->primary_topic) ? $request->primary_topic : explode(',',$request->primary_topic);
            }
            $loginUser = Auth::user();
            if($loginUser->role == 'client') {
                $topicList = Topics::where('is_primary_topic', 0)->where('status', 'like', Topics::STATUS_OPEN);
                if($website) {
                    $topicList = $topicList->whereIn('website_id', $website);
                }
                if($primaryTopic) {
                    $topicList = $topicList->whereIn('primary_topic_id', $primaryTopic);
                }
                $topicList = $topicList->get();
            }
            else {
                $topicList = Topics::where('is_primary_topic', 0)->where('status', 'like', Topics::STATUS_OPEN)->get();
            }

            return $this->handleResponse($topicList, 'Fetched matched lists.');
        }
        catch(Exception $e) 
        {
            logger('open child topic by role error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function childTopicByWebsite(Request $request)
    {
        try {
                
            $input = $request->only('website');
                
            $validator = Validator::make($input,['website' => 'required']);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            
            $loginUser = Auth::user();
            $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            if($loginUser->role == 'client') {
                $topicList = Topics::where('is_primary_topic', 0)->whereIn('website_id', $website);
                if($request->status) {
                    $topicList = $topicList->where('status', 'like', trim($request->status));
                }
                $topicList = $topicList->get();
            }
            else {
                $topicList = Topics::where('is_primary_topic', 0)->whereNotNull('website_id');
                if($request->status) {
                    $topicList = $topicList->where('status', 'like', trim($request->status));
                }
                $topicList = $topicList->get();
            }

            return $this->handleResponse($topicList, 'Fetched matched website lists.');
        }
        catch(Exception $e) 
        {
            logger('child topic by website error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function childTopicByPrimary(Request $request)
    {
        try {
                
            $input = $request->only('website', 'primary_topic');
                
            $validator = Validator::make($input,['website' => 'required', 'primary_topic' => 'required']);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            
            $loginUser = Auth::user();
            $website = is_array($request->website) ? $request->website : explode(',',$request->website);
            $primaryTopic = is_array($request->primary_topic) ? $request->primary_topic : explode(',',$request->primary_topic);
            if($loginUser->role == 'client') {
                $topicList = Topics::where('is_primary_topic', 0)->whereIn('website_id', $website)
                ->whereIn('primary_topic_id', $primaryTopic);
                if($request->status) {
                    $topicList = $topicList->where('status', 'like', trim($request->status));
                }
                $topicList = $topicList->get();
            }
            else {
                $topicList = Topics::where('is_primary_topic', 0)->get();
            }
            $primaryTopicDetails = Topics::find($request->primary_topic);
            $response = [
                'primary_topic' => $primaryTopicDetails,
                'child_topics' =>$topicList
            ];

            return $this->handleResponse($response, 'Fetched matched website lists.');
        }
        catch(Exception $e) 
        {
            logger('child topic by primary error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
                
            $loginUser = Auth::user();
            $input = $request->only('child_topic', 'status');
                
            $validator = Validator::make($input,[
                'child_topic' => 'required|integer', 
                'status' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            $childTopic = Topics::where('is_primary_topic', 0)->find($request->child_topic);
            $status = strtolower(trim($request->status));

            if(empty($childTopic)) {
                return $this->handleError('Invalid child topic', $validator->errors()->all(), 403);
            }
            if(!in_array($status, [Topics::STATUS_APPROVED, Topics::STATUS_OPEN, Topics::STATUS_REJECTED])) {
                return $this->handleError('Invalid status to update child topic', $validator->errors()->all(), 403);
            }

            $childTopic->status = $status;
            $childTopic->updated_by_id = $loginUser->id;
            $childTopic->updated_at = Carbon::now()->toDateTimeString();
            $childTopic->save();

            return $this->handleResponse($childTopic->fresh(), 'Success');
        }
        catch(Exception $e) 
        {
            logger('update status error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }
}
