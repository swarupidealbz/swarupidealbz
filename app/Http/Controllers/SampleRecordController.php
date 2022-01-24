<?php

namespace App\Http\Controllers;

use App\Models\SideMenus;
use App\Models\Websites;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SampleRecordController extends Controller
{
    const ACCESS = [
        'client' => true,
        'writer' => true,
        'reviewer' => true,
        'admin' => true,
        'editor' => true,
    ];

    public function createMenu()
    {
        $data = [];
        $access = self::ACCESS;
        $record = [
            'name' => 'Dashboard',
            'access' => json_encode($access),
            'created_by_id' => 1,
            'updated_by_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
        $data[] = $record;

        $access = self::ACCESS;
        $access['reviewer'] = false;
        $access['editor'] = false;
        $record['name'] = 'Region';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $access['reviewer'] = false;
        $access['editor'] = false;
        $record['name'] = 'Language';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $access['reviewer'] = false;
        $access['editor'] = false;
        $record['name'] = 'Website';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $access['reviewer'] = false;
        $access['editor'] = false;
        $record['name'] = 'Groups';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $record['name'] = 'All Topics';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $record['name'] = 'Primary Topics';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $record['name'] = 'Child Topics';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $record['name'] = 'Contents';
        $record['access'] = json_encode($access);
        $data[] = $record;

        $access = self::ACCESS;
        $record['name'] = 'Comments';
        $record['access'] = json_encode($access);
        $data[] = $record;
        
        $access = self::ACCESS;
        $access['reviewer'] = false;
        $access['editor'] = false;
        $access['client'] = false;
        $access['writer'] = false;
        $record['name'] = 'Side Menu';
        $record['access'] = json_encode($access);
        $data[] = $record;

        SideMenus::insert($data);
    }

    public function createWebsite()
    {
        $data = [];
        $record = [
            'name' => 'google.io',
            'owners' => "1,2,3,4,5,6,7,8,9,10",
            'created_by_id' => 1,
            'updated_by_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
        $data[] = $record;

        $record = [
            'name' => 'content.io',
            'owners' => "1,2,3,4,5,6,7,8,9,10",
            'created_by_id' => 1,
            'updated_by_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
        $data[] = $record;

        Websites::insert($data);
    }
}