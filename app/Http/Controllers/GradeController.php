<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Submissions; // ← singular
use App\Models\User;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
                $grades = Grade::with(['submission', 'user'])
            ->orderBy('score', 'desc')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => $grades,
        ], 200);

    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data =  $request->validate([
            'submission_id' => 'required|string',
            'user_identifier' => 'required|string',
            'score' => 'required|decimal:1,2'
        ]);


        return DB::transaction(function () use ($data) {
            $submission = Submissions::firstOrCreate(
                ['submission_id' => $data['submission_id']],
                ['user_id' => 1] // sesuaikan default-mu
            );

            $grade = User::firstOrCreate(
                ['user_identifier' => $data['user_identifier']],
                ['question_text' => '1729910']
            );

        return response()->json(['success'=>true,'data'=>$grade], 201);


        });
    }

    /**
     * Display the specified resource.
     */
   public function show(Grade $grade)
{
    // pastikan di Grade ada getRouteKeyName() => 'grade_id' supaya /api/grades/G_001 bisa
    $grade->load(['submission','user']);
    return response()->json($grade, 200);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'submission_id'   => 'sometimes|string|exists:submissions,submission_id',
            'user_identifier' => 'sometimes|string|exists:users,user_identifier', // hapus rule exists ini jika users tidak punya kolom user_identifier
            'score'           => 'sometimes|decimal:1,2',
        ]);

        return DB::transaction(function () use ($data, $grade) {
            if (array_key_exists('submission_id', $data)) {
                $grade->submission_id = $data['submission_id'];
            }
            if (array_key_exists('user_identifier', $data)) {
                $grade->user_identifier = $data['user_identifier'];
            }
            if (array_key_exists('score', $data)) {
                $grade->score = $data['score'];
            }

            $grade->save();

            return response()->json([
                'success' => true,

            ], 200);
        });
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
