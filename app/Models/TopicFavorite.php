<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopicFavorite extends Model
{
    use HasFactory, SoftDeletes;
	
	protected $guarded = [];
	
	public function updatedUser()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
