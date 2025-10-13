<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::middleware('jwt.auth')->post('logout', [LoginController::class, 'logout']);

// Korumalı rota örneği
Route::middleware('jwt.auth')->get('user', function(Request $request){
    return auth()->user();
});