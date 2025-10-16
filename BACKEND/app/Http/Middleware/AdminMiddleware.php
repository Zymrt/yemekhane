<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\Auth; // Artık buna gerek yok

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Token'ı kontrol eden JWT middleware'i zaten çalıştığı için,
        // direkt olarak auth() yardımcı fonksiyonunu kullanmak en güvenlisidir.
        $user = auth()->user(); 
        
        // Veya daha kompakt bir kontrol:
        // if (!auth()->check() || !auth()->user()->is_admin) {

        // 1. Kullanıcı girişi yoksa veya Admin değilse 403 (Yasak)
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Erişim Reddedildi: Sadece yöneticiler bu işlemi yapabilir.'], 403);
        }

        // 2. Admin ise devam et
        return $next($request);
    }
}