<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegionController extends BaseController
{
    public function index()
    {
        try {
                
            $list = Region::all();

            return $this->handleResponse($list, 'Fetched all list');
        }
        catch(Exception $e) 
        {
            logger('region list error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    public function show(Region $region)
    {
        try {
                
            if(empty($region)) {
                return $this->handleError([], 'Data not found', 404);
            }
            return $this->handleResponse($region, 'Success');
        }
        catch(Exception $e) 
        {
            logger('region show error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }
}
