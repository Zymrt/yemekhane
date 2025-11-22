<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogController extends Controller
{
    /**
     * 📜 Tüm sistem loglarını filtreli ve sayfalı getirir.
     */
    public function index(Request $request)
    {
        // İlişkili kullanıcı verisiyle beraber sorguyu başlat
        $query = Log::with(['user' => function($q) {
            $q->select('_id', 'name', 'surname', 'role', 'unit');
        }]);

        // 🔍 Arama (İşlem adı veya açıklama)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // 🎨 Tip Filtresi (info, error vs.)
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // 📅 Tarih Filtresi
        if ($request->has('date') && !empty($request->date)) {
            // MongoDB tarih sorgusu (String olarak tutuyorsa 'LIKE', Date ise 'whereDate')
            // Genelde created_at MongoDB'de UTC date nesnesidir.
            // Basitlik için Carbon parse ile aralık verebiliriz ama
            // Şimdilik en son kayıtlar en üstte gelecek şekilde sıralayalım.
        }

        // Sıralama (En yeni en üstte)
        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate(20); // Sayfa başına 20 kayıt

        return response()->json($logs, Response::HTTP_OK);
    }

    /**
     * ➕ Log kaydetme (Helper fonksiyon olarak kullanılabilir)
     * Bu endpoint dışarıdan çağrılmaz, sistem içinden kullanılır.
     * Örnek kullanım: (new LogController)->record(...)
     */
    public function record($userId, $action, $desc, $type = 'info')
    {
        Log::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $desc,
            'type' => $type,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}