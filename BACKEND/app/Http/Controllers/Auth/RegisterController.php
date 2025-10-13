<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'unit' => 'required|string',
            'proof_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $path = $request->file('proof_document')->store('proofs', 'public');

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'unit' => $request->unit,
            'proof_document' => $path,
            'status' => 'pending',
            'balance' => 0,
        ]);

        return response()->json([
            'message' => 'Kayıt başarıyla oluşturuldu. Onay bekleniyor.',
            'user_id' => $user->_id
        ], 201);
    }
}