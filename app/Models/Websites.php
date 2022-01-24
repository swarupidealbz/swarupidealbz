<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Websites extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $with = ['language', 'region', 'createdUser', 'updatedUser'];
    public $appends = ['owner_details_list'];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function primaryTopic()
    {
        return $this->hasMany(Topics::class, 'website_id')->where('is_primary_topic', 1);
    }

    public function childTopic()
    {
        return $this->hasMany(ChildTopics::class, 'website_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class, 'website_id');
    }

    public function content()
    {
        return $this->hasMany(Content::class, 'website_id');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedUser()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function getOwnerDetailsListAttribute()
    {
        $owners = $this->owners;
        $owners = is_array($owners) ? $owners : explode(',', $owners);
        return collect($owners)->map(function($userId, $key){
            return User::find($userId);
        });
    }
}
