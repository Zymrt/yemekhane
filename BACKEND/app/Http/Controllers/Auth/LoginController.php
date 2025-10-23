<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

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

        $customClaims = ['role' => $user->role];
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        // ✅ Local için cookie ayarları
        $cookie = cookie(
            'token',
            $token,
            60 * 24,    // 1 gün
            '/',
            '127.0.0.1', // local domain
            false,       // secure = false (HTTP)
            true,        // HttpOnly
            false,
            'Lax'        // Chrome kabul eder
        );

        return response()->json([
            'message' => 'Giriş başarılı.',
            'user' => $user->only('_id','name','surname','phone','unit','balance','role'),
            'debug_token' => $token // geçici debug
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {}

        $forgetCookie = cookie()->forget('token');

        return response()->json([
            'message' => 'Çıkış yapıldı.'
        ])->withCookie($forgetCookie);
    }
}
