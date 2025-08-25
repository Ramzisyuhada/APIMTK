<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\PresignController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubmissionsController;

use Illuminate\Http\Request;


Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('answers',AnswerController::class);
Route::apiResource('grades', GradeController::class); // <-- plural & tanpa slash
Route::apiResource('submissions', SubmissionsController::class); // <-- plural & tanpa slash
Route::get('/submissions/find', [SubmissionsController::class, 'findByUserAndAssessment']);
Route::post('/submissions/find', [SubmissionsController::class, 'findByUserAndAssessment']);

Route::get('ping', fn() => response()->json(['pong' => true]));
Route::post('/presign/upload', [PresignController::class, 'upload']);
Route::post('/presign/download', [PresignController::class, 'download']);
Route::put('/users/gayabelajar/update', [UserController::class, 'updateGayaBelajar']);
Route::put('/users/{user_identifier}/gayabelajar', [UserController::class, 'updateGayaBelajarById']);

Route::post('probe', function (Request $r) {
    return response()->json([
        'hit'   => true,
        'url'   => $r->fullUrl(),
        'body'  => $r->all(),
        'meth'  => $r->method(),
    ]);
});
