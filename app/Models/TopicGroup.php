<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicGroup extends Model
{
    use HasFactory;

    protected $table = 'topic_groups';

    public function group()
    {
        return $this->belongsTo(Groups::class, 'group_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topics::class, 'topic_id');
    }
}
