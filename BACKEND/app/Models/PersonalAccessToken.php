<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasAbilities;

class PersonalAccessToken extends Model implements HasAbilities
{
    protected $connection = 'mongodb';
    protected $collection = 'personal_access_tokens';

    protected $fillable = [
        'tokenable_id',
        'tokenable_type',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable()
    {
        return $this->morphTo();
    }

    public function getAbilities(): array
    {
        return $this->abilities ?? [];
    }

    public function can($ability)
    {
        return in_array('*', $this->getAbilities()) ||
               in_array($ability, $this->getAbilities());
    }

    public function cant($ability)
    {
        return ! $this->can($ability);
    }
}
