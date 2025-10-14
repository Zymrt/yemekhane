<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Normal kullanıcılar ve Adminler için Günlük Menü bilgisini döndürür.
      */ 
    public function getTodayMenu(Request $request)
    {
        // Gerçek bir uygulamada bu veriler veritabanından çekilir.
        $todayMenu = [
            'date' => now()->format('Y-m-d'),
            'dayName' => now()->translatedFormat('l'),
            'items' => [
                ['name' => 'Kremalı Mantar Çorbası', 'description' => 'Mantar ve krema ile hazırlanmış.'],
                ['name' => 'İskender Kebap', 'description' => 'Özel tereyağlı sosuyla.'],
                ['name' => 'Pirinç Pilavı', 'description' => 'Tereyağlı, tane tane.'],
                ['name' => 'Sütlaç', 'description' => 'Fırında üzeri kızarmış.'],
            ],
        ];

        return response()->json($todayMenu, 200);
    }
    
    /**
     * YENİ METOT: Sadece Adminlerin menü kaydetmesini sağlar.
     * Bu metoda erişim, rotada 'admin' middleware'i ile korunacaktır.
     */
    public function addMenu(Request $request)
    {
        // 1. Doğrulama (Frontend'den gelecek verinin yapısını belirtir)
        $request->validate([
            'menu_date' => 'required|date_format:Y-m-d|unique:menus,menu_date', // Aynı gün için sadece 1 menü
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
        ]);

        // 2. Veritabanına kaydetme mantığı (Burada Menu Modelinizi kullanacaksınız)
        // Örn: Menu::create($request->all());

        return response()->json([
            'message' => "{$request->menu_date} tarihi için menü başarıyla kaydedildi.", 
            'data' => $request->all()
        ], 201);
    }
}