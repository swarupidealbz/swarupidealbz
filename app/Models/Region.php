<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'region';
    protected $guarded = [];
    protected $with = ['createdUser', 'updatedUser'];


    public function website()
    {
        return $this->hasOne(Websites::class, 'region_id');
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
