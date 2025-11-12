<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

class SessionToken extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'session_tokens';

    protected $fillable = [
        'user_id',
        'access_token_hash',
        'access_token_expires_at',
        'refresh_token_hash',
        'refresh_token_expires_at',
        'ip_address',
        'user_agent',
        'last_used_at',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function isAccessTokenExpired(): bool
    {
        return $this->expiresAtPassed($this->access_token_expires_at);
    }

    public function isRefreshTokenExpired(): bool
    {
        return $this->expiresAtPassed($this->refresh_token_expires_at);
    }

    protected function expiresAtPassed($value): bool
    {
        if (! $value) {
            return true;
        }

        if (! $value instanceof CarbonInterface) {
            $value = Carbon::parse($value);
        }

        return $value->isPast();
    }
}
