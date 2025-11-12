<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TokenService;

class RefreshController extends Controller
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        $session = $this->tokenService->validateRefreshToken($refreshToken);

        if (! $session) {
            return response()->json([
                'message' => 'Token bulunamadı veya süresi doldu.',
                'hint' => 'Kullanıcı yeniden giriş yapmalı.',
            ], 401);
        }

        $session->loadMissing('user');

        if (! $session->user) {
            $this->tokenService->revokeSession($session);

            return response()->json([
                'message' => 'Kullanıcı bulunamadı.',
            ], 401);
        }

        $sessionData = $this->tokenService->rotateTokens($session, $request);

        $accessCookie = $this->tokenService->makeAccessTokenCookie($sessionData['access_token']);
        $refreshCookie = $this->tokenService->makeRefreshTokenCookie($sessionData['refresh_token']);

        $response = response()->json([
            'message' => '✅ Token başarıyla yenilendi.',
        ]);

        return $response->withCookie($accessCookie)->withCookie($refreshCookie);
    }
}
