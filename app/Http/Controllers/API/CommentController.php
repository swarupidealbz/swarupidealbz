<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Comments;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Topics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseController
{
    public function index()
    {
        try {                
            $list = Comments::all();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('comment list error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Comments $Comment)
    {
        try {                
            if(empty($Comment)) {
                return $this->handleError([], 'Data not found', 404);
            }

            return $this->handleResponse($Comment, 'Success');
        }
        catch(Exception $e) 
        {
            logger('Comment show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function commentByUser(Request $request)
    {
        try {
            $lists = Comments::query();
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

            $lists = $lists->get();
            
            return $this->handleResponse($lists, 'Success');            
        }
        catch(Exception $e) 
        {
            logger('Comment list by user error');
            return $this->handleError('Something went wrong', [], 500);
        }

    }

    public function addEditComment(Request $request)
    {
        try {
            $loginUser = Auth::user();
            $input = $request->only('website', 'primary_topic', 'child_topic', 'content_type', 'comment', 'action');
                
            $validator = Validator::make($input,[
                'website' => 'required|integer',
                'primary_topic' => 'required|integer',
                'child_topic' => 'required|integer',
                'content_type' => 'required',
                'comment' => 'required',
                'action' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            $childTopic = Topics::where('is_primary_topic', 0)->where('website_id', $request->website)
            ->where('primary_topic_id', $request->primary_topic)
            ->where('id', $request->child_topic)
            ->first();

            if(($childTopic->status != Topics::STATUS_APPROVED) && !in_array($loginUser->role, [User::ROLE_CLIENT, User::ROLE_WRITER])) {
                return $this->handleError('You are not permitted to comment.', [], 403);
            }

            $message = 'success';
            if($request->action == 'edit') {
                $comment_id = $request->comment_id;
                $commentDetails = Comments::find($comment_id);                
                if(!$commentDetails || !$comment_id) {
                    return $this->handleError('Comment not found to update', $commentDetails, 403);
                }
                if($commentDetails->user_id != $loginUser->id) {
                    return $this->handleError('You are not permitted to edit this comment.', $commentDetails, 403);
                }
                $commentDetails->website_id = $request->website;
                $commentDetails->primary_topic_id = $request->primary_topic;
                $commentDetails->child_topic_id = $request->child_topic;
                $commentDetails->comment = $request->comment;
                $commentDetails->updated_by_id = $loginUser->id;
                $commentDetails->updated_at = Carbon::now()->toDateTimeString();
                $commentDetails->save();
                $message = 'Comment updated successfully';
            }
            elseif($request->action == 'add') {
                $time = Carbon::now()->toDateTimeString();
                $insertData = [
                    'website_id' => $request->website,
                    'primary_topic_id' => $request->primary_topic,
                    'child_topic_id' => $request->child_topic,
                    'user_id' => $loginUser->id,
                    'comment' => $request->comment,
                    'created_by_id' => $loginUser->id,
                    'updated_by_id' => $loginUser->id,
                    'created_at' => $time,
                    'updated_at' => $time
                ];
                $commentDetails = Comments::create($insertData);
                $message = 'Comment added successfully';
            }
            else {
                return $this->handleError('Comment can not save, Wrong action', [], 500);
            }

            return $this->handleResponse($commentDetails->fresh(), $message);            
            
        }
        catch(Exception $e) 
        {
            logger('Comment add edit error');
            return $this->handleError('Something went wrong', [], 500);
        }

    }
}
