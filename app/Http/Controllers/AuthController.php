<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
     public function login(Request $request)
    {

        $request->validate([
            'user_identifier' => 'required',
            'password'        => 'required',
        ]);

        $user = User::where('user_identifier', $request->user_identifier)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'User atau password salah'
            ], 401);
        }

        // return data + role
        return response()->json([
            'message' => 'Login berhasil',
            'user' => [
                'user_identifier' => $user->user_identifier,
                'name'  => $user->name,
                'role'  => $user->role,
            ]
        ]);
    }

    public function dashboardGuru()
    {
        return response()->json([
            'message' => 'Selamat datang Guru!'
        ]);
    }

    // contoh endpoint hanya untuk siswa
    public function dashboardSiswa()
    {
        return response()->json([
            'message' => 'Selamat datang Siswa!'
        ]);
    }

}
