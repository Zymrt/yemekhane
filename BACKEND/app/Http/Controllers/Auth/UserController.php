<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth; // Artık Auth facade'ına ihtiyacımız yok

class UserController extends Controller
{
    /**
     * Oturumu açık olan kullanıcının profil bilgilerini döndürür.
     * Bu rota JWT middleware tarafından korunmalıdır.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
     
    // Mevcut profile metodunuz
    public function profile(Request $request)
    {
        return response()->json(auth()->user());
    }

    // Düzeltilmiş getProfile metodunuz
    public function getProfile(Request $request)
    {
        // auth()->user() çağrısı, JWT middleware'i token'ı doğruladıktan sonra
        // kullanıcıyı otomatik olarak yükleyecektir. Artık Auth::guard('api') hatası almayız.
        $user = auth()->user(); 

        // Eğer rota JWT middleware ile korunuyorsa, bu kontrol gereksizdir.
        // Middleware token yoksa zaten 401 hatası döner.
        // Ancak ek güvenlik için tutmak isterseniz:
        if (!$user) {
            // 401 Unauthorized (Yetkisiz) durum kodu, 404'ten daha uygundur.
            return response()->json(['message' => 'Oturum geçersiz.'], 401); 
        }

        // Kullanıcının tüm profil verilerini döndürür.
        return response()->json([
            'user' => $user,
        ], 200);
    }
}