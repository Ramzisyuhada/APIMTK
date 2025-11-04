<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
       public function index()
    {
        $users = User::all();
        return response()->json($users);
    }



    public function getRataRataGayaBelajar()
{
    // Hitung total user
    $totalUsers = User::count();

    if ($totalUsers === 0) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada data user.',
            'data'    => []
        ], 404);
    }

    // Hitung jumlah tiap gaya belajar
    $visualCount      = User::where('gayabelajar', 'Visual')->count();
    $auditoryCount    = User::where('gayabelajar', 'Auditory')->count();
    $kinestheticCount = User::where('gayabelajar', 'Kinesthetic')->count();

    // Hitung rata-rata (persentase)
    $visualAvg      = ($visualCount / $totalUsers) * 100;
    $auditoryAvg    = ($auditoryCount / $totalUsers) * 100;
    $kinestheticAvg = ($kinestheticCount / $totalUsers) * 100;

    return response()->json([
        'success' => true,
        'message' => 'Rata-rata gaya belajar berhasil dihitung',
        'total_user' => $totalUsers,
        'data' => [
            'Visual'      => [
                'jumlah' => $visualCount,
                'persentase' => round($visualAvg, 2)
            ],
            'Auditory'    => [
                'jumlah' => $auditoryCount,
                'persentase' => round($auditoryAvg, 2)
            ],
            'Kinesthetic' => [
                'jumlah' => $kinestheticCount,
                'persentase' => round($kinestheticAvg, 2)
            ],
        ]
    ], 200);
}

       public function updateGayaBelajar(Request $request)
{
    $data = $request->validate([
        'user_identifier' => 'required|string',
        'gayabelajar'     => 'required|string|in:Visual,Auditory,Kinesthetic' // sesuaikan enum kamu
    ]);

    // Cari user
    $user = User::where('user_identifier', $data['user_identifier'])->firstOrFail();

    // Update kolom
    $user->gayabelajar = $data['gayabelajar'];
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Gaya belajar diperbarui',
        'data'    => [
            'user_identifier' => $user->user_identifier,
            'name'            => $user->name,
            'role'            => $user->role,
            'gayabelajar'     => $user->gayabelajar,
        ],
    ], 200);
}

    public function updateGayaBelajarById(Request $request, string $user_identifier)
    {
        $data = $request->validate([
            'gayabelajar' => 'required|string',
        ]);

        try {
            $user = User::where('user_identifier', $user_identifier)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $user->gayabelajar = $data['gayabelajar'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Gaya belajar diperbarui',
            'data'    => [
                'user_identifier' => $user->user_identifier,
                'name'            => $user->name,
                'role'            => $user->role,
                'gayabelajar'     => $user->gayabelajar,
            ],
        ], 200);
    }


}
