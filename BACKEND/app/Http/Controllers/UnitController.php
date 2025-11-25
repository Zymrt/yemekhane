<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // Tüm birimleri getir
    public function index()
    {
        return response()->json(Unit::all(), 200);
    }

    // Yeni birim ekle
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:units,name',
            'price' => 'required|numeric|min:0'
        ]);

        $unit = Unit::create([
            'name' => $request->name,
            'price' => $request->price
        ]);

        return response()->json($unit, 201);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0'
        ]);

        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Birim bulunamadı'], 404);
        }

        // 1. Eski birim adını sakla 
        $oldName = $unit->name;

        // 2. Birimi güncelle
        $unit->name = $request->name;
        $unit->price = $request->price;
        $unit->save();

        User::where('unit', $oldName)->update([
            'unit' => $request->name,      // İsim değiştiyse kullanıcıda da değişsin
            'meal_price' => $request->price // Fiyat değiştiyse kullanıcıda da değişsin
        ]);

        return response()->json([
            'message' => 'Birim ve bağlı kullanıcıların fiyatları güncellendi', 
            'unit' => $unit
        ], 200);
    }

    // Birim sil
    public function destroy($id)
    {
        Unit::destroy($id);
        return response()->json(['message' => 'Birim silindi'], 200);
    }
}