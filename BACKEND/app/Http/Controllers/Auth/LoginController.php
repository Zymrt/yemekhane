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

        // --- DEĞİŞİKLİK 1: TOKEN'IN İÇİNE ROL BİLGİSİNİ EKLİYORUZ ---
        $customClaims = ['role' => $user->role];
        $token = JWTAuth::claims($customClaims)->fromUser($user);
        
        // Önceki satır: $token = JWTAuth::fromUser($user);

        // --- DEĞİŞİKLİK 2: YANITTAN 'is_admin' KALDIRIP 'role' EKLİYORUZ ---
        return response()->json([
            // 'only' metoduna 'role' ekledik ve 'is_admin'i kaldırdık
            'user' => $user->only('_id','name','surname','phone','unit','balance', 'role'), 
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Token zaten geçersizse bile başarılı sayarız
            return response()->json(['message' => 'Çıkış yapıldı.'], 200);
        }
        
        return response()->json(['message' => 'Çıkış yapıldı.']);
    }
}