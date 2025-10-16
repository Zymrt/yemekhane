<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Notifications\Notifiable;

class User extends Model implements Authenticatable, JWTSubject
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
        'role' // 'is_admin' yerine bunu ekledik.
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

    // JWTSubject için gerekli metodlar
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}