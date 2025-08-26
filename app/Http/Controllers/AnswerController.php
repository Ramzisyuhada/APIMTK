<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Submissions;
use App\Models\Question;
use App\Models\Answer;

use Illuminate\Http\Request;
class AnswerController extends Controller
{

    public function index()
    {
        return response()->json(
            Answer::with(['question', 'submission'])->get()
        );

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $data = $request->validate([
            'submission_id' => 'required|string',
            'question_id'   => 'required|string',
            'answer_text'   => 'required|string',
        ]);

        return DB::transaction(function () use ($data) {
            $submission = Submissions::firstOrCreate(
                ['submission_id' => $data['submission_id']],
                ['user_id' => 1] // sesuaikan default-mu
            );

            $question = Question::firstOrCreate(
                ['question_id' => $data['question_id']],
                ['question_text' => 'Pertanyaan baru']
            );

            $answer = \App\Models\Answer::create([
                'submission_id' => $submission->submission_id,
                'question_id'   => $question->question_id,
                'answer_text'   => $data['answer_text'],
            ]);

            return response()->json(['success'=>true,'data'=>$answer->load(['question','submission'])], 201);
        });
    }


    /**
     * Display the specified resource.
     */
  public function show($id)
{
    $answer = Answer::with(['submission.user', 'question'])
        ->where('submission_id', $id)
           ->get();;

    if (!$answer) {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan',
            'data'    => null
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data'    => $answer,
    ], 200);
}


public function showBySubmission(Request $request, $submission_id)
{
    // Jika ada user_identifier + assessment_id, pakai itu untuk resolve submission sebenarnya
    if ($request->filled('user_identifier') && $request->filled('assessment_id')) {
        $resolved = Submissions::where('user_identifier', $request->user_identifier)
            ->where('assessment_id',   $request->assessment_id)
            ->value('submission_id');

        if (!empty($resolved)) {
            $submission_id = $resolved; // override S001 yang salah
        }
    }

    $answers = Answer::with(['submission.user','question'])
        ->where('submission_id', $submission_id)
        ->when($request->filled('assessment_id'), function ($q) use ($request) {
            $q->whereHas('submission', fn($sub) => $sub->where('assessment_id', $request->assessment_id));
        })
        ->when($request->filled('user_identifier'), function ($q) use ($request) {
            $q->whereHas('submission', fn($sub) => $sub->where('user_identifier', $request->user_identifier));
        })
        ->get();

    return response()->json([
        'success' => true,
        'count'   => $answers->count(),
        'data'    => $answers,
    ], 200);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
