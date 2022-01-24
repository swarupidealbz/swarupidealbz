<?php

use App\Http\Controllers\SampleRecordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/record/menu', [SampleRecordController::class, 'createMenu']);
Route::get('/record/website', [SampleRecordController::class, 'createWebsite']);
