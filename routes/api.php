<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChildTopicController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\ContentController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\LanguageController;
use App\Http\Controllers\API\PeppertypeController;
use App\Http\Controllers\API\PrimaryTopicController;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\WebsiteController;
use App\Http\Controllers\SampleRecordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
Route::post('/password/forgot-password', 
    [AuthController::class, 'sendResetLinkResponse'])->name('password.sent');
Route::post('/password/reset', 
    [AuthController::class, 'sendResetResponse'])->name('password.reset');

Route::get('email/verify/{id}', [AuthController::class, 'verify'])->name('verification.verify'); // Make sure to keep this as your route name

Route::get('email/resend', [AuthController::class, 'resend'])->name('verification.resend');
    


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::prefix('websites')
    ->name('websites.')
    ->group(function(){
        Route::get('/list', [WebsiteController::class, 'index'])->name('list');
        Route::get('/show/{website}', [WebsiteController::class, 'show'])->name('show');
    });

    Route::prefix('language')
    ->name('language.')
    ->group(function(){
        Route::get('/list', [LanguageController::class, 'index'])->name('list');
        Route::get('/show/{language}', [LanguageController::class, 'show'])->name('show');
    });

    Route::prefix('region')
    ->name('region.')
    ->group(function(){
        Route::get('/list', [RegionController::class, 'index'])->name('list');
        Route::get('/show/{region}', [RegionController::class, 'show'])->name('show');
    });

    Route::prefix('primary-topic')
    ->name('primary-topic.')
    ->group(function(){
        Route::get('/list', [PrimaryTopicController::class, 'index'])->name('list');
        Route::get('/show/{primaryTopic}', [PrimaryTopicController::class, 'show'])->name('show');
        Route::get('/list-by-role', [PrimaryTopicController::class, 'primaryTopicByRole'])->name('list-by-role');
        Route::post('/list-by-website', [PrimaryTopicController::class, 'primaryTopicByWebsite'])->name('list-by-website');

        Route::post('/list-by-user', [PrimaryTopicController::class, 'primaryTopicByUser'])->name('list-by-user');
    });

    Route::prefix('child-topic')
    ->name('child-topic.')
    ->group(function(){
        Route::get('/list', [ChildTopicController::class, 'index'])->name('list');
        Route::get('/show/{childTopic}', [ChildTopicController::class, 'show'])->name('show');
        Route::post('/list-by-role', [ChildTopicController::class, 'childTopicByRole'])->name('list-by-role');//can send optional parameter website and primary_topic
        Route::post('/open-list-by-role', [ChildTopicController::class, 'openChildTopicByRole'])->name('open-list-by-role');//can send optional parameter website and primary_topic
        Route::post('/list-by-website/{status?}', [ChildTopicController::class, 'childTopicByWebsite'])->name('list-by-website');
        Route::post('/list-by-primary/{status?}', [ChildTopicController::class, 'childTopicByPrimary'])->name('list-by-primary');
        Route::post('/update-status', [ChildTopicController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('content')
    ->name('content.')
    ->group(function(){
        Route::get('/list', [ContentController::class, 'index'])->name('list');
        Route::get('/show/{content}', [ContentController::class, 'show'])->name('show');
        Route::post('/list-by-status', [ContentController::class, 'contentByStatus'])->name('list-by-status');
        Route::post('/update-status', [ContentController::class, 'updateStatus'])->name('update-status');

        Route::post('/list-for-timeline', [ContentController::class, 'contentForTimeline'])->name('list-for-timeline');
        Route::post('/show-by-role/{content}', [ContentController::class, 'contentShowByRole'])->name('show-by-role');
        Route::post('/review-content', [ContentController::class, 'reviewContent'])->name('review-content');
        Route::post('/create', [ContentController::class, 'create'])->name('create');
    });

    Route::prefix('comment')
    ->name('comment.')
    ->group(function(){
        Route::get('/list', [CommentController::class, 'index'])->name('list');
        Route::get('/show/{comment}', [CommentController::class, 'show'])->name('show');
        Route::post('/list-by-user', [CommentController::class, 'commentByUser'])->name('list-by-user');

        Route::post('/add-edit', [CommentController::class, 'addEditComment'])->name('add-edit');
    });

    Route::prefix('topic')
    ->name('topic.')
    ->group(function(){
        Route::post('/create', [PrimaryTopicController::class, 'create'])->name('create');
        Route::post('/update-status', [PrimaryTopicController::class, 'updateStatus'])->name('update-status');
		Route::post('/sort-record', [PrimaryTopicController::class, 'topicBySort'])->name('sort-record');
    });

    Route::get('/pepper-login', [PeppertypeController::class, 'login']);
    Route::post('/create-pepper-idea', [PeppertypeController::class, 'createIdeas']);
    Route::post('/create-pepper-intro', [PeppertypeController::class, 'createIntro']);
    Route::post('/create-pepper-outline', [PeppertypeController::class, 'createOutline']);
    Route::post('/create-pepper-conclusion', [PeppertypeController::class, 'createConclusion']);

    Route::post('/dashboard/data', [DashboardController::class, 'dashboard'])->name('dashboard');
});

