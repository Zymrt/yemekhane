<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class RefreshController extends Controller
{
    public function refresh(Request $request)
    {
        try {
            // 🍪 Cookie'den mevcut token'ı al
            $token = $request->cookie('token');

            if (!$token) {
                return response()->json([
                    'message' => 'Token bulunamadı.',
                    'hint' => 'Cookie gelmemiş olabilir, CORS veya SameSite ayarlarını kontrol et.'
                ], 401);
            }

            // Token yenile
            $newToken = JWTAuth::refresh($token);

            // Local ortamda HTTPS olmadığı için secure=false yapılmalı
            $isSecure = false;

            // Yeni cookie oluştur
            $cookie = cookie(
    'token',
    $token,
    60 * 24,   // 1 gün
    '/',
    '127.0.0.1', // 🍪 domain ekledik
    $isSecure,   // localde false
    true,        // HttpOnly
    false,
    'Lax'        // ✅ "None" yerine "Lax" yap
);

            return response()
                ->json(['message' => '✅ Token başarıyla yenilendi.'])
                ->withCookie($cookie);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token süresi tamamen dolmuş.',
                'hint' => 'Kullanıcı yeniden giriş yapmalı.'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Geçersiz token.',
                'hint' => 'Cookie bozulmuş veya imza geçersiz.'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token yenilenemedi.',
                'hint' => $e->getMessage()
            ], 401);
        }
    }
}
