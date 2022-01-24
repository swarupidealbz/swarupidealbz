<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Websites;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebsiteController extends BaseController
{
    public function index()
    {
        try {
                
            $list = Websites::all();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('website list error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Websites $website)
    {
        try {
                
            if(empty($website)) {
                return $this->handleError([], 'Data not found', 404);
            }
            return $this->handleResponse($website, 'Success');
        }
        catch(Exception $e) 
        {
            logger('website show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }
}
