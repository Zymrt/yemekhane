<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenAuthMiddleware
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $plainToken = $request->cookie('access_token') ?? $request->bearerToken();

        $session = $this->tokenService->validateAccessToken($plainToken);

        if (! $session) {
            return response()->json(['message' => 'Token bulunamadı veya süresi doldu.'], 401);
        }

        $session->loadMissing('user');

        if (! $session->user) {
            $this->tokenService->revokeSession($session);

            return response()->json(['message' => 'Kullanıcı bulunamadı.'], 401);
        }

        $this->tokenService->touch($session, $request);

        Auth::setUser($session->user);
        $request->setUserResolver(fn () => $session->user);

        return $next($request);
    }
}
