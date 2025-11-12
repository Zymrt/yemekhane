<?php

namespace App\Services;

use App\Models\SessionToken;
use App\Models\User;
use Illuminate\Contracts\Cookie\Factory as CookieFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Cookie;

class TokenService
{
    protected int $accessTokenTtlMinutes;

    protected int $refreshTokenTtlMinutes;

    public function __construct(protected CookieFactory $cookieFactory)
    {
        $this->accessTokenTtlMinutes = (int) Config::get('token.access_token_ttl', 60);
        $refreshDays = (int) Config::get('token.refresh_token_ttl', 7);
        $this->refreshTokenTtlMinutes = $refreshDays * 24 * 60;
    }

    public function createSession(User $user, Request $request): array
    {
        $accessToken = $this->generateToken(64);
        $refreshToken = $this->generateToken(128);

        $session = SessionToken::create([
            'user_id' => (string) $user->getKey(),
            'access_token_hash' => $this->hashToken($accessToken),
            'access_token_expires_at' => Carbon::now()->addMinutes($this->accessTokenTtlMinutes),
            'refresh_token_hash' => $this->hashToken($refreshToken),
            'refresh_token_expires_at' => Carbon::now()->addMinutes($this->refreshTokenTtlMinutes),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'last_used_at' => Carbon::now(),
        ]);

        return [
            'session' => $session,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function validateAccessToken(?string $plainToken): ?SessionToken
    {
        if (! $plainToken) {
            return null;
        }

        $hash = $this->hashToken($plainToken);
        $session = SessionToken::where('access_token_hash', $hash)->first();

        if (! $session || $session->isAccessTokenExpired()) {
            return null;
        }

        return $session;
    }

    public function validateRefreshToken(?string $plainToken): ?SessionToken
    {
        if (! $plainToken) {
            return null;
        }

        $hash = $this->hashToken($plainToken);
        $session = SessionToken::where('refresh_token_hash', $hash)->first();

        if (! $session || $session->isRefreshTokenExpired()) {
            return null;
        }

        return $session;
    }

    public function rotateTokens(SessionToken $session, Request $request): array
    {
        $accessToken = $this->generateToken(64);
        $refreshToken = $this->generateToken(128);

        $session->fill([
            'access_token_hash' => $this->hashToken($accessToken),
            'access_token_expires_at' => Carbon::now()->addMinutes($this->accessTokenTtlMinutes),
            'refresh_token_hash' => $this->hashToken($refreshToken),
            'refresh_token_expires_at' => Carbon::now()->addMinutes($this->refreshTokenTtlMinutes),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'last_used_at' => Carbon::now(),
        ]);

        $session->save();

        return [
            'session' => $session,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function touch(SessionToken $session, ?Request $request = null): void
    {
        $session->last_used_at = Carbon::now();

        if ($request) {
            $session->ip_address = $request->ip();
            $session->user_agent = substr((string) $request->userAgent(), 0, 500);
        }

        $session->save();
    }

    public function revokeSession(SessionToken $session): void
    {
        $session->delete();
    }

    public function revokeByTokens(?string $accessToken, ?string $refreshToken): void
    {
        $hashes = array_filter([
            $accessToken ? $this->hashToken($accessToken) : null,
            $refreshToken ? $this->hashToken($refreshToken) : null,
        ]);

        if (empty($hashes)) {
            return;
        }

        SessionToken::where(function ($query) use ($hashes) {
            $query->whereIn('access_token_hash', $hashes)
                ->orWhereIn('refresh_token_hash', $hashes);
        })->delete();
    }

    public function makeAccessTokenCookie(string $token): Cookie
    {
        return $this->cookieFactory->make(
            'access_token',
            $token,
            $this->accessTokenTtlMinutes,
            '/',
            null,
            false,
            true,
            false,
            'Lax'
        );
    }

    public function makeRefreshTokenCookie(string $token): Cookie
    {
        return $this->cookieFactory->make(
            'refresh_token',
            $token,
            $this->refreshTokenTtlMinutes,
            '/',
            null,
            false,
            true,
            false,
            'Lax'
        );
    }

    public function forgetCookies(): array
    {
        return [
            $this->cookieFactory->forget('access_token'),
            $this->cookieFactory->forget('refresh_token'),
        ];
    }

    protected function generateToken(int $length = 64): string
    {
        $bytes = (int) ceil($length / 2);

        return substr(bin2hex(random_bytes($bytes)), 0, $length);
    }

    protected function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
