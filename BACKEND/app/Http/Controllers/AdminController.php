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
    public function getPendingUsers()
    {
        $pendingUsers = User::where('status', 'pending')
            ->select('_id', 'name', 'surname', 'phone', 'unit', 'document_path', 'created_at')
            ->get();

        return response()->json($pendingUsers, Response::HTTP_OK);
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
        // 🧩 Kullanıcı istatistikleri
        $totalUsers = User::count();
        $pendingUsers = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();

        // 📅 Bugünün tarihi (string olarak)
        $today = now()->toDateString();

        // 🍽 Menü istatistikleri
        $totalMenus = Menu::count();
        $todayMenu = Menu::where('date', $today)->first(); // ✅ Mongo için doğrudan string karşılaştırma

        // 📆 Son 7 gün
        $startDate = now()->subDays(6)->toDateString();
        $menusLast7 = Menu::where('date', '>=', $startDate)->get(['date']);

        $last7Data = [];
        for ($i = 0; $i < 7; $i++) {
            $day = now()->subDays(6 - $i)->toDateString();
            $last7Data[$day] = 0;
        }

        foreach ($menusLast7 as $menu) {
            $menuDate = is_string($menu->date)
                ? $menu->date
                : (string) \Carbon\Carbon::parse($menu->date)->toDateString();

            if (isset($last7Data[$menuDate])) {
                $last7Data[$menuDate]++;
            }
        }

        // 📊 Aylık veri hesaplama (manuel)
        $menus = Menu::all(['date']);
        $monthlyData = [];

        foreach ($menus as $menu) {
            $menuDate = is_string($menu->date)
                ? $menu->date
                : (string) \Carbon\Carbon::parse($menu->date)->toDateString();

            $monthKey = substr($menuDate, 0, 7); // YYYY-MM formatı
            $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
        }

        $monthlyData = collect($monthlyData)->map(function ($count, $month) {
            return [
                'month' => $month,
                'count' => $count
            ];
        })->values();

        // 🍛 Son 30 günün popüler menüleri
        $since30 = now()->subDays(30)->toDateString();
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
                'today' => $todayMenu,
                'last7Days' => $last7Data,
                'byMonth' => $monthlyData,
                'topItems' => $topItems,
            ],
        ], 200);
    } catch (\Throwable $e) {
        // ❌ Hata durumunda log ve JSON hata cevabı
        \Log::error('Dashboard error: ' . $e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ], 500);
    }
}

}
