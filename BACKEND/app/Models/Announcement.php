<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Announcement extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'announcements';

    protected $fillable = ['title', 'content', 'is_active'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}