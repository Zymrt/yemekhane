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
     * Uygulamada yorum saatini ve timezone'u tek yerden yönetelim.
     */
    private string $tz = 'Europe/Istanbul';

    /**
     * Helper: Yorum başlangıç saatini getirir.
     * config/services.php içine şu şekilde ekleyebilirsin:
     *
     * 'review' => [
     *     'start_time' => env('REVIEW_START_TIME', '12:00'),
     * ],
     */
    private function getStartTime()
    {
        return config('services.review.start_time', env('REVIEW_START_TIME', '12:00'));
    }

    private function nowTz(): Carbon
    {
        return Carbon::now($this->tz);
    }

    private function todayTz(): Carbon
    {
        return Carbon::today($this->tz);
    }

    private function reviewStartAt(): Carbon
    {
        $when = $this->getStartTime(); // Örn: "12:00"
        [$hh, $mm] = array_pad(explode(':', $when, 2), 2, '0');

        return $this->todayTz()->setTime((int) $hh, (int) $mm);
    }

    private function todayBounds(): array
    {
        $start = $this->todayTz()->startOfDay();
        $end   = $this->todayTz()->endOfDay();

        return [$start, $end, $this->tz];
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
                'now_tz'           => $this->nowTz()->toDateTimeString(), // debug için
            ]);
        }

        $menuId = (string) ($menu->_id ?? $menu->id);

        // Kullanıcı siparişi var mı?
        $hasOrder = Order::where('user_id', (string)($user->_id ?? $user->id))
            ->whereBetween('date', [$startDay, $endDay])
            ->exists();

        // Saat kontrolü (Türkiye saati)
        $afterStart = $this->nowTz()->gte($this->reviewStartAt());

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
            'can_review'       => ($hasOrder && $afterStart && !$existing),
            'already'          => (bool)$existing,
            'avg'              => $avg,
            'count'            => $count,
            'my_review'        => $existing,
            'after_start'      => $afterStart,
            'has_order'        => $hasOrder,
            'review_start_raw' => $this->getStartTime(),
            'now_tz'           => $this->nowTz()->toDateTimeString(), // debug için frontendten görebilirsin
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

        // 🛡️ SAAT KONTROLÜ – HER ZAMAN TÜRKİYE SAATİ
        if (!$this->nowTz()->gte($this->reviewStartAt())) {
            return response()->json([
                'message'    => 'Yorumlar saat ' . $this->getStartTime() . ' itibarıyla açılacaktır.',
                'now_tz'     => $this->nowTz()->toDateTimeString(),      // debug
                'start_time' => $this->reviewStartAt()->toDateTimeString(), // debug
            ], 422);
        }

        // 🛡️ SATIN ALMA KONTROLÜ
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

        $menuIds = collect($paginator->items())
            ->pluck('menu_id')
            ->filter()
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

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
                    'items' => $menu->items ?? null,
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
