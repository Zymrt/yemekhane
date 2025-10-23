<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\RefreshController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Bu yapı cookie tabanlı JWT doğrulama için optimize edilmiştir.
| Frontend'de her istek "credentials: 'include'" ile gönderilmelidir.
|
*/

// --------------------------------------------------------
// 🟢 AÇIK ROTLAR (AUTH GEREKTİRMEZ)
// --------------------------------------------------------
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// ✅ TOKEN YENİLEME (Cookie tabanlı)
Route::post('/refresh', [RefreshController::class, 'refresh']);

// 🔍 Cookie test (isteğe bağlı, dev/test için)
Route::get('/cookie-test', function (Request $request) {
    return response()->json([
        'token_cookie' => $request->cookie('token') ? '✅ Cookie alındı' : '❌ Cookie yok',
        'raw' => $request->cookie('token')
    ]);
});

// --------------------------------------------------------
// 🔒 KORUMALI ROTLAR (JWT GEREKTİRİR)
// --------------------------------------------------------
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::get('/menu/today', [MenuController::class, 'getTodayMenu']);

    // --------------------------------------------------------
    // 🧑‍💼 ADMİN ROTLARI
    // --------------------------------------------------------
    Route::prefix('admin')->middleware('admin')->group(function () {
        // 🧾 MENÜ İŞLEMLERİ
        Route::post('/menu/add', [MenuController::class, 'addMenu']);
        Route::get('/menu/all', [MenuController::class, 'getAllMenus']);
        Route::delete('/menu/{id}', [MenuController::class, 'deleteMenu']);
        Route::put('/menu/{id}', [MenuController::class, 'updateMenu']);

        // 👥 KULLANICI YÖNETİMİ
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::get('/users/{userId}/document', [AdminController::class, 'downloadDocument']);
        Route::post('/users/{userId}/approve', [AdminController::class, 'approveUser']);
        Route::delete('/users/{userId}/reject', [AdminController::class, 'rejectUser']);

        // 📊 DASHBOARD RAPORLAR
        Route::get('/dashboard', [AdminController::class, 'getDashboardStats']);

        Route::get('/cookie-test', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'token_cookie' => $request->cookie('token') ? '✅ Cookie alındı' : '❌ Cookie yok',
        'raw' => $request->cookie('token'),
    ]);
});
    });
});
