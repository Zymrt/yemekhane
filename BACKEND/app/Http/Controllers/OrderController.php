<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use MongoDB\BSON\ObjectId;

class OrderController extends Controller
{
    /**
     * Bugünün başlangıç/bitiş aralığı (TZ güvenli)
     */
    private function todayBounds(): array
    {
        $tz = config('app.timezone', 'Europe/Istanbul');
        $start = Carbon::today($tz)->startOfDay();
        $end   = Carbon::today($tz)->endOfDay();
        return [$start, $end, $tz];
    }

    /**
     * POST /api/order/purchase
     * Bugünün menüsünü satın alır.
     */
    public function purchaseToday(Request $req)
    {
        // ⏰ 1. SAAT KONTROLÜ (YENİ EKLENEN KISIM)
        // Şu anki saat Türkiye saatiyle 12:00 veya daha ileriyse işlemi reddet.
        $tz = config('app.timezone', 'Europe/Istanbul');
        $now = Carbon::now($tz);

        if ($now->format('H:i') >= '12:00') {
            return response()->json([
                'message' => 'Üzgünüz, bugün için yemek satın alma süresi (12:00) dolmuştur.'
            ], 403); // 403: Yasaklandı
        }

        // --- MEVCUT KODLARIN DEVAMI ---

        [$startDay, $endDay] = $this->todayBounds();
        $user = $req->user();
        $qty = 1; 

        // 2. Bugün bir menü var mı?
        $menu = Menu::whereBetween('date', [$startDay, $endDay])->first();
        if (!$menu) {
            return response()->json(['message' => 'Bugün için satın alınabilir menü bulunamadı.'], Response::HTTP_NOT_FOUND);
        }
        $menuId = (string) ($menu->_id ?? $menu->id);

        // 3. Fiyatı bul
        $unitPrice = $user->meal_price
            ?? $menu->price
            ?? (float) env('MENU_DEFAULT_PRICE', 50.0);
        
        $total = $unitPrice * $qty;

        // 4. Daha önce satın almış mı?
        $alreadyPurchased = Order::where('user_id', (string)($user->_id ?? $user->id))
                                 ->where('menu_id', $menuId)
                                 ->where('date', $startDay)
                                 ->exists();

        if ($alreadyPurchased) {
            return response()->json(['message' => 'Bugünün menüsünü zaten satın almışsınız.'], Response::HTTP_BAD_REQUEST);
        }

        // 5. Bakiye kontrolü
        $freshUser = User::find($user->_id ?? $user->id);
        $balance = (float) ($freshUser->balance ?? 0);
        if ($balance < $total) {
            return response()->json(['message' => 'Yetersiz bakiye. Lütfen bakiye yükleyin.'], 402);
        }

        // 6. SATIN ALMA İŞLEMİ
        try {
            // A. Bakiye düş
            $freshUser->balance = $balance - $total;
            $freshUser->save();

            // B. Siparişi kaydet
            $order = Order::create([
                'user_id' => (string)($freshUser->_id ?? $freshUser->id),
                'menu_id' => $menuId,
                'qty'     => $qty,
                'price'   => $unitPrice,
                'total'   => $total,
                'date'    => $startDay,
                'status'  => 'paid',
            ]);

            // C. İşlem logu (Transaction)
            if (class_exists(Transaction::class)) {
                Transaction::create([
                    'user_id' => (string)($freshUser->_id ?? $freshUser->id),
                    'type'    => 'debit', // Harcama
                    'amount'  => (float) $total,
                    'meta'    => [
                        'menu_id'  => $menuId,
                        'order_id' => (string)($order->_id ?? $order->id),
                        'desc'     => 'Yemek Satın Alma'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Satın alma sırasında teknik bir hata oluştu: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message'     => 'Afiyet olsun! Satın alma işlemi başarılı.',
            'new_balance' => $freshUser->balance,
        ], Response::HTTP_CREATED);
    }


    // -----------------------------------------------------------------
    // DİĞER YARDIMCI FONKSİYONLAR (Aynı kaldı)
    // -----------------------------------------------------------------

    // POST /api/orders (Manuel veya Admin girişi için opsiyonel)
    public function store(Request $req)
    {
        $data = $req->validate([
            'menu_id' => 'required|string',
            'qty'     => 'nullable|integer|min:1',
            'date'    => 'nullable|date'
        ]);

        $qty  = (int)($data['qty'] ?? 1);
        $date = isset($data['date']) ? Carbon::parse($data['date'])->startOfDay() : Carbon::today()->startOfDay();

        // Menu bul
        $menuId = $this->cleanObjectId($data['menu_id']);
        $menu = Menu::where('_id', $menuId)->first();
        if (!$menu) {
            return response()->json(['message' => 'Menü bulunamadı.'], Response::HTTP_NOT_FOUND);
        }

        // Fiyat hesapla
        $unitPrice = $req->user()->meal_price
            ?? $menu->price
            ?? (int) env('MENU_DEFAULT_PRICE', 125);

        $total = (int)$unitPrice * $qty;
        $user = $req->user();

        // Mükerrer kontrolü
        $already = Order::where('user_id', (string)($user->_id ?? $user->id))
            ->where('menu_id', (string)$menuId)
            ->where('date', $date)
            ->exists();
        if ($already) {
            return response()->json(['message' => 'Bu menüyü bugün zaten aldın.'], 422);
        }

        // Bakiye kontrolü
        $freshUser = User::find($user->_id ?? $user->id);
        $balance = (int) ($freshUser->balance ?? 0);
        if ($balance < $total) {
            return response()->json(['message' => 'Yetersiz bakiye.'], 402);
        }

        // İşlem
        $freshUser->balance = $balance - $total;
        $freshUser->save();

        $order = Order::create([
            'user_id' => (string)($freshUser->_id ?? $freshUser->id),
            'menu_id' => (string)$menuId,
            'qty'     => $qty,
            'price'   => (int)$unitPrice,
            'total'   => (int)$total,
            'date'    => $date,
            'status'  => 'paid',
        ]);

        if (class_exists(Transaction::class)) {
            Transaction::create([
                'user_id' => (string)($freshUser->_id ?? $freshUser->id),
                'type'    => 'debit',
                'amount'  => (int)$total,
                'meta'    => [
                    'menu_id'  => (string)$menuId,
                    'order_id' => (string)($order->_id ?? $order->id)
                ]
            ]);
        }

        return response()->json([
            'message'     => 'Satın alma başarılı.',
            'order'       => $order,
            'new_balance' => (int)$freshUser->balance,
        ], 201);
    }

    // GET /api/orders/my
    public function myOrders(Request $req)
    {
        $user = $req->user();
        $orders = Order::with('menu')
            ->where('user_id', (string)($user->_id ?? $user->id))
            ->orderBy('date','desc')
            ->limit(100)
            ->get();

        return response()->json($orders);
    }

    private function cleanObjectId($id)
    {
        if ($id instanceof ObjectId) return $id;
        if (preg_match('/^[a-f\d]{24}$/i', (string)$id)) return new ObjectId((string)$id);
        if (preg_match("/ObjectId\('([a-f\d]{24})'\)/i", (string)$id, $m)) return new ObjectId($m[1]);
        throw new \InvalidArgumentException('Geçersiz ObjectId');
    }
}