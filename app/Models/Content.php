<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'content';
    protected $guarded = [];
    protected $with = ['website', 'primaryTopic', 'childTopic', 'user', 'createdUser', 'updatedUser'];

    
    const STATUS_OPEN = 'open';
    const STATUS_WORKIN_PROGRESS = 'work in progress';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const CONTENT_TYPE_OUTLINE = 'outline';
    const CONTENT_TYPE_ARTICLE = 'article';

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
