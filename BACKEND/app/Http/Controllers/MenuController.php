<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Log; // 🌟 LOG MODELİ EKLENDİ
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use MongoDB\BSON\ObjectId;

class MenuController extends Controller
{
    /**
     * Menü ekle veya aynı tarih varsa güncelle
     */
    public function addMenu(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.calorie' => 'nullable|numeric|min:0|max:5000',
        ]);

        $menu = Menu::updateOrCreate(
            ['date' => Carbon::parse($validated['date'])->startOfDay()],
            ['items' => $validated['items']]
        );

        // 📝 LOG TUTMA: Menü Ekleme/Güncelleme
        Log::create([
            'user_id' => $request->user()->id ?? null,
            'action' => 'Menü İşlemi',
            'description' => "{$validated['date']} tarihi için menü oluşturuldu veya güncellendi.",
            'type' => 'info',
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return response()->json(['message' => 'Menü başarıyla kaydedildi veya güncellendi.']);
    }

    /**
     * Tüm menüleri listele (Admin)
     */
    public function getAllMenus()
    {
        $menus = Menu::orderBy('date', 'desc')->get();
        return response()->json($menus);
    }

    /**
     * Menü sil
     */
    public function deleteMenu(Request $request, $id) // 🌟 Request parametresi eklendi (IP Logu için)
    {
        if (!$id || $id === 'undefined') {
            return response()->json(['message' => 'Geçersiz menü ID.'], 400);
        }

        $objectId = $this->cleanObjectId($id);
        $menu = Menu::where('_id', $objectId)->first();

        if (!$menu) {
            return response()->json(['message' => 'Menü bulunamadı.'], 404);
        }

        // Silinmeden önce tarihini alalım (Log mesajı için)
        $menuDate = $menu->date instanceof \DateTime 
            ? $menu->date->format('Y-m-d') 
            : substr((string)$menu->date, 0, 10);

        $menu->delete();

        // 📝 LOG TUTMA: Menü Silme
        Log::create([
            'user_id' => $request->user()->id ?? null,
            'action' => 'Menü Silindi',
            'description' => "{$menuDate} tarihli menü silindi.",
            'type' => 'warning',
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return response()->json(['message' => 'Menü başarıyla silindi.']);
    }

    /**
     * Menü güncelle
     */
    public function updateMenu(Request $request, $id)
    {
        if (!$id || $id === 'undefined') {
            return response()->json(['message' => 'Geçersiz menü ID.'], 400);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.calorie' => 'nullable|numeric|min:0|max:5000',
        ]);

        $objectId = $this->cleanObjectId($id);
        $menu = Menu::where('_id', $objectId)->first();

        if (!$menu) {
            return response()->json(['message' => 'Menü bulunamadı.'], 404);
        }

        $menu->date  = Carbon::parse($validated['date'])->startOfDay();
        $menu->items = $validated['items'];
        $menu->save();

        // 📝 LOG TUTMA: Menü Güncelleme
        Log::create([
            'user_id' => $request->user()->id ?? null,
            'action' => 'Menü Düzenlendi',
            'description' => "{$validated['date']} tarihli menü içeriği güncellendi.",
            'type' => 'info',
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return response()->json(['message' => 'Menü başarıyla güncellendi.']);
    }

    /**
     * Bugünün menüsünü getir
     */
    public function getTodayMenu()
    {
        // Saat dilimi sorunu yaşamamak için aralık sorgusu kullanıyoruz
        $tz = config('app.timezone', 'Europe/Istanbul');
        $start = Carbon::today($tz)->startOfDay();
        $end   = Carbon::today($tz)->endOfDay();

        $menu = Menu::whereBetween('date', [$start, $end])->first();

        if (!$menu) {
            return response()->json(['message' => 'Bugün için menü bulunamadı.'], 404);
        }

        return response()->json($menu);
    }

    /**
     * ObjectId temizleme fonksiyonu
     */
    private function cleanObjectId($id)
    {
        if (preg_match('/^[a-f\d]{24}$/i', $id)) {
            return new ObjectId($id);
        }

        if (preg_match("/ObjectId\('([a-f\d]{24})'\)/i", $id, $matches)) {
            return new ObjectId($matches[1]);
        }

        throw new \Exception("Geçersiz ObjectId formatı: $id");
    }
}