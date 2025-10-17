<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MenuController extends Controller
{
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

        return response()->json(['message' => 'Menü başarıyla kaydedildi/güncellendi.']);
    }
// Tüm menüleri getir (admin için)
public function getAllMenus()
{
    $menus = Menu::orderBy('date', 'desc')->get();
    return response()->json($menus);
}

// Menü sil
public function deleteMenu($id)
{
    $menu = Menu::find($id);

    if (!$menu) {
        return response()->json(['message' => 'Menü bulunamadı.'], 404);
    }

    $menu->delete();

    return response()->json(['message' => 'Menü başarıyla silindi.']);
}
    public function updateMenu(Request $request, $id)
{
    $validated = $request->validate([
        'date' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.name' => 'required|string|max:255',
        'items.*.description' => 'nullable|string|max:255',
    ]);

    $menu = Menu::find($id);

    if (!$menu) {
        return response()->json(['message' => 'Menü bulunamadı.'], 404);
    }

    $menu->update([
        'date' => \Carbon\Carbon::parse($validated['date'])->startOfDay(),
        'items' => $validated['items'],
    ]);

    return response()->json(['message' => 'Menü başarıyla güncellendi.']);
    }
    // Bu da kullanıcıların menüyü görmesi için
    public function getTodayMenu()
    {
        $menu = Menu::where('date', Carbon::today())->first();

        if (!$menu) {
            return response()->json(['message' => 'Bugün için menü bulunamadı.'], 404);
        }

        return response()->json($menu);
    }
}