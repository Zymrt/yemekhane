<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\TokenService;

class LoginController extends Controller
{
    public function __construct(protected TokenService $tokenService)
    {
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Telefon veya şifre hatalı.'],
            ]);
        }

        if ($user->status !== 'approved') {
            throw ValidationException::withMessages([
                'phone' => ['Hesabınız henüz onaylanmamış.'],
            ]);
        }

        $sessionData = $this->tokenService->createSession($user, $request);

        $accessCookie = $this->tokenService->makeAccessTokenCookie($sessionData['access_token']);
        $refreshCookie = $this->tokenService->makeRefreshTokenCookie($sessionData['refresh_token']);

        return response()->json([
            'message' => 'Giriş başarılı.',
            'user' => $user->only('_id','name','surname','phone','unit','balance','role'),
        ])->withCookie($accessCookie)->withCookie($refreshCookie);
    }

    public function logout(Request $request)
    {
        $accessToken = $request->cookie('access_token') ?? $request->bearerToken();
        $refreshToken = $request->cookie('refresh_token');

        $this->tokenService->revokeByTokens($accessToken, $refreshToken);

        $forgetCookies = $this->tokenService->forgetCookies();

        $response = response()->json([
            'message' => 'Çıkış yapıldı.'
        ]);

        foreach ($forgetCookies as $cookie) {
            $response->withCookie($cookie);
        }

        return $response;
    }
}
