<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\Topics;
use App\Models\TopicFavorite;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class SetFavoriteController extends BaseController
{
    public function setFavoriteTopic(Request $request)
	{
		try {
			
			$user = Auth::user();
			$time = now()->toDateTimeString();
            $input = $request->only('topic');
                    
            $validator = Validator::make($input,[
                'topic' => 'required|integer'
            ]);
			
			if ($validator->fails()) {
                return $this->handleError('Select a topic to set favorite.', $validator->errors()->all(), 422);
            }
			
			$favorite = $user->favoriteTopic()->create(['topic_id' => $request->topic]);
			
			return $this->handleResponse($favorite, 'Selected topic set as favorite successfully.');
			
		}
        catch(Exception $e) 
        {
            logger('set topic favorite error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
	}
	
	public function unsetFavoriteTopic(Request $request)
	{
		try {
			
			$user = Auth::user();
			$time = now()->toDateTimeString();
            $input = $request->only('topic');
                    
            $validator = Validator::make($input,[
                'topic' => 'required|integer'
            ]);
			
			if ($validator->fails()) {
                return $this->handleError('Select a topic to unset favorite.', $validator->errors()->all(), 422);
            }
			
			$favorite = $user->favoriteTopic()->where(['topic_id' => $request->topic])->delete();
			
			return $this->handleResponse($favorite, 'Selected topic unset from favorite successfully.');
			
		}
        catch(Exception $e) 
        {
            logger('set topic favorite error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
	}
}
