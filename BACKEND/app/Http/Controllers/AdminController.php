<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * Onay bekleyen kullanıcıları listeler (status: 'pending').
     */
    public function getPendingUsers()
    {
        // Status'ü 'pending' olan kullanıcıları ve gerekli alanları seç
        $pendingUsers = User::where('status', 'pending')
                            ->select('id', 'name', 'surname', 'phone', 'unit', 'document_path', 'created_at')
                            ->get();

        return response()->json($pendingUsers, Response::HTTP_OK);
    }

    /**
     * Kullanıcının yüklediği belgeyi indirir.
     */
    public function downloadDocument($userId)
    {
        // Kullanıcıyı bul veya hata fırlat
        $user = User::findOrFail($userId);

        if (empty($user->document_path)) {
            return response()->json(['message' => 'Bu kullanıcı için belge yüklenmemiş.'], Response::HTTP_NOT_FOUND);
        }

        $filePath = $user->document_path;
        // Dosya uzantısını pathinfo ile alarak daha güvenli bir dosya adı oluşturma
        $fileName = 'Belge_' . $user->name . '_' . $user->surname . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        // Dosyanın storage/app (local disk) içinde varlığını kontrol et
        if (Storage::disk('local')->exists($filePath)) {
            // Dosyayı indirme zorunluluğu (download) ile gönder
            return Storage::download($filePath, $fileName);
        }

        return response()->json(['message' => 'Belge dosyası sunucuda bulunamadı.'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Belirtilen kullanıcıyı onaylar (status: 'approved' yapar).
     * @param int $userId Onaylanacak kullanıcının ID'si
     */
    public function approveUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->status === 'approved') {
            return response()->json(['message' => 'Kullanıcı zaten onaylanmış.'], Response::HTTP_CONFLICT);
        }

        // Kullanıcının status'ünü 'approved' olarak günceller
        $user->status = 'approved';
        $user->save();

        // Başarılı yanıt
        return response()->json(['message' => 'Kullanıcı başarıyla onaylandı.'], Response::HTTP_OK);
    }

    /**
     * Belirtilen kullanıcıyı reddeder veya siler. 
     * Reddetme işlemi için kullanıcıyı tamamen siliyoruz.
     * @param int $userId Silinecek kullanıcının ID'si
     */
    public function rejectUser($userId)
    {
        $user = User::findOrFail($userId);

        // Kullanıcının yüklediği belgeyi de silmek isterseniz:
        if (!empty($user->document_path) && Storage::disk('local')->exists($user->document_path)) {
             Storage::disk('local')->delete($user->document_path);
        }

        // Kullanıcıyı veritabanından tamamen sil
        $user->delete();

        // Başarılı yanıt
        return response()->json(['message' => 'Kullanıcı kaydı başarıyla reddedildi ve silindi.'], Response::HTTP_OK);
    }
}