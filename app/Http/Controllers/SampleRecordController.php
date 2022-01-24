<?php

namespace App\Http\Controllers;

use App\Models\SideMenus;
use App\Models\Websites;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // $data = [];
        // $record = [
        //     'name' => 'google.io',
        //     'owners' => "1,2,3,4,5,6,7,8,9,10",
        //     'created_by_id' => 1,
        //     'updated_by_id' => 1,
        //     'created_at' => Carbon::now()->toDateTimeString(),
        //     'updated_at' => Carbon::now()->toDateTimeString(),
        // ];
        // $data[] = $record;

        // $record = [
        //     'name' => 'content.io',
        //     'owners' => "1,2,3,4,5,6,7,8,9,10",
        //     'created_by_id' => 1,
        //     'updated_by_id' => 1,
        //     'created_at' => Carbon::now()->toDateTimeString(),
        //     'updated_at' => Carbon::now()->toDateTimeString(),
        // ];
        // $data[] = $record;

        // Websites::insert($data);
        // dump(DB::raw("SHOW VARIABLES LIKE 'SQL_REQUIRE%'"));
        // DB::raw('SET SQL_REQUIRE_PRIMARY_KEY=OFF');
        // dd(DB::raw("SHOW VARIABLES LIKE 'SQL_REQUIRE%'"));
		$para = [];
		$string = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).";
		$original = $string;
		$length = \Str::length($string);
		dump($length);
		while($length != 0) {
			$limitString = \Str::substr($string, 0, 300);
			$slice = \Str::beforeLast($limitString, '.').'.';
			$sliceLength = \Str::length($slice);
			$limitLength = \Str::length($limitString);
			$para[] = trim($slice);
			$string = \Str::substr($string, $sliceLength);
			$length = \Str::length($string);
			dump($limitString, $limitLength, $slice, $sliceLength, $length);
		}
		dd($para);
		
    }
	
	public function sample()
	{
		dd('hello');
	}
}