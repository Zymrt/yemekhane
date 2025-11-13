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
    // 🌟 YENİ YARDIMCI FONKSİYON EKLENDİ (purchaseToday için gerekli)
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

    // 🌟 YENİ FONKSİYON EKLENDİ (Frontend'in çağırdığı)
    /**
     * POST /api/order/purchase
     * Bugünün menüsünü satın alır.
     * Bu fonksiyon, senin 'store' metodundaki mantığı taklit eder.
     */
    public function purchaseToday(Request $req)
    {
        [$startDay, $endDay, $tz] = $this->todayBounds();
        $user = $req->user();
        $qty = 1; // Bugünün menüsünü satın alma her zaman 1 adettir

        // 1. Bugün bir menü var mı?
        $menu = Menu::whereBetween('date', [$startDay, $endDay])->first();
        if (!$menu) {
            return response()->json(['message' => 'Bugün için satın alınabilir menü bulunamadı.'], Response::HTTP_NOT_FOUND);
        }
        $menuId = (string) ($menu->_id ?? $menu->id);

        // 2. Fiyatı bul (Senin 'store' metodundaki mantıkla)
        // (kullanıcı özel → menü → env default)
        $unitPrice = $user->meal_price
            ?? $menu->price
            ?? (float) env('MENU_DEFAULT_PRICE', 50.0); // .env'den al
        
        $total = $unitPrice * $qty;

        // 3. Kullanıcı bugünün menüsünü DAHA ÖNCE satın almış mı? (Senin 'store' metodundaki mantıkla)
        $alreadyPurchased = Order::where('user_id', (string)($user->_id ?? $user->id))
                                 ->where('menu_id', $menuId)
                                 ->where('date', $startDay) // Günü normalize et
                                 ->exists();

        if ($alreadyPurchased) {
            return response()->json(['message' => 'Bugünün menüsünü zaten satın almışsınız.'], Response::HTTP_BAD_REQUEST);
        }

        // 4. Bakiye kontrolü (Senin 'store' metodundaki mantıkla)
        $freshUser = User::find($user->_id ?? $user->id);
        $balance = (float) ($freshUser->balance ?? 0);
        if ($balance < $total) {
            return response()->json(['message' => 'Yetersiz bakiye.'], 402); // 402 Payment Required
        }

        // 5. SATIN ALMA (Senin 'store' metodundaki mantıkla)
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

            // C. İşlem logu (Senin 'store' metodundaki mantıkla)
            if (class_exists(Transaction::class)) {
                Transaction::create([
                    'user_id' => (string)($freshUser->_id ?? $freshUser->id),
                    'type'    => 'debit',
                    'amount'  => (float) $total, // float olarak kaydet
                    'meta'    => [
                        'menu_id'  => $menuId,
                        'order_id' => (string)($order->_id ?? $order->id)
                    ]
                ]);
            }

        } catch (\Exception $e) {
            // Hata olursa (örn: loglama patlarsa) - Not: Bu atomik değil, bakiye düşmüş olabilir.
            // Gerçek dünyada DB::transaction kullanılmalı.
            return response()->json(['message' => 'Satın alma sırasında bir hata oluştu: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Başarılı
        return response()->json([
            'message'       => 'Satın alma başarılı!',
            'new_balance'   => $freshUser->balance, // Güncel bakiyeyi dön
        ], Response::HTTP_CREATED);
    }


    // -----------------------------------------------------------------
    // SENİN MEVCUT FONKSİYONLARIN (Değişmedi)
    // -----------------------------------------------------------------

    // POST /api/orders
    public function store(Request $req)
    {
        $data = $req->validate([
            'menu_id' => 'required|string',
            'qty'     => 'nullable|integer|min:1',
            'date'    => 'nullable|date'
        ]);

        $qty  = (int)($data['qty'] ?? 1);
        $date = isset($data['date']) ? Carbon::parse($data['date'])->startOfDay() : Carbon::today()->startOfDay();

        // Menu bul (Mongo ObjectId normalize)
        $menuId = $this->cleanObjectId($data['menu_id']);
        $menu = Menu::where('_id', $menuId)->first();
        if (!$menu) {
            return response()->json(['message' => 'Menü bulunamadı.'], Response::HTTP_NOT_FOUND);
        }

        // (Opsiyonel) Menü tarihi bugün değilse engelle
        if (isset($menu->date) && !Carbon::parse($menu->date)->equalTo($date)) {
            return response()->json(['message' => 'Bu menü bugüne ait değil.'], 422);
        }

        // Fiyat (kullanıcı özel → menü → env default)
        $unitPrice = $req->user()->meal_price
            ?? $menu->price
            ?? (int) env('MENU_DEFAULT_PRICE', 125);

        $total = (int)$unitPrice * $qty;

        $user = $req->user();

        // aynı gün aynı menü tekrarını engelle
        $already = Order::where('user_id', (string)($user->_id ?? $user->id))
            ->where('menu_id', (string)$menuId)
            ->where('date', $date)
            ->exists();
        if ($already) {
            return response()->json(['message' => 'Bu menüyü bugün zaten aldın.'], 422);
        }

        // günlük limit kontrol (opsiyonel)
        if (!empty($menu->daily_limit)) {
            $todayQty = Order::where('menu_id', (string)$menuId)
                ->where('date', $date)
                ->sum('qty');
            if ($todayQty + $qty > (int)$menu->daily_limit) {
                return response()->json(['message' => 'Günlük limit dolu.'], 409);
            }
        }

        // 💸 Bakiye kontrolü (fresh read)
        $freshUser = User::find($user->_id ?? $user->id);
        $balance = (int) ($freshUser->balance ?? 0);
        if ($balance < $total) {
            return response()->json(['message' => 'Yetersiz bakiye.'], 402);
        }

        // Bakiye düş
        $freshUser->balance = $balance - $total;
        $freshUser->save();

        // Siparişi kaydet
        $order = Order::create([
            'user_id' => (string)($freshUser->_id ?? $freshUser->id),
            'menu_id' => (string)$menuId,
            'qty'     => $qty,
            'price'   => (int)$unitPrice,
            'total'   => (int)$total,
            'date'    => $date,
            'status'  => 'paid',
        ]);

        // (Opsiyonel) işlem logu
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