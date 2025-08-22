<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Submissions;
use App\Models\Question;

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
    public function show(string $id)
    {


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
