<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

class QuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Question::findOrFail($id);
        return response()->json(['success'=>true,'data' => $post],200);    }


    // public function showByID(string $id)
    // {
    //       $post = Question::findOrFail($id);
    //     return response()->json(['success'=>true,'data' => $post],200);
    // }

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
    public function update(Request $request, Question $question)
    {
        // kalau PUT: biasanya required semua. Kalau PATCH: partial.
        $data = $request->validate([
            'question_text'   => 'sometimes|required|string',
        ]);

        $question->update($data);

        return response()->json([
            'success' => true,
            'data'    => $question
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
