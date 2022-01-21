<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Topics;
use App\Models\Comments;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContentController extends BaseController
{
    //approved, rejected, open, work in progress
    //outline, article

    public function index()
    {
        try {                
            $list = Content::all();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('content list error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Content $content)
    {
        try {                
            if(empty($content)) {
                return $this->handleError([], 'Data not found', 404);
            }
            return $this->handleResponse($content, 'Success');
        }
        catch(Exception $e) 
        {
            logger('content show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function contentByStatus(Request $request)
    {
        try {
            $lists = Content::query();
            if($request->website) {
                $lists = $lists->whereWebsiteId(trim($request->website));
            }
            if($request->primary_topic) {
                $lists = $lists->where('primary_topic_id', trim($request->primary_topic));
            }
            if($request->child_topic) {
                $lists = $lists->where('child_topic_id', trim($request->child_topic));
            }
            if($request->user) {
                $lists = $lists->where('user_id', trim($request->user));
            }
            if($request->content_type) {
                $lists = $lists->where('content_type', trim($request->content_type));
            }
            if($request->status) {
                $lists = $lists->where('status', trim($request->status));
            }
            $lists = $lists->get();

            
            return $this->handleResponse($lists, 'Success');            
        }
        catch(Exception $e) 
        {
            logger('content list by status error');
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function updateStatus(Request $request)
    {
        try {
                
            $input = $request->only('content', 'status');
                
            $validator = Validator::make($input,[
                'content' => 'required|integer', 
                'status' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }
            $content = Content::find($request->content);
            $status = strtolower(trim($request->status));

            if(empty($content)) {
                return $this->handleError('Invalid content', $validator->errors()->all(), 403);
            }

            $content->status = $status;
            $content->save();

            return $this->handleResponse($content->fresh(), 'Success');
        }
        catch(Exception $e) 
        {
            logger('update status error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function contentForTimeline(Request $request)
    {
        try {

            $input = $request->only('website', 'primary_topic', 'child_topic');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer', 
                'primary_topic' => 'required|integer',
                'child_topic' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $contentLists = Content::where('primary_topic_id', trim($request->primary_topic))
            ->where('website_id',trim($request->website))
            ->where('child_topic_id', trim($request->child_topic))
            ->get();
            
            $commentLists = Comments::where('primary_topic_id', trim($request->primary_topic))
            ->where('website_id',trim($request->website))
            ->where('child_topic_id', trim($request->child_topic))
            ->get();

            $timeline = [
                'contents' => $contentLists,
                'comments' => $commentLists
            ];
            
            return $this->handleResponse($timeline, 'Fetched matched record.');            
        }
        catch(Exception $e) 
        {
            logger('content list for timeline error');
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function contentShowByRole(Content $content)
    {
        try {   
            $loginUser = Auth::user();             
            if(empty($content)) {
                return $this->handleError('Data not found', [], 404);
            }
            if(($loginUser->role == User::ROLE_WRITER) && !in_array($content->status, [Content::STATUS_OPEN, Content::STATUS_WORKIN_PROGRESS])) {
                return $this->handleError('You are not permitted to view this content', [], 403);
            }
            elseif(($loginUser->role == User::ROLE_CLIENT) && !in_array($content->status, [Content::STATUS_APPROVED])) {
                return $this->handleError('You are not permitted to view this content', [], 403);
            }
            return $this->handleResponse($content, 'Success');
        }
        catch(Exception $e) 
        {
            logger('content show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function reviewContent(Request $request)
    {
        try {
            $loginUser = Auth::user();
            $input = $request->only('website', 'primary_topic', 'child_topic', 'content_type', 'action', 'content_id');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer',
                'primary_topic' => 'required|integer',
                'child_topic' => 'required|integer',
                'content_type' => 'required',
                'action' => 'required',
                'content_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $childTopic = Topics::where('is_primary_topic', 0)->where('website_id', $request->website)
            ->where('primary_topic_id', $request->primary_topic)
            ->where('id', $request->child_topic)
            ->first();

            $contentDetails = Content::find($request->content_id);

            if(($childTopic->content()->latest()->first()->id != $contentDetails->id) && ($loginUser->role != User::ROLE_CLIENT)) {
                return $this->handleError('You are not permitted to update status to this content.', [], 403);
            }

            if($request->action == 'approve') {
                $contentDetails->status = Content::STATUS_APPROVED;
                $contentDetails->updated_by_id = $loginUser->id;
                $contentDetails->updated_at = Carbon::now()->toDateTimeString();
                $contentDetails->save();
            }
            elseif($request->action == 'reject') {
                $contentDetails->status = Content::STATUS_APPROVED;
                $contentDetails->updated_by_id = $loginUser->id;
                $contentDetails->updated_at = Carbon::now()->toDateTimeString();
                $contentDetails->save();
            }
            else {
                return $this->handleError('content can not update, Wrong action', [], 500);
            }

            return $this->handleResponse($contentDetails->fresh(), 'Content updated successfully');            
            
        }
        catch(Exception $e) 
        {
            logger('review content: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }

    }
}
