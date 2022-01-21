<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comments;
use App\Models\Content;
use App\Models\Notifications;
use App\Models\SideMenus;
use App\Models\Topics;
use App\Models\Websites;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    public function dashboard(Request $request)
    {
        $result = [
            'side_menus' => (blank($request->parts) || ($request->parts == 'side_menu')) ? $this->sideMenus() : [],
            'websites' => (blank($request->parts) || ($request->parts == 'top_bar')) ? $this->websites() : [],
            'languages' => (blank($request->parts) || ($request->parts == 'top_bar')) ? $this->languages() : [],
            'notifications' => (blank($request->parts) || ($request->parts == 'top_bar')) ? $this->notifications() : [],
            'statistics' => (blank($request->parts) || ($request->parts == 'data')) ? $this->contents($request) : [],
			'topic_lists' => (blank($request->parts) || ($request->parts == 'data')) ? $this->topics($request) : [],
			'article_lists' => (blank($request->parts) || ($request->parts == 'data')) ? $this->articles($request) : []
        ];

        return $this->handleResponse($result, 'Dashboard content is ready');
    }

    private function sideMenus()
    {
        $loginUser = Auth::user();
        $rec = SideMenus::select('id', 'parent_id', 'name', 'access')->whereNull('parent_id')->get()->filter(function($item) use($loginUser){
            $access = json_decode($item->access, true);
            return $access[$loginUser->role];
        })->map(function($menu) use($loginUser){
			$item = [
				'id' => $menu->id,
				'parent_id' => $menu->parent_id,
				'name' => $menu->name,
				'access' => $menu->access
			];
			$children = $menu->sub_menus->filter(function($item) use($loginUser){
				$access = json_decode($item->access, true);
				return $access[$loginUser->role];
			})->map(function($sub) {
				return [
					'id' => $sub->id,
					'parent_id' => $sub->parent_id,
					'name' => $sub->name,
					'access' => $sub->access
				];
			});
			if($children->count()) {
				$item['sub_menus'] = $children;
			}			
			return $item;
		});

        return $rec;

    }

    private function websites()
    {
        $loginUser = Auth::user();
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        });

        return $rec;
    }

    private function languages()
    {
        return [
            [
                'name' => 'English',
                'sort_name' => 'EN'
            ],
            [
                'name' => 'Denmark',
                'sort_name' => 'DN'
            ]
        ];
    }

    private function notifications()
    {
        $loginUser = Auth::user();
        $rec = Notifications::where('recipient_user_id', $loginUser->id)
        ->where('is_read', 0);
        $response = [
            'count' => $rec->count(),
            'data' => $rec->latest()->take(10)->get()
        ];

        return $response;
    }

    private function contents($request)
    {
        $loginUser = Auth::user();
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        })->first();
        $websiteId = optional($rec)->id;
        if($request->website) {
            $websiteId = $request->website;
        }
        $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $currentDate = Carbon::now()->toDateTimeString();

        $firstDayofPreviousMonth = Carbon::now()->startOfMonth()->subMonthsNoOverflow()->toDateTimeString();
        $lastDayofPreviousMonth = Carbon::now()->subMonthsNoOverflow()->endOfMonth()->toDateTimeString();

        $topics = $this->topicRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);
        $articles = $this->articleRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);
        $outlines = $this->outlineRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);
        $comments = $this->commentRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth);    

        return [
            'topics' => $topics,
            'articles' => $articles,
            'outlines' => $outlines,
            'comments' => $comments
        ];       

    }

    private function topicRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        //getting topic
        $currentMonthTopics = Topics::where('website_id', $websiteId)
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthTopics = Topics::where('website_id', $websiteId)
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstTopic = Topics::where('website_id', $websiteId)->oldest()->get()->first();
        $topicStartText = 'Since The Start';
        $topicInc = '100%';
        $incText = '--';
        if($firstTopic && optional(optional($firstTopic)->created_at)->toDateTimeString() < $startOfMonth) {
            $topicStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthTopics - $lastMonthTopics;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $inc = (abs($diffLastCurrent) / $lastMonthTopics) * 100;
            $topicInc = number_format($inc, 2).'%';
        }
        $topicContent = [
            'text' => 'Topics',
            'count' => $currentMonthTopics,
            'stat' => $topicInc,
            'stat_text' => $incText,
            'stat_review_text' => $topicStartText
        ];

        return $topicContent;
    }

    private function articleRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        //getting content
        $currentMonthContent = Content::where('website_id', $websiteId)
        ->where('content_type', 'like', 'article')
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthContent = Content::where('website_id', $websiteId)
        ->where('content_type', 'like', 'article')
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstContent = Content::where('website_id', $websiteId)
        ->where('content_type', 'like', 'article')
        ->oldest()->get()->first();
        $contentStartText = 'Since The Start';
        $contentInc = '100%';
        $incText = '--';
        if($firstContent && optional(optional($firstContent)->created_at)->toDateTimeString() < $startOfMonth) {
            $contentStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthContent - $lastMonthContent;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $inc = (abs($diffLastCurrent) / $lastMonthContent) * 100;
            $contentInc = number_format($inc, 2).'%';
        }
        $articleContent = [
            'text' => 'Articles',
            'count' => $currentMonthContent,
            'stat' => $contentInc,
            'stat_text' => $incText,
            'stat_review_text' => $contentStartText
        ];

        return $articleContent;
    }

    private function outlineRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        //getting outline
        $currentMonthOutline = Content::where('website_id', $websiteId)
        ->where('content_type', 'like', 'outline')
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthOutline = Content::where('website_id', $websiteId)
        ->where('content_type', 'like', 'outline')
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstOutline = Content::where('website_id', $websiteId)
        ->where('content_type', 'like', 'outline')
        ->oldest()->get()->first();
        $outlineStartText = 'Since The Start';
        $outlineInc = '100%';
        $incText = '--';
        if($firstOutline && optional(optional($firstOutline)->created_at)->toDateTimeString() < $startOfMonth) {
            $outlineStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthOutline - $lastMonthOutline;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $inc = (abs($diffLastCurrent) / $currentMonthOutline) * 100;
            $outlineInc = number_format($inc, 2).'%';
        }
        $outlineContent = [
            'text' => 'Outlines',
            'count' => $currentMonthOutline,
            'stat' => $outlineInc,
            'stat_text' => $incText,
            'stat_review_text' => $outlineStartText
        ];

        return $outlineContent;
    }

    private function commentRecord($websiteId, $startOfMonth, $currentDate, $firstDayofPreviousMonth, $lastDayofPreviousMonth)
    {
        $currentMonthComment = Comments::where('website_id', $websiteId)
        ->whereBetween('created_at',[$startOfMonth, $currentDate])->count();
        $lastMonthComment = Comments::where('website_id', $websiteId)
        ->whereBetween('created_at',[$firstDayofPreviousMonth, $lastDayofPreviousMonth])->count();

        $firstComment = Comments::where('website_id', $websiteId)->oldest()->get()->first();
        $commentStartText = 'Since The Start';
        $commentInc = '100%';
        $incText = '--';
        if($firstComment && optional(optional($firstComment)->created_at)->toDateTimeString() < $startOfMonth) {
            $commentStartText = 'Since Last Month';
            $diffLastCurrent = $currentMonthComment - $lastMonthComment;
            $incText = $diffLastCurrent > 0 ? 'inc' : 'dec';
            $inc = (abs($diffLastCurrent) / $lastMonthComment) * 100;
            $commentInc = number_format($inc, 2).'%';
        }
        $commentContent = [
            'text' => 'Comments',
            'count' => $currentMonthComment,
            'stat' => $commentInc,
            'stat_text' => $incText,
            'stat_review_text' => $commentStartText
        ];

        return $commentContent;
    }
	
	private function topics($request)
	{
		$loginUser = Auth::user();
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        })->first();
        $websiteId = optional($rec)->id;
        if($request->website) {
            $websiteId = $request->website;
        }
		
		$topics = Topics::where('website_id', $websiteId)->latest()->take(10)->get();
		
		return $topics;
	}
	
	private function articles($request)
	{
		$loginUser = Auth::user();
        $rec = Websites::all()->filter(function($item) use($loginUser){
            $owners = explode(',', $item->owners);
            return in_array($loginUser->id, $owners);
        })->first();
        $websiteId = optional($rec)->id;
        if($request->website) {
            $websiteId = $request->website;
        }
		
		$articles = Content::where('website_id', $websiteId)
        ->where('content_type', 'like', 'article')->latest()->take(10)->get();
		
		return $articles;
	}
}
