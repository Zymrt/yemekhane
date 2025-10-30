<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Telefon veya şifre hatalı.'],
            ]);
        }

        if ($user->status !== 'approved') {
            throw ValidationException::withMessages([
                'phone' => ['Hesabınız henüz onaylanmamış.'],
            ]);
        }

        // 🔐 Özel claim ekle
        $customClaims = ['role' => $user->role];
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        // 🍪 Cookie ayarları (localhost + 127.0.0.1 için uyumlu)
        $cookie = cookie(
            'token',
            $token,
            60 * 24,    // 1 gün
            '/',        // path
            null,       // domain = otomatik (localhost / 127.0.0.1 fark etmez)
            false,      // secure = false (HTTP)
            true,       // HttpOnly
            false,
            'Lax'
        );

        return response()->json([
            'message' => 'Giriş başarılı.',
            'user' => $user->only('_id','name','surname','phone','unit','balance','role'),
            'debug_token' => $token // geçici debug (istersen silebilirsin)
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        try {
            // 🔄 Cookie'deki token'ı oku
            $token = $request->cookie('token') ?? JWTAuth::getToken();

            if ($token) {
                JWTAuth::setToken($token)->invalidate(); // Token’ı geçersiz yap
            }
        } catch (Exception $e) {
            // Sadece loglama amaçlı hata bastırma
            \Log::warning('JWT logout hatası: ' . $e->getMessage());
        }

        // 🍪 Cookie’yi sıfırla
        $forgetCookie = cookie()->forget('token');

        return response()->json([
            'message' => 'Çıkış yapıldı.'
        ])->withCookie($forgetCookie);
    }
}
