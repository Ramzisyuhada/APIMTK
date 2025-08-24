<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Grade;
use App\Models\Submission; // <-- singular
use App\Models\User;

class GradeController extends Controller
{
    /**
     * GET /api/grades
     */
public function index(Request $request)
{
    $q = Grade::with(['submission', 'user'])
        ->orderByDesc('score')
        ->orderByDesc('updated_at');

    // Filter berdasarkan assessment_id
    if ($request->filled('assessment_id')) {
        $q->whereHas('submission', function ($sub) use ($request) {
            $sub->where('assessment_id', $request->assessment_id);
        });
    }

    // Filter hanya jika ada jawaban di tabel Answer
    $q->whereHas('submission.answers');

    $grades = $q->get();

    return response()->json([
        'status'  => true,
        'message' => 'OK',
        'data'    => $grades,
    ], 200);
}



    /**
     * POST /api/grades
     * Buat / update nilai untuk kombinasi (submission_id, user_identifier)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'submission_id'   => ['required', 'string', 'exists:submissions,submission_id'],
            'user_identifier' => ['required', 'string', 'exists:users,user_identifier'],
            'score'           => ['required', 'numeric', 'between:0,100'],
        ]);

        $grade = DB::transaction(function () use ($data) {
            // Pastikan relasi ada (kalau mau ‘create kalau tidak ada’, buka komentar firstOrCreate)
            // Submission::firstOrCreate(
            //     ['submission_id' => $data['submission_id']],
            //     ['user_identifier' => $data['user_identifier']]
            // );

            // Update atau buat grade untuk pasangan (submission_id, user_identifier)
            return Grade::updateOrCreate(
                [
                    'submission_id'   => $data['submission_id'],
                    'user_identifier' => $data['user_identifier'],
                ],
                [
                    'score' => round((float) $data['score'], 2),
                ]
            )->load(['submission', 'user']);
        });

        return response()->json([
            'success' => true,
            'data'    => $grade,
        ], 201);
    }

    /**
     * GET /api/grades/{grade}
     * Pastikan di model Grade ada:
     *   public function getRouteKeyName() { return 'grade_id'; }
     * supaya /api/grades/G_001 bekerja
     */
    public function show(Grade $grade)
    {
        $grade->load(['submission', 'user']);

        return response()->json([
            'success' => true,
            'data'    => $grade,
        ], 200);
    }

    /**
     * PUT/PATCH /api/grades/{grade}
     * Hanya update score jika dikirim
     */
    public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'score' => ['sometimes', 'nullable', 'numeric', 'between:0,100'],
        ]);

        if (! $request->has('score')) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada field yang diupdate.',
            ], 400);
        }

        if (array_key_exists('score', $data)) {
            $grade->score = is_null($data['score'])
                ? null
                : round((float) $data['score'], 2);
        }

        if ($grade->isDirty()) {
            $grade->save();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'grade_id'   => $grade->grade_id ?? null,
                'score'      => $grade->score,
                'updated_at' => $grade->updated_at,
            ],
        ], 200);
    }

    /**
     * DELETE /api/grades/{grade}
     */
    public function destroy(Grade $grade)
    {
        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grade dihapus.',
        ], 200);
    }
}
