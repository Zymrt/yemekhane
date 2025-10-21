<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Carbon;
use MongoDB\BSON\ObjectId;

class MenuController extends Controller
{
    /**
     * Günün menüsünü getirir (tarih bazlı)
     */
    public function getTodayMenu()
    {
        $today = Carbon::today()->startOfDay();
        $menu = Menu::where('date', $today)->first();

        if (!$menu) {
            return response()->json(['message' => 'Bugün için menü bulunamadı.'], 404);
        }

        return response()->json($menu);
    }

    /**
     * Yeni menü ekler veya aynı gün varsa günceller
     */
    public function addMenu(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:255',
        ]);

        Menu::updateOrCreate(
            ['date' => Carbon::parse($validated['date'])->startOfDay()],
            ['items' => $validated['items']]
        );

        return response()->json(['message' => 'Menü başarıyla kaydedildi veya güncellendi.']);
    }

    /**
     * Tüm menüleri getir (admin için)
     */
    public function getAllMenus()
    {
        $menus = Menu::orderBy('date', 'desc')->get();
        return response()->json($menus);
    }

    /**
     * Menü güncelle
     */
    public function updateMenu(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:255',
        ]);

        $objectId = $this->cleanObjectId($id);
        $menu = Menu::where('_id', $objectId)->first();

        if (!$menu) {
            return response()->json(['message' => 'Menü bulunamadı.'], 404);
        }

        $menu->date  = Carbon::parse($validated['date'])->startOfDay();
        $menu->items = $validated['items'];
        $menu->save();

        return response()->json(['message' => 'Menü başarıyla güncellendi.']);
    }

    /**
     * Menü sil
     */
    public function deleteMenu($id)
    {
        $objectId = $this->cleanObjectId($id);
        $menu = Menu::where('_id', $objectId)->first();

        if (!$menu) {
            return response()->json(['message' => 'Menü bulunamadı.'], 404);
        }

        $menu->delete();
        return response()->json(['message' => 'Menü başarıyla silindi.']);
    }

    /**
     * ObjectId temizleyici
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
