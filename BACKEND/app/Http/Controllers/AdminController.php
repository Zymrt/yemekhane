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
     * Onay bekleyen kullanıcıları listeler (status: 'pending').
     */
    public function getPendingUsers()
    {
        $pendingUsers = User::where('status', 'pending')
            ->select('_id', 'name', 'surname', 'phone', 'unit', 'document_path', 'created_at')
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

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath, $fileName);
        }

        return response()->json(['message' => 'Belge dosyası sunucuda bulunamadı.'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Belirtilen kullanıcıyı onaylar.
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

        if (!empty($user->document_path) && Storage::disk('public')->exists($user->document_path)) {
            Storage::disk('public')->delete($user->document_path);
        }

        $user->delete();

        return response()->json(['message' => 'Kullanıcı kaydı başarıyla reddedildi ve silindi.'], Response::HTTP_OK);
    }

    /**
     * 📊 Dashboard istatistiklerini getirir.
     */
    public function getDashboardStats(Request $request)
    {
        $totalMenus = Menu::count();
        $todayMenu = Menu::where('date', Carbon::today()->startOfDay())->first();

        $byMonth = Menu::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id'   => ['year' => ['$year' => '$date'], 'month' => ['$month' => '$date']],
                        'count' => ['$sum' => 1],
                    ]
                ],
                ['$sort' => ['_id.year' => 1, '_id.month' => 1]]
            ]);
        });

        $start7 = Carbon::today()->subDays(6)->startOfDay();
        $last7 = Menu::where('date', '>=', $start7)->get(['date']);
        $last7Map = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $start7->copy()->addDays($i)->format('Y-m-d');
            $last7Map[$d] = 0;
        }
        foreach ($last7 as $m) {
            $key = Carbon::parse($m->date)->format('Y-m-d');
            if (isset($last7Map[$key])) $last7Map[$key] += 1;
        }

        $totalUsers = User::count();
        $pendingUsers = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();

        $since30 = Carbon::today()->subDays(30)->startOfDay();
        $popularItems = Menu::where('date', '>=', $since30)->get(['items']);
        $freq = [];
        foreach ($popularItems as $menu) {
            foreach (($menu->items ?? []) as $it) {
                $name = trim($it['name'] ?? '');
                if ($name === '') continue;
                $freq[$name] = ($freq[$name] ?? 0) + 1;
            }
        }
        arsort($freq);
        $topItems = [];
        foreach (array_slice($freq, 0, 8, true) as $k => $v) {
            $topItems[] = ['name' => $k, 'count' => $v];
        }

        return response()->json([
            'menuStats' => [
                'total'   => $totalMenus,
                'today'   => $todayMenu,
                'byMonth' => array_values(array_map(function ($row) {
                    return [
                        'year'  => $row->_id->year ?? $row->_id['year'] ?? null,
                        'month' => $row->_id->month ?? $row->_id['month'] ?? null,
                        'count' => $row->count ?? 0
                    ];
                }, iterator_to_array($byMonth))),
                'last7'   => $last7Map,
                'topItems'=> $topItems,
            ],
            'userStats' => [
                'total'    => $totalUsers,
                'pending'  => $pendingUsers,
                'approved' => $approvedUsers,
            ],
        ]);
    }
}
