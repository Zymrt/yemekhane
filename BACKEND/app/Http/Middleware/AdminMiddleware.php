<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Token'ı kontrol eden 'auth:api' middleware'i zaten çalışmış olmalı.
        $user = Auth::guard('api')->user(); 

        // 1. Kullanıcı girişi yoksa veya Admin değilse 403 (Yasak)
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Erişim Reddedildi: Sadece yöneticiler bu işlemi yapabilir.'], 403);
        }

        // 2. Admin ise devam et
        return $next($request);
    }
}