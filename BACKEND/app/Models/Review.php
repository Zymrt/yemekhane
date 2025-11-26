<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; 
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'reviews';

    protected $fillable = [
        'user_id', 
        'menu_id', 
        'rating', 
        'comment', 
        'date'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function menu() {
        return $this->belongsTo(Menu::class);
    }
}