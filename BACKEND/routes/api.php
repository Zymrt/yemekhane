<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuController; 
use App\Http\Controllers\Auth\UserController; // YENİ: UserController'ı dahil ediyoruz
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// --------------------------------------------------------
// AÇIK ROTLAR (AUTH)
// --------------------------------------------------------
Route::post('/register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

// --------------------------------------------------------
// KORUMALI ROTLAR (JWT Kimlik Doğrulaması Gerektirir)
// --------------------------------------------------------
Route::middleware('jwt.auth')->group(function () {
    
    // 1. Çıkış Yapma
    Route::post('logout', [LoginController::class, 'logout']);

    // 2. KULLANICI PROFİLİ ÇEKME (UserController kullanılarak detaylı veri çekilir)
    // Frontend URL: http://127.0.0.1:8000/api/user/profile
    Route::get('/user/profile', [UserController::class, 'getProfile']); 

    // Eski 'user' örneğini isterseniz kaldırabiliriz:
    // Route::get('user', function(Request $request){ return auth()->user(); });

    // 3. MENÜ GÖRÜNTÜLEME
    // Frontend URL: http://127.0.0.1:8000/api/menu/today
    Route::get('/menu/today', [MenuController::class, 'getTodayMenu']);

    // 4. ADMIN MENÜ EKLEME (AdminMiddleware ile korumalı)
    Route::middleware('admin')->group(function () {
        // Frontend URL: http://127.0.0.1:8000/api/admin/menu/add
        Route::post('/admin/menu/add', [MenuController::class, 'addMenu']); 
    });
});