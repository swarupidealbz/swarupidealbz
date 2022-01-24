<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topics extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'topics';
    protected $guarded = [];
    protected $with = ['website', 'createdUser', 'updatedUser'];

    const STATUS_OPEN = 'open';
    const STATUS_WORKIN_PROGRESS = 'work in progress';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function website()
    {
        return $this->belongsTo(Websites::class, 'website_id');
    }

    public function primaryTopic()
    {
        return $this->belongsTo(Topics::class, 'primary_topic_id')->where('is_primary_topic', 1);
    }

    public function content()
    {
        return $this->hasMany(Content::class, 'child_topic_id');
    }

    public function groups()
    {
        return $this->hasMany(TopicGroup::class, 'topic_id');
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
