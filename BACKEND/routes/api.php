<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------
// AÇIK ROTLAR (AUTH GEREKTİRMEZ)
// --------------------------------------------------------
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// --------------------------------------------------------
// KORUMALI ROTLAR (JWT GEREKTİRİR)
// --------------------------------------------------------
Route::middleware('jwt.auth')->group(function () {
    
    // Çıkış Yapma
    Route::post('/logout', [LoginController::class, 'logout']);

    // Kullanıcı Profil Bilgileri
    Route::get('/user/profile', [UserController::class, 'getProfile']);

    // O Günün Menüsünü Görüntüleme
    Route::get('/menu/today', [MenuController::class, 'getTodayMenu']);

    // --------------------------------------------------------
    // ADMİN İŞLEMLERİ (AdminMiddleware ve 'admin' Ön Eki ile Korumalı)
    // --------------------------------------------------------
    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {
        
        // MENÜ İŞLEMLERİ
        Route::post('/menu/add', [MenuController::class, 'addMenu']);
        Route::get('/menu/all', [MenuController::class, 'getAllMenus']); // 👈 yeni
        Route::delete('/menu/{id}', [MenuController::class, 'deleteMenu']); // 👈 yeni
        Route::put('/menu/{id}', [MenuController::class, 'updateMenu']);

        // KULLANICI YÖNETİMİ
        // Onay bekleyen kullanıcıları listeleme
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        
        // Kullanıcı belgesini indirme
        Route::get('/users/{userId}/document', [AdminController::class, 'downloadDocument']);
        
        // Kullanıcıyı onaylama
        Route::post('/users/{userId}/approve', [AdminController::class, 'approveUser']);
        
        // Kullanıcıyı reddetme/silme
        Route::delete('/users/{userId}/reject', [AdminController::class, 'rejectUser']);
    });
});