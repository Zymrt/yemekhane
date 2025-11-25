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
use App\Http\Controllers\UnitController; // 👈 UnitController'ı ekledik
use App\Models\Announcement; // 👈 Duyuru modelini ekledik
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --------------------------------------------------------
// 🟢 AÇIK ROTLAR (AUTH GEREKTİRMEZ - HERKES GÖREBİLİR)
// --------------------------------------------------------
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/refresh', [RefreshController::class, 'refresh']);

// 📢 DUYURULAR (Herkese Açık - Karşılama Ekranı İçin)
Route::get('/announcements', function () {
    return Announcement::orderBy('created_at', 'desc')->get();
});

// Cookie test
Route::get('/cookie-test', function (Request $request) {
    return response()->json([
        'access_token' => $request->cookie('access_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
        'refresh_token' => $request->cookie('refresh_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
    ]);
});

// --------------------------------------------------------
// 🔒 KORUMALI ROTLAR (JWT GEREKTİRİR - GİRİŞ YAPMIŞ KULLANICILAR)
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
    // 🧑‍💼 ADMİN ROTLARI (SADECE YÖNETİCİLER)
    // --------------------------------------------------------
    Route::prefix('admin')->middleware('admin')->group(function () {
        
        // 📢 DUYURU YÖNETİMİ (Ekle/Sil)
        Route::get('/announcements', [AdminController::class, 'getAnnouncements']); // Admin listesi
        Route::post('/announcements', [AdminController::class, 'createAnnouncement']);
        Route::delete('/announcements/{id}', [AdminController::class, 'deleteAnnouncement']);

        // 💬 YORUM YÖNETİMİ (Görüntüleme)
        Route::get('/reviews', [AdminController::class, 'getAllReviews']);

        // 🧾 MENÜ İŞLEMLERİ
        Route::post('/menu/add', [MenuController::class, 'addMenu']);
        Route::get('/menu/all', [MenuController::class, 'getAllMenus']);
        Route::delete('/menu/{id}', [MenuController::class, 'deleteMenu']);
        Route::put('/menu/{id}', [MenuController::class, 'updateMenu']);

        // 👥 KULLANICI YÖNETİMİ
        Route::get('/users/all', [AdminController::class, 'getAllUsers']);
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        
        Route::get('/users/{userId}/document', [AdminController::class, 'downloadDocument']);
        Route::post('/users/{userId}/approve', [AdminController::class, 'approveUser']);
        Route::delete('/users/{userId}/reject', [AdminController::class, 'rejectUser']);
        Route::post('/users/{id}/update-price', [AdminController::class, 'updateUserPrice']);

        // 📊 DASHBOARD RAPORLAR
        Route::get('/dashboard-stats', [AdminController::class, 'getDashboardStats']);
        Route::get('/unit-stats', [AdminController::class, 'getUnitStats']);
        Route::get('/finance-stats', [AdminController::class, 'getFinanceStats']);

        // 🏢 BİRİM YÖNETİMİ
        Route::get('/units', [UnitController::class, 'index']);
        Route::post('/units', [UnitController::class, 'store']);
        Route::delete('/units/{id}', [UnitController::class, 'destroy']); 
        Route::put('/units/{id}', [UnitController::class, 'update']);  

        // Admin Cookie Test
        Route::get('/cookie-test', function (\Illuminate\Http\Request $request) {
            return response()->json([
                'access_token' => $request->cookie('access_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
                'refresh_token' => $request->cookie('refresh_token') ? '✅ Cookie alındı' : '❌ Cookie yok',
            ]);
        });
    });
});