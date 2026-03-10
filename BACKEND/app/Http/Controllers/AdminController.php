<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // 🔹 PDF export için

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

    public function approveUser(Request $request, $userId)
    {
        $request->validate([
            'meal_price' => 'required|numeric|min:0',
            'unit' => 'nullable|string'
        ]);

        $user = User::findOrFail($userId);

        $user->status = 'approved';
        $user->meal_price = $request->input('meal_price');
        
        if ($request->has('unit')) {
            $user->unit = $request->input('unit');
        }
        
        $user->save();

        return response()->json(['message' => 'Onaylandı.'], 200);
    }

    public function updateUserPrice(Request $request, $id)
    {
        $request->validate([
            'meal_price' => 'nullable|numeric|min:0',
            'unit'       => 'nullable|string'
        ]);

        $user = User::findOrFail($id);

        if ($request->has('meal_price')) {
            $user->meal_price = $request->input('meal_price');
        }

        if ($request->has('unit')) {
            $user->unit = $request->input('unit');
        }

        $user->save();

        return response()->json([
            'message' => 'Kullanıcı bilgileri güncellendi.',
            'user'    => $user
        ], Response::HTTP_OK);
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

    public function getFinanceStats()
    {
        try {
            $totalUserBalance = User::sum('balance');
            $totalDeposits = \App\Models\Transaction::where('type', 'deposit')->sum('amount');
            
            $recentTransactions = \App\Models\Transaction::with('user:id,name,surname,unit')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'total_balance' => $totalUserBalance,
                'total_deposits' => $totalDeposits,
                'recent_transactions' => $recentTransactions
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Finans verileri alınamadı: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 📊 Yönetim Dashboard istatistikleri
     */
    public function getDashboardStats(Request $request)
    {
        try {
            $tz = config('app.timezone', 'Europe/Istanbul');
            $startOfDay = Carbon::today($tz)->startOfDay();
            $endOfDay   = Carbon::today($tz)->endOfDay();

            $totalUsers = User::count();
            $pendingUsers = User::where('status', 'pending')->count();
            $approvedUsers = User::where('status', 'approved')->count();

            $totalMenus = Menu::count();
            
            $todayMenu = Menu::whereBetween('date', [$startOfDay, $endOfDay])->first();

            $startLast7 = Carbon::today($tz)->subDays(6)->startOfDay();
            $menusLast7 = Menu::where('date', '>=', $startLast7)->get(['date']);

            $last7Data = [];
            for ($i = 0; $i < 7; $i++) {
                $day = Carbon::today($tz)->subDays(6 - $i)->toDateString();
                $last7Data[$day] = 0;
            }

            foreach ($menusLast7 as $menu) {
                $menuDate = $menu->date instanceof \DateTime 
                    ? $menu->date->format('Y-m-d') 
                    : (string) substr((string)$menu->date, 0, 10);

                if (isset($last7Data[$menuDate])) {
                    $last7Data[$menuDate]++;
                }
            }

            $menus = Menu::all(['date']);
            $monthlyData = [];

            foreach ($menus as $menu) {
                $menuDate = $menu->date instanceof \DateTime 
                    ? $menu->date->format('Y-m-d') 
                    : (string) substr((string)$menu->date, 0, 10);

                $monthKey = substr($menuDate, 0, 7);
                $monthlyData[$monthKey] = ($monthlyData[$monthKey] ?? 0) + 1;
            }

            $monthlyData = collect($monthlyData)->map(function ($count, $month) {
                return [
                    'month' => $month,
                    'count' => $count
                ];
            })->values();

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
            \Log::error('Dashboard error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    /**
     * 📊 Menü istatistikleri (Toplam menü + son güncelleme)
     */
    public function getMenuStats()
    {
        try {
            $totalMenus = Menu::count();
            $lastMenu = Menu::orderBy('updated_at', 'desc')->first();

            return response()->json([
                'total_menus'  => $totalMenus,
                'last_updated' => $lastMenu ? $lastMenu->updated_at : null,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Menu stats error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Menü istatistikleri alınamadı.',
            ], 500);
        }
    }

    public function getUnitStats()
    {
        try {
            $users = User::all(['unit', 'balance']);

            $stats = $users->groupBy('unit')->map(function ($group, $unitName) {
                return [
                    'unit' => $unitName ?: 'Birim Belirtilmemiş',
                    'user_count' => $group->count(),
                    'total_balance' => $group->sum('balance')
                ];
            })->values();

            $sortedStats = $stats->sortByDesc('user_count')->values();

            return response()->json($sortedStats, 200);

        } catch (\Throwable $e) {
            \Log::error('Unit stats error: ' . $e->getMessage());
            return response()->json(['error' => 'İstatistikler alınamadı.'], 500);
        }
    }

    /**
     * 📤 BİRİM İSTATİSTİKLERİNİ DIŞA AKTAR (Excel/PDF)
     */
    public function exportUnitStats($format)
    {
        try {
            $users = User::all(['unit', 'balance']);

            $stats = $users->groupBy('unit')->map(function ($group, $unitName) {
                return [
                    'unit' => $unitName ?: 'Birim Belirtilmemiş',
                    'user_count' => $group->count(),
                    'total_balance' => $group->sum('balance')
                ];
            })->values()->toArray();

            if ($format === 'excel') {
                $filename = 'birim-istatistikleri-' . now()->format('Ymd_His') . '.csv';

                $headers = [
                    'Content-Type'        => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
                ];

                $callback = function () use ($stats) {
                    $handle = fopen('php://output', 'w');
                    // UTF-8 BOM (Excel Türkçe için)
                    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                    fputcsv($handle, ['Birim', 'Kullanıcı Sayısı', 'Toplam Bakiye']);

                    foreach ($stats as $row) {
                        fputcsv($handle, [
                            $row['unit'],
                            $row['user_count'],
                            $row['total_balance'],
                        ]);
                    }

                    fclose($handle);
                };

                return response()->stream($callback, 200, $headers);
            }

            if ($format === 'pdf') {
                $html  = '<h1 style="font-family: sans-serif; margin-bottom: 10px;">Birim Bazlı İstatistikler</h1>';
                $html .= '<table width="100%" cellspacing="0" cellpadding="4" style="font-family:sans-serif;font-size:12px;border-collapse:collapse;">';
                $html .= '<thead><tr>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Birim</th>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Kullanıcı Sayısı</th>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Toplam Bakiye (₺)</th>';
                $html .= '</tr></thead><tbody>';

                foreach ($stats as $row) {
                    $html .= '<tr>';
                    $html .= '<td style="border:1px solid #ddd;">' . e($row['unit']) . '</td>';
                    $html .= '<td style="border:1px solid #ddd; text-align:right;">' . e($row['user_count']) . '</td>';
                    $html .= '<td style="border:1px solid #ddd; text-align:right;">' . number_format($row['total_balance'], 2, ',', '.') . '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody></table>';

                $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

                return $pdf->download('birim-istatistikleri-' . now()->format('Ymd_His') . '.pdf');
            }

            return response()->json(['error' => 'Geçersiz format. excel veya pdf gönderin.'], 400);

        } catch (\Throwable $e) {
            \Log::error('Export unit stats error: ' . $e->getMessage());
            return response()->json(['error' => 'Dışa aktarma başarısız oldu.'], 500);
        }
    }

    /**
     * 📤 FİNANS İSTATİSTİKLERİNİ DIŞA AKTAR (Excel/PDF)
     */
    public function exportFinanceStats($format)
    {
        try {
            $totalUserBalance = User::sum('balance');
            $totalDeposits = \App\Models\Transaction::where('type', 'deposit')->sum('amount');
            
            $recentTransactions = \App\Models\Transaction::with('user:id,name,surname,unit')
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();

            if ($format === 'excel') {
                $filename = 'finans-raporu-' . now()->format('Ymd_His') . '.csv';

                $headers = [
                    'Content-Type'        => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
                ];

                $callback = function () use ($totalUserBalance, $totalDeposits, $recentTransactions) {
                    $handle = fopen('php://output', 'w');
                    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                    // Özet satırlar
                    fputcsv($handle, ['Toplam Kullanıcı Bakiyesi (₺)', $totalUserBalance]);
                    fputcsv($handle, ['Toplam Yükleme (₺)', $totalDeposits]);
                    fputcsv($handle, []); // boş satır

                    // Detay tablo başlığı
                    fputcsv($handle, ['Tarih', 'Kullanıcı', 'Birim', 'Tutar (₺)', 'Tür']);

                    foreach ($recentTransactions as $t) {
                        $userName = $t->user ? ($t->user->name . ' ' . $t->user->surname) : '-';
                        $unit = $t->user->unit ?? '-';
                        fputcsv($handle, [
                            $t->created_at ? $t->created_at->format('d.m.Y H:i') : '',
                            $userName,
                            $unit,
                            $t->amount,
                            $t->type,
                        ]);
                    }

                    fclose($handle);
                };

                return response()->stream($callback, 200, $headers);
            }

            if ($format === 'pdf') {
                $html  = '<h1 style="font-family:sans-serif; margin-bottom:6px;">Finans Raporu</h1>';
                $html .= '<p style="font-family:sans-serif;font-size:12px;">';
                $html .= 'Toplam Kullanıcı Bakiyesi: <strong>' . number_format($totalUserBalance, 2, ',', '.') . ' ₺</strong><br>';
                $html .= 'Toplam Yükleme: <strong>' . number_format($totalDeposits, 2, ',', '.') . ' ₺</strong>';
                $html .= '</p>';

                $html .= '<h3 style="font-family:sans-serif;margin:10px 0 4px;">Son İşlemler</h3>';
                $html .= '<table width="100%" cellspacing="0" cellpadding="4" style="font-family:sans-serif;font-size:11px;border-collapse:collapse;">';
                $html .= '<thead><tr>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Tarih</th>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Kullanıcı</th>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Birim</th>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Tutar (₺)</th>';
                $html .= '<th style="border:1px solid #ccc;background:#f3f3f3;">Tür</th>';
                $html .= '</tr></thead><tbody>';

                foreach ($recentTransactions as $t) {
                    $userName = $t->user ? ($t->user->name . ' ' . $t->user->surname) : '-';
                    $unit = $t->user->unit ?? '-';
                    $html .= '<tr>';
                    $html .= '<td style="border:1px solid #ddd;">' . ($t->created_at ? $t->created_at->format('d.m.Y H:i') : '') . '</td>';
                    $html .= '<td style="border:1px solid #ddd;">' . e($userName) . '</td>';
                    $html .= '<td style="border:1px solid #ddd;">' . e($unit) . '</td>';
                    $html .= '<td style="border:1px solid #ddd; text-align:right;">' . number_format($t->amount, 2, ',', '.') . '</td>';
                    $html .= '<td style="border:1px solid #ddd;">' . e($t->type) . '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody></table>';

                $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

                return $pdf->download('finans-raporu-' . now()->format('Ymd_His') . '.pdf');
            }

            return response()->json(['error' => 'Geçersiz format. excel veya pdf gönderin.'], 400);

        } catch (\Throwable $e) {
            \Log::error('Export finance stats error: ' . $e->getMessage());
            return response()->json(['error' => 'Dışa aktarma başarısız oldu.'], 500);
        }
    }

    // --- 📢 DUYURU SİSTEMİ (GÜNCELLENDİ) ---

    public function getAnnouncements()
    {
        return response()->json(Announcement::orderBy('created_at', 'desc')->get());
    }

    public function createAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => true
        ]);

        try {
            $socketUrl = env('SOCKET_SERVER_URL', 'http://localhost:3001');
            Http::post("{$socketUrl}/api/announcement-posted", [
                'title' => $request->title
            ]);
        } catch (\Exception $e) {
            \Log::warning('Socket duyuru hatası: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Duyuru yayınlandı.'], 201);
    }

    public function deleteAnnouncement($id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['message' => 'Duyuru bulunamadı veya zaten silinmiş.'], 404);
        }

        $announcement->delete();

        try {
            $socketUrl = env('SOCKET_SERVER_URL', 'http://localhost:3001');
            Http::post("{$socketUrl}/api/announcement-deleted", [
                'id' => $id
            ]);
        } catch (\Exception $e) {
            \Log::warning('Socket silme bildirimi hatası: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Duyuru başarıyla silindi.']);
    }

    // --- 💬 YORUM SİSTEMİ (Admin Görüntüleme) ---

    public function getAllReviews()
    {
        $reviews = Review::with('user:id,name,surname,unit')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }
}
