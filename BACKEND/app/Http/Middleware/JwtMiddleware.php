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
            // 🍪 1️⃣ Token'ı cookie'den al, yoksa header'dan
            $token = $request->cookie('token') ?? $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token bulunamadı.'], 401);
            }

            // 🧠 2️⃣ Header'a manuel olarak ekle (bazı JWTAuth çağrıları bunu ister)
            $request->headers->set('Authorization', 'Bearer ' . $token);

            // 🔐 3️⃣ Token'ı doğrula
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

        // ✅ Token geçerli, devam et
        return $next($request);
    }
}
