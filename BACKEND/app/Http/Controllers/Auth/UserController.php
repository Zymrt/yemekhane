<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class UserController extends Controller
{
    /**
     * Cookie içindeki token'ı doğrulayıp kullanıcı profilini döndürür.
     */
    public function profile(Request $request)
    {
        try {
            // 🍪 Cookie'den token'ı al
            $token = $request->cookie('token');

            if (!$token) {
                return response()->json([
                    'message' => 'Token bulunamadı. (cookie mevcut değil)',
                    'hint' => 'Login sonrası cookie tarayıcıya yazılamamış olabilir.'
                ], 401);
            }

            // Token'ı doğrula
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'Kullanıcı bulunamadı.',
                    'hint' => 'Token geçerli ama kullanıcı sistemde yok.'
                ], 404);
            }

            return response()->json([
                'user' => $user
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token süresi dolmuş.',
                'hint' => 'Frontend refresh endpointini çağırmalı.'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token geçersiz.',
                'hint' => 'Cookie’deki token bozulmuş olabilir (EncryptCookies.php veya SameSite hatası).'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token okunamadı.',
                'hint' => 'Cookie backend’e ulaşmamış olabilir (CORS ayarlarını kontrol et).'
            ], 401);
        }
    }

    /**
     * Alias versiyon (isteğe bağlı)
     */
    public function getProfile(Request $request)
    {
        return $this->profile($request);
    }
}
