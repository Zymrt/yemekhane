<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    /**
     * 📋 Onay bekleyen kullanıcıları getirir
     */
    public function getPendingUsers()
    {
        $pendingUsers = User::where('status', 'pending')
            ->select('_id', 'name', 'surname', 'phone', 'unit', 'document_path', 'created_at')
            ->get();

        return response()->json($pendingUsers, Response::HTTP_OK);
    }

    /**
     * 👥 TÜM KULLANICILARI GETİRİR
     */
    public function getAllUsers()
    {
        $users = User::orderBy('created_at', 'desc')->get([
            '_id',
            'name',
            'surname',
            'email',
            'phone',
            'unit',
            'balance',
            'meal_price',
            'role',
            'status',
            'created_at'
        ]);

        return response()->json($users, Response::HTTP_OK);
    }

    public function downloadDocument($userId)
    {
        $user = User::findOrFail($userId);

        if (empty($user->document_path)) {
            return response()->json(['message' => 'Bu kullanıcı için belge yüklenmemiş.'], Response::HTTP_NOT_FOUND);
        }

        $filePath = $user->document_path;
        $fileName = 'Belge_' . $user->name . '_' . $user->surname . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath, $fileName);
        }

        return response()->json(['message' => 'Belge dosyası sunucuda bulunamadı.'], Response::HTTP_NOT_FOUND);
    }

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

    public function rejectUser($userId)
    {
        $user = User::findOrFail($userId);

        if (!empty($user->document_path) && Storage::disk('public')->exists($user->document_path)) {
            Storage::disk('public')->delete($user->document_path);
        }

        $user->delete();

        return response()->json(['message' => 'Kullanıcı kaydı başarıyla reddedildi ve silindi.'], Response::HTTP_OK);
    }

    /**
     * 📊 Yönetim Dashboard istatistikleri
     */
    public function getDashboardStats(Request $request)
    {
        try {
            // 🕒 SAAT DİLİMİ VE GÜN AYARLARI (ReviewController ile aynı mantık)
            $tz = config('app.timezone', 'Europe/Istanbul');
            $startOfDay = Carbon::today($tz)->startOfDay();
            $endOfDay   = Carbon::today($tz)->endOfDay();

            // 🧩 Kullanıcı istatistikleri
            $totalUsers = User::count();
            $pendingUsers = User::where('status', 'pending')->count();
            $approvedUsers = User::where('status', 'approved')->count();

            // 🍽 Menü istatistikleri
            $totalMenus = Menu::count();
            
            // ✅ DÜZELTME: Basit string eşleşmesi yerine tarih aralığı kullanıyoruz.
            // Bu yöntem veritabanındaki tarih formatı ne olursa olsun bugünü yakalar.
            $todayMenu = Menu::whereBetween('date', [$startOfDay, $endOfDay])->first();

            // 📆 Son 7 gün
            $startLast7 = Carbon::today($tz)->subDays(6)->startOfDay();
            $menusLast7 = Menu::where('date', '>=', $startLast7)->get(['date']);

            $last7Data = [];
            for ($i = 0; $i < 7; $i++) {
                $day = Carbon::today($tz)->subDays(6 - $i)->toDateString();
                $last7Data[$day] = 0;
            }

            foreach ($menusLast7 as $menu) {
                // Tarihi güvenli bir şekilde string'e çevir
                $menuDate = $menu->date instanceof \DateTime 
                    ? $menu->date->format('Y-m-d') 
                    : (string) substr((string)$menu->date, 0, 10);

                if (isset($last7Data[$menuDate])) {
                    $last7Data[$menuDate]++;
                }
            }

            // 📊 Aylık veri hesaplama
            // Tüm menüleri çekip PHP tarafında gruplamak yerine, son 1 yıl vs alınabilir ama şimdilik tümü kalsın.
            $menus = Menu::all(['date']);
            $monthlyData = [];

            foreach ($menus as $menu) {
                $menuDate = $menu->date instanceof \DateTime 
                    ? $menu->date->format('Y-m-d') 
                    : (string) substr((string)$menu->date, 0, 10);

                $monthKey = substr($menuDate, 0, 7); // YYYY-MM
                $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
            }

            $monthlyData = collect($monthlyData)->map(function ($count, $month) {
                return [
                    'month' => $month,
                    'count' => $count
                ];
            })->values();

            // 🍛 Son 30 günün popüler menüleri
            $since30 = Carbon::today($tz)->subDays(30)->startOfDay();
            $recentMenus = Menu::where('date', '>=', $since30)->get(['items']);

            $itemFrequency = [];
            foreach ($recentMenus as $menu) {
                foreach ($menu->items ?? [] as $item) {
                    $name = trim($item['name'] ?? '');
                    if ($name === '') continue;
                    $itemFrequency[$name] = ($itemFrequency[$name] ?? 0) + 1;
                }
            }

            arsort($itemFrequency);
            $topItems = [];
            foreach (array_slice($itemFrequency, 0, 8, true) as $name => $count) {
                $topItems[] = ['name' => $name, 'count' => $count];
            }

            // 🎯 JSON cevabı
            return response()->json([
                'userStats' => [
                    'total' => $totalUsers,
                    'pending' => $pendingUsers,
                    'approved' => $approvedUsers,
                ],
                'menuStats' => [
                    'total' => $totalMenus,
                    'today' => $todayMenu, // Artık nesne dönecek, frontend true/false kontrolü yapabilir
                    'last7Days' => $last7Data,
                    'byMonth' => $monthlyData,
                    'topItems' => $topItems,
                ],
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function getUnitStats()
    {
        try {
            // Tüm kullanıcıları çek (Sadece gerekli alanlar)
            $users = User::all(['unit', 'balance']);

            // Birimlere göre grupla
            $stats = $users->groupBy('unit')->map(function ($group, $unitName) {
                return [
                    'unit' => $unitName ?: 'Birim Belirtilmemiş', // Boşsa isim ata
                    'user_count' => $group->count(),
                    'total_balance' => $group->sum('balance')
                ];
            })->values(); // Key'leri sıfırla, array yap

            // Sıralama: En kalabalık birim en üstte olsun
            $sortedStats = $stats->sortByDesc('user_count')->values();

            return response()->json($sortedStats, 200);

        } catch (\Throwable $e) {
            \Log::error('Unit stats error: ' . $e->getMessage());
            return response()->json(['error' => 'İstatistikler alınamadı.'], 500);
        }
    }
}
