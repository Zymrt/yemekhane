<?php

namespace App\Http\Controllers; // Senin kullandığın namespace bu, doğru.

use App\Models\User; // Bu satır eksikti, ekledik.
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
        $pendingUsers = User::where('status', 'pending')
                            ->select('_id', 'name', 'surname', 'phone', 'unit', 'document_path', 'created_at') // MongoDB için id yerine _id kullandım
                            ->get();

        return response()->json($pendingUsers, Response::HTTP_OK);
    }

    /**
     * Kullanıcının yüklediği belgeyi indirir.
     */
    public function downloadDocument($userId)
    {
        $user = User::findOrFail($userId);

        if (empty($user->document_path)) {
            return response()->json(['message' => 'Bu kullanıcı için belge yüklenmemiş.'], Response::HTTP_NOT_FOUND);
        }

        $filePath = $user->document_path;
        $fileName = 'Belge_' . $user->name . '_' . $user->surname . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        
        // public diski kullandığımız için direkt storage_path ile erişebiliriz.
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath, $fileName);
        }

        return response()->json(['message' => 'Belge dosyası sunucuda bulunamadı.'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Belirtilen kullanıcıyı onaylar (status: 'approved' yapar).
     */
    public function approveUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->status === 'approved') {
            return response()->json(['message' => 'Kullanıcı zaten onaylanmış.'], Response::HTTP_CONFLICT);
        }

        $user->status = 'approved';
        $user->save();

        return response()->json(['message' => 'Kullanıcı başarıyla onaylandı.'], Response::HTTP_OK);
    }

    /**
     * Belirtilen kullanıcıyı reddeder (status: 'rejected' yapar).
     */
    public function rejectUser($userId)
    {
        $user = User::findOrFail($userId);

        // Kullanıcının yüklediği belgeyi de silmek istersen:
        if (!empty($user->document_path) && Storage::disk('public')->exists($user->document_path)) {
             Storage::disk('public')->delete($user->document_path);
        }

        // Kullanıcıyı silmek yerine status'ünü 'rejected' yapmak daha iyi bir pratik olabilir.
        // Ama şimdilik senin mantığınla gidelim, direkt silelim.
        $user->delete();

        return response()->json(['message' => 'Kullanıcı kaydı başarıyla reddedildi ve silindi.'], Response::HTTP_OK);
    }
}