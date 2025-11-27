<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validasyon
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'email' => 'required|email|unique:users', // Validation tamam
            'password' => 'required|string|min:6|confirmed',
            'unit' => 'required|string',
            'proof_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            // 2. Dosyayı Kaydet
            $path = $request->file('proof_document')->store('proofs', 'public');

            // 3. KULLANICIYI ELLE OLUŞTUR
            $user = new User();
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->phone = $request->phone;
            
            // 👇👇👇 UNUTULAN SATIR BURASIYDI 👇👇👇
            $user->email = $request->email; 
            // 👆👆👆 BU SATIRI EKLEMEZSEN KAYDETMEZ 👆👆👆

            $user->password = Hash::make($request->password);
            $user->unit = $request->unit;
            $user->document_path = $path;
            
            $user->role = 'user';
            $user->status = 'pending'; 
            $user->balance = 0;
            $user->meal_price = 0;

            // 4. Veritabanına Kaydet
            $user->save();

            Log::info('Kullanıcı başarıyla veritabanına yazıldı. Email: ' . $user->email);

            return response()->json([
                'message' => 'Kayıt başarıyla oluşturuldu. Onay bekleniyor.',
                'user_id' => $user->_id
            ], 201);

        } catch (\Exception $e) {
            Log::error('Kayıt Hatası: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Veritabanı kayıt hatası!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}