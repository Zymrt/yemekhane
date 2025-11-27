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
     * Toplu atamaya (Mass Assignment) izin verilen alanlar.
     * meal_price ve unit alanlarının burada olması şart.
     */
    protected $fillable = [
        'name', 
        'surname', 
        'phone', 
        'email',
        'password', 
        'unit', 
        'balance', 
        'status', 
        'document_path',
        'role', 
        'meal_price'
    ];

    /**
     * Varsayılan değerler.
     */
    protected $attributes = [
        'role' => 'user',
        'status' => 'pending',
        'balance' => 0,
    ];

    protected $hidden = ['password'];
}