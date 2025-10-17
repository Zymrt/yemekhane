<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Eğer kullanıcı giriş yapmışsa VE rolü 'admin' ise, isteğin devam etmesine izin ver.
        if (auth()->user() && auth()->user()->role == 'admin') {
            return $next($request);
        }

        // Değilse, "Bu alana girmeye yetkiniz yok" hatası döndür.
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}