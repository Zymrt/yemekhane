<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // 🍪 Önce cookie'den token al, yoksa header'dan
            $token = $request->cookie('token') ?? $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token bulunamadı.'], 401);
            }

            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Kullanıcı bulunamadı.'], 401);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Yetkisiz erişim veya geçersiz token.',
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
