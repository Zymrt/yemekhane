<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // 👈 Eklendi
use App\Mail\PasswordResetMail;      // 👈 Eklendi
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Bu e-posta adresiyle kayıtlı kullanıcı bulunamadı.'], 404);
        }

        $token = (string) rand(100000, 999999);

        PasswordReset::updateOrCreate(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // --- ESKİSİ (LOG) ---
        // Log::info("ŞİFRE SIFIRLAMA KODU ($user->email): $token");

        // --- YENİSİ (MAIL) ---
        try {
            Mail::to($user->email)->send(new PasswordResetMail($token));
            return response()->json(['message' => 'Doğrulama kodu e-posta adresinize gönderildi.']);
        } catch (\Exception $e) {
            Log::error("Mail Gönderme Hatası: " . $e->getMessage());
            return response()->json(['message' => 'Kod üretildi fakat mail sunucusu hatası oluştu.'], 500);
        }
    }

    // ... reset fonksiyonu aynı kalacak ...
    public function reset(Request $request)
    {
        // ... burası aynen kalıyor ...
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed'
        ]);
        
        $record = PasswordReset::where('email', $request->email)
            ->where('token', (string) $request->token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Geçersiz veya hatalı kod!'], 400);
        }
        
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Kullanıcı bulunamadı.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $record->delete();

        return response()->json(['message' => 'Şifreniz başarıyla değiştirildi. Giriş yapabilirsiniz.']);
    }
}