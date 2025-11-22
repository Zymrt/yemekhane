<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use MongoDB\BSON\ObjectId;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Helper: Yorum başlangıç saatini getirir.
     * config/services.php içine eklediğini varsayıyorum, yoksa env() kalabilir ama cache'de dikkat et.
     */
    private function getStartTime()
    {
        // Güvenli yöntem: config üzerinden okumak
        return config('services.review.start_time', env('REVIEW_START_TIME', '12:00'));
    }

    private function reviewStartAt(): Carbon
    {
        $tz   = config('app.timezone', 'Europe/Istanbul');
        $when = $this->getStartTime();
        [$hh, $mm] = array_pad(explode(':', $when, 2), 2, '0');
        return Carbon::today($tz)->setTime((int)$hh, (int)$mm);
    }

    private function todayBounds(): array
    {
        $tz = config('app.timezone', 'Europe/Istanbul');
        $start = Carbon::today($tz)->startOfDay();
        $end   = Carbon::today($tz)->endOfDay();
        return [$start, $end, $tz];
    }

    // GET /api/reviews/today
    public function today(Request $req)
    {
        [$startDay, $endDay, $tz] = $this->todayBounds();
        $user = $req->user();

        // Bugünün menüsü
        $menu = Menu::whereBetween('date', [$startDay, $endDay])->first();

        if (!$menu) {
            return response()->json([
                'menu'             => null,
                'can_review'       => false,
                'already'          => false,
                'avg'              => null,
                'count'            => 0,
                'my_review'        => null,
                'after_start'      => false,
                'has_order'        => false,
                'review_start_raw' => $this->getStartTime(),
            ]);
        }

        $menuId = (string) ($menu->_id ?? $menu->id);

        // Kullanıcı siparişi var mı?
        $hasOrder = Order::where('user_id', (string)($user->_id ?? $user->id))
            ->whereBetween('date', [$startDay, $endDay])
            ->exists();

        // Saat kontrolü
        $afterStart = Carbon::now($tz)->gte($this->reviewStartAt());

        // Zaten yorum yapmış mı?
        $existing = Review::where('user_id', (string)($user->_id ?? $user->id))
            ->where('menu_id', $menuId)
            ->whereBetween('date', [$startDay, $endDay])
            ->first();

        // İstatistikler
        $agg = Review::where('menu_id', $menuId)
            ->whereBetween('date', [$startDay, $endDay])
            ->get(['rating']);
        
        $count = $agg->count();
        $avg   = $count ? round($agg->avg('rating'), 2) : null;

        return response()->json([
            'menu'             => $menu,
            'can_review'       => ($hasOrder && $afterStart && !$existing), // Frontend için kritik bilgi
            'already'          => (bool)$existing,
            'avg'              => $avg,
            'count'            => $count,
            'my_review'        => $existing,
            'after_start'      => $afterStart,
            'has_order'        => $hasOrder,
            'review_start_raw' => $this->getStartTime(),
        ]);
    }

    // POST /api/reviews
    public function store(Request $req)
    {
        $data = $req->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'menu_id' => 'nullable|string',
        ]);

        [$startDay, $endDay, $tz] = $this->todayBounds();
        $user = $req->user();

        // Menü bul
        if (!empty($data['menu_id'])) {
            $menuIdObj = $this->cleanObjectId($data['menu_id']);
            $menu = Menu::where('_id', $menuIdObj)->first();
        } else {
            $menu = Menu::whereBetween('date', [$startDay, $endDay])->first();
        }

        if (!$menu) {
            return response()->json(['message' => 'Bugün için menü bulunamadı.'], Response::HTTP_NOT_FOUND);
        }
        $menuId = (string) ($menu->_id ?? $menu->id);

        // 🛡️ GÜVENLİK DUVARI: Saat Kontrolü
        if (!Carbon::now($tz)->gte($this->reviewStartAt())) {
            return response()->json([
                'message' => 'Yorumlar saat ' . $this->getStartTime() . ' itibarıyla açılacaktır.'
            ], 422);
        }

        // 🛡️ GÜVENLİK DUVARI: Satın Alma Kontrolü
        $hasOrder = Order::where('user_id', (string)($user->_id ?? $user->id))
            ->whereBetween('date', [$startDay, $endDay])
            ->exists();

        if (!$hasOrder) {
            return response()->json(['message' => 'Yorum yapabilmek için bugün menü satın almalısın.'], 403);
        }

        // Kaydet veya Güncelle
        $review = Review::updateOrCreate(
            [
                'user_id' => (string)($user->_id ?? $user->id),
                'menu_id' => $menuId,
                'date'    => $startDay, 
            ],
            [
                'rating'  => (int)$data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        return response()->json(['message' => 'Yorumun başarıyla kaydedildi.', 'review' => $review], 201);
    }

    // GET /api/reviews/menu/{menuId}
    public function forMenu(Request $req, $menuId)
    {
        [$startDay, $endDay] = $this->todayBounds();
        $menuIdObj = $this->cleanObjectId($menuId);

        $items = Review::where('menu_id', (string)$menuIdObj)
            ->whereBetween('date', [$startDay, $endDay])
            ->orderBy('_id', 'desc')
            ->limit(200)
            ->get();

        $count = $items->count();
        $avg   = $count ? round($items->avg('rating'), 2) : null;

        return response()->json(['avg' => $avg, 'count' => $count, 'items' => $items]);
    }

    public function myReviews(Request $req)
    {
        $user = $req->user();
        $perPage = (int) $req->query('per_page', 10);
        $page = (int) $req->query('page', 1);

        $query = Review::where('user_id', (string)($user->_id ?? $user->id))
            ->orderBy('created_at', 'desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // ID'leri topla
        $menuIds = collect($paginator->items())
            ->pluck('menu_id')
            ->filter()
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

        // Menü detaylarını al (ObjectId dönüşümü gerekebilir, burası MongoDB configine göre değişir ama genelde string çalışır)
        // Eğer çalışmazsa burada whereIn içinde cleanObjectId kullanmak gerekebilir.
        $menus = Menu::whereIn('_id', $menuIds)->get()->keyBy(function ($m) {
            return (string) ($m->_id ?? $m->id);
        });

        $data = collect($paginator->items())->map(function ($r) use ($menus) {
            $menu = $menus[(string)$r->menu_id] ?? null;
            return [
                'id'         => (string) ($r->_id ?? $r->id),
                'menu_id'    => (string) $r->menu_id,
                'rating'     => (int) ($r->rating ?? 0),
                'comment'    => (string) ($r->comment ?? ''),
                'created_at' => optional($r->created_at)->toISOString(),
                'menu'       => $menu ? [
                    'id'    => (string) ($menu->_id ?? $menu->id),
                    'date'  => optional($menu->date)->toISOString() ?? (string)($menu->date ?? ''),
                    'items' => $menu->items ?? null, // Menü içeriği (Çorba, Pilav vs.)
                ] : null,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    private function cleanObjectId($id)
    {
        if ($id instanceof ObjectId) return $id;
        if (preg_match('/^[a-f\d]{24}$/i', (string)$id)) return new ObjectId((string)$id);
        if (preg_match("/ObjectId\('([a-f\d]{24})'\)/i", (string)$id, $m)) return new ObjectId($m[1]);
        throw new \InvalidArgumentException('Geçersiz ObjectId');
    }
}