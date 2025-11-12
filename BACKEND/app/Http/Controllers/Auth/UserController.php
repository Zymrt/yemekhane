<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Aktif oturumu doğrulayıp kullanıcı profilini döndürür.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Aktif oturum bulunamadı.'
            ], 401);
        }

        return response()->json([
            'user' => $user->only('_id','name','surname','phone','unit','balance','role')
        ], 200);
    }

    /**
     * Alias versiyon (isteğe bağlı)
     */
    public function getProfile(Request $request)
    {
        return $this->profile($request);
    }
}
