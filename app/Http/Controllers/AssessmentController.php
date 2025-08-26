<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
            'assessment_type' => 'required|string',
            'title'   => 'required|string',
            'file_url_soal'   => 'required|string',
        ]);
        $Assessment = Assessment::create($data);
        return response()->json(['success'=>true,'data'=>$Assessment], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
          $post = Assessment::findOrFail($id);
        return response()->json(['success'=>true,'data' => $post],200);
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
  public function update(Request $request, Assessment $assessment)
{
    $data = $request->validate([
        'file_url_soal' => 'required|string',
    ]);

    $assessment->update($data);

    return response()->json([
        'success' => true,
        'data'    => $assessment
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
