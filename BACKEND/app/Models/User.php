<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Notifications\Notifiable;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // --- 1. BU DİZİYİ GÜNCELLEYİN ---
    protected $fillable = [
        'name', 'surname', 'phone', 'password', 'unit', 'balance', 'status', 'document_path',
        'role' ,'meal_price'// 'is_admin' yerine bunu ekledik.
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    // --- 2. BU BLOĞU KOMPLE EKLEYİN ---
    protected $attributes = [
        'role' => 'user',
        'status' => 'pending',
        'balance' => 0,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['password'];
}
