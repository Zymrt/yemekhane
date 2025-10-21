<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

// --------------------------------------------------------
// AÇIK ROTLAR (AUTH GEREKTİRMEZ)
// --------------------------------------------------------
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// ✅ TOKEN YENİLEME (jwt.auth OLMAYACAK!)
// Token süresi dolmadan frontend'ten istek atıldığında yeniler
Route::post('/refresh', function (Request $request) {
    try {
        $newToken = JWTAuth::parseToken()->refresh();
        return response()->json(['token' => $newToken]);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['error' => 'Geçersiz token.'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['error' => 'Token süresi dolmuş.'], 401);
    } catch (Exception $e) {
        return response()->json(['error' => 'Token yenilenemedi.'], 401);
    }
});

// --------------------------------------------------------
// KORUMALI ROTLAR (JWT GEREKTİRİR)
// --------------------------------------------------------
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::get('/menu/today', [MenuController::class, 'getTodayMenu']);

    // --------------------------------------------------------
    // ADMİN ROTLARI
    // --------------------------------------------------------
    Route::prefix('admin')->middleware('admin')->group(function () {
        // MENÜ İŞLEMLERİ
        Route::post('/menu/add', [MenuController::class, 'addMenu']);
        Route::get('/menu/all', [MenuController::class, 'getAllMenus']);
        Route::delete('/menu/{id}', [MenuController::class, 'deleteMenu']);
        Route::put('/menu/{id}', [MenuController::class, 'updateMenu']);

        // KULLANICI YÖNETİMİ
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::get('/users/{userId}/document', [AdminController::class, 'downloadDocument']);
        Route::post('/users/{userId}/approve', [AdminController::class, 'approveUser']);
        Route::delete('/users/{userId}/reject', [AdminController::class, 'rejectUser']);
    });
});

// --------------------------------------------------------
// Laravel'in kendi user route'u (isteğe bağlı)
// --------------------------------------------------------
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
