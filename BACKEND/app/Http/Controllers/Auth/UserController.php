<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Aktif oturum bulunamadı.'
            ], 401);
        }

        return response()->json([
            'user' => $user->only([
                '_id',
                'name',
                'surname',
                'phone',
                'email', // <--- BURAYA DA EKLEDİM
                'unit',
                'balance',
                'role',
                'meal_price',
                'created_at'
            ])
        ], 200);
    }

    public function getProfile(Request $request)
    {
        return $this->profile($request);
    }
}