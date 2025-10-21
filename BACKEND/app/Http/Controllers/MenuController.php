<?php

namespace App\Http\Controllers;

use App\Models\Menu;
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
'items.*.calorie' => 'nullable|numeric|min:0',
        ]);

        Menu::updateOrCreate(
            ['date' => Carbon::parse($validated['date'])->startOfDay()],
            ['items' => $validated['items']]
        );

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

        $menu->update([
            'date' => Carbon::parse($validated['date'])->startOfDay(),
            'items' => $validated['items'],
        ]);

        return response()->json(['message' => 'Menü başarıyla güncellendi.']);
    }

    /**
     * Bugünün menüsünü getir
     */
    public function getTodayMenu()
    {
        $menu = Menu::where('date', Carbon::today())->first();

        if (!$menu) {
            return response()->json(['message' => 'Bugün için menü bulunamadı.'], 404);
        }

        return response()->json($menu);
    }

    /**
     * ObjectId temizleme fonksiyonu
     * Örn: "ObjectId('66fdccba54b8cd2d4b3b2733')" → "66fdccba54b8cd2d4b3b2733"
     */
    private function cleanObjectId($id)
    {
        // Eğer id zaten 24 karakterlik düz string ise direkt dön
        if (preg_match('/^[a-f\d]{24}$/i', $id)) {
            return new ObjectId($id);
        }

        // Eğer id ObjectId('xxxxx') şeklindeyse regex ile temizle
        if (preg_match("/ObjectId\('([a-f\d]{24})'\)/i", $id, $matches)) {
            return new ObjectId($matches[1]);
        }

        throw new \Exception("Geçersiz ObjectId formatı: $id");
    }
}
