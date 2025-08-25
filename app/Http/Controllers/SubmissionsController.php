<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submissions;

class SubmissionsController extends Controller
{

   public function findByUserAndAssessment(Request $request)
{
    // Ambil input (GET atau POST) aman:
    $userId = $request->input('user_identifier');
    $assId  = $request->input('assessment_id');

    if (!$userId || !$assId) {
        return response()->json([
            'success' => false,
            'message' => 'Parameter user_identifier dan assessment_id wajib diisi',
            'debug'   => [
                'all' => $request->all(),
                'query' => $request->query()
            ]
        ], 400);
    }

    // Cari data
    $submission = \App\Models\Submissions::where('user_identifier', $userId)
        ->where('assessment_id', $assId)
        ->first();

    if (!$submission) {
        return response()->json([
            'success' => false,
            'message' => 'Submission tidak ditemukan',
            'debug'   => [
                'user_identifier' => $userId,
                'assessment_id'   => $assId
            ]
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data'    => $submission
    ], 200);
}



     public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'user_identifier' => 'required|string',
            'assessment_id'   => 'required|string'
        ]);

        // Simpan data ke tabel submissions
        $submission = Submissions::create([
            'user_identifier' => $data['user_identifier'],
            'assessment_id'   => $data['assessment_id']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submission berhasil dibuat',
            'data'    => $submission
        ], 201);
    }


    public function show($id)
{
    $submission = Submissions::find($id);

    if (!$submission) {
        return response()->json([
            'success' => false,
            'message' => 'Submission tidak ditemukan'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $submission
    ], 200);
}

}
