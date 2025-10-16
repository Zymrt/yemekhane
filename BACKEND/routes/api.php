<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuController; 
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\AdminController; 
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

    // 2. KULLANICI PROFİLİ ÇEKME
    Route::get('/user/profile', [UserController::class, 'getProfile']); 

    // 3. MENÜ GÖRÜNTÜLEME
    Route::get('/menu/today', [MenuController::class, 'getTodayMenu']);

    // 4. ADMIN İŞLEMLERİ (AdminMiddleware ile korumalı)
    Route::middleware('admin')->group(function () {
        
        // MENÜ EKLEME (Mevcut)
        Route::post('/admin/menu/add', [MenuController::class, 'addMenu']); 
        
        // YENİ: ONAY BEKLEYEN KULLANICILARI LİSTELEME
        Route::get('/admin/pending-users', [AdminController::class, 'getPendingUsers']);
        
        // YENİ: BELGE İNDİRME
        Route::get('/admin/download-document/{userId}', [AdminController::class, 'downloadDocument']);
        
        // YENİ EKLENEN ROTLAR: ONAYLAMA ve REDDETME
        // Kullanıcıyı Onaylama
        Route::post('/admin/approve-user/{userId}', [AdminController::class, 'approveUser']);
        
        // Kullanıcıyı Reddetme/Silme
        Route::delete('/admin/reject-user/{userId}', [AdminController::class, 'rejectUser']);
    });
});