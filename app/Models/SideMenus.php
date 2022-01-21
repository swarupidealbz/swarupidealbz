<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SideMenus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'side_menu';
	protected $guarded = [];
    protected $with = ['sub_menus'];
	
	public function sub_menus()
    {
        return $this->hasMany(SideMenus::class, 'parent_id');
    }
}
