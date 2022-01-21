<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LanguageController extends BaseController
{
    public function index()
    {
        try {
                
            $list = Language::all();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('language list error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Language $language)
    {
        try {
                
            if(empty($language)) {
                return $this->handleError([], 'Data not found', 404);
            }
            return $this->handleResponse($language, 'Success');
        }
        catch(Exception $e) 
        {
            logger('language show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }
}
