<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];
    protected $with = ['website', 'primaryTopic', 'childTopic', 'user', 'createdUser', 'updatedUser'];

    public function website()
    {
        return $this->belongsTo(Websites::class, 'website_id');
    }

    public function primaryTopic()
    {
        return $this->belongsTo(Topics::class, 'primary_topic_id')->where('is_primary_topic', 1);
    }

    public function childTopic()
    {
        return $this->belongsTo(Topics::class, 'child_topic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedUser()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
