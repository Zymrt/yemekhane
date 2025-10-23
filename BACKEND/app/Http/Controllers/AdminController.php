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
        // 🧩 Kullanıcı İstatistikleri
        $totalUsers = User::count();
        $pendingUsers = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();

        // 🍽 Menü İstatistikleri
        $totalMenus = Menu::count();
        $todayMenu = Menu::whereDate('date', Carbon::today())->first();

        // 📆 Son 7 Günlük Menü Sayısı
        $startDate = Carbon::today()->subDays(6);
        $last7Menus = Menu::where('date', '>=', $startDate)->get(['date']);
        $last7Data = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startDate->copy()->addDays($i)->format('Y-m-d');
            $last7Data[$day] = 0;
        }
        foreach ($last7Menus as $menu) {
            $key = Carbon::parse($menu->date)->format('Y-m-d');
            if (isset($last7Data[$key])) {
                $last7Data[$key]++;
            }
        }

        // 📈 Aylık Menü Grafiği (yıl-ay bazında)
        $monthlyData = Menu::selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // 🍛 Son 30 Günün En Popüler Menüleri
        $since30 = Carbon::today()->subDays(30);
        $menus = Menu::where('date', '>=', $since30)->get(['items']);
        $itemFrequency = [];

        foreach ($menus as $menu) {
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
        ]);
    }
}
