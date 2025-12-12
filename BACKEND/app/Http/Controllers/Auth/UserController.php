<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Aktif oturum bulunamadı.'
            ], 401);
        }

        // Kullanıcıdan göstermek istediğimiz alanlar
        $userData = $user->only([
            '_id',
            'name',
            'surname',
            'phone',
            'email',
            'unit',
            'balance',
            'role',
            'meal_price',
            'created_at',
        ]);

        // 🔥 Modeldeki accessor'dan gelen has_purchased bilgisini EKLİYORUZ
        $userData['has_purchased'] = $user->has_purchased;

        return response()->json([
            'user' => $userData,
        ], 200);
    }

    public function getProfile(Request $request)
    {
        return $this->profile($request);
    }
}
