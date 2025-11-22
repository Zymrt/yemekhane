<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\RefreshController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransactionController; 
use App\Http\Controllers\PaymentController; 
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

// 🔍 Cookie test
Route::get('/cookie-test', function (Request $request) {
    return response()->json([
        'access_token' => $request->cookie('access_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
        'refresh_token' => $request->cookie('refresh_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
    ]);
});

// --------------------------------------------------------
// 🔒 KORUMALI ROTLAR (JWT GEREKTİRİR)
// --------------------------------------------------------
Route::middleware(['token.auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    
    // MENÜ
    Route::get('/menu/today', [MenuController::class, 'getTodayMenu']);

    // YORUM SİSTEMİ
    Route::get('/reviews/today', [ReviewController::class, 'today']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/my-reviews', [ReviewController::class, 'myReviews']);
    
    // SATIN ALMA
    Route::post('/order/purchase', [OrderController::class, 'purchaseToday']);
    
    // HESAP HAREKETLERİ
    Route::get('/transactions', [TransactionController::class, 'index']);
    
    // ÖDEME SİSTEMİ
    Route::post('/payment/start', [PaymentController::class, 'startPayment']);

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
        // 👇 EKSİK OLAN ROTA BUYDU (Rapor sayfası için gerekli)
        Route::get('/users', [AdminController::class, 'getAllUsers']);

        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::get('/users/{userId}/document', [AdminController::class, 'downloadDocument']);
        Route::post('/users/{userId}/approve', [AdminController::class, 'approveUser']);
        Route::delete('/users/{userId}/reject', [AdminController::class, 'rejectUser']);

        // 📊 DASHBOARD RAPORLAR
        Route::get('/dashboard', [AdminController::class, 'getDashboardStats']);
        Route::get('/stats/units', [AdminController::class, 'getUnitStats']);

        // Admin Cookie Test
        Route::get('/cookie-test', function (\Illuminate\Http\Request $request) {
            return response()->json([
                'access_token' => $request->cookie('access_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
                'refresh_token' => $request->cookie('refresh_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
            ]);
        });
    });
});