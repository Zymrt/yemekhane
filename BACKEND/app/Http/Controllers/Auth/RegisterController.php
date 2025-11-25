<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Loglamak için ekledik

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validasyon
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'unit' => 'required|string',
            'proof_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            // 2. Dosyayı Kaydet
            $path = $request->file('proof_document')->store('proofs', 'public');

            // 3. KULLANICIYI ELLE OLUŞTUR (Create yerine new User)
            // Bu yöntem $fillable dizisine bakmaz, direkt nesneye yazar.
            $user = new User();
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->unit = $request->unit;
            $user->document_path = $path;
            
            // Varsayılan değerleri de elle girelim, modelin keyfine bırakmayalım
            $user->role = 'user';
            $user->status = 'pending'; 
            $user->balance = 0;
            $user->meal_price = 0;

            // 4. Veritabanına Kaydet
            $user->save();

            // Log dosyasına not düşelim (storage/logs/laravel.log'dan bakabilirsin)
            Log::info('Kullanıcı başarıyla veritabanına yazıldı. ID: ' . $user->_id);

            return response()->json([
                'message' => 'Kayıt başarıyla oluşturuldu. Onay bekleniyor.',
                'user_id' => $user->_id
            ], 201);

        } catch (\Exception $e) {
            // Hata olursa gizleme, direkt ekrana bas
            Log::error('Kayıt Hatası: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Veritabanı kayıt hatası!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}