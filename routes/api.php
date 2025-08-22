<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\GradeController;

use Illuminate\Http\Request;


Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('answers',AnswerController::class);
Route::apiResource('grade',GradeController::class);
Route::get('ping', fn() => response()->json(['pong' => true]));

Route::post('probe', function (Request $r) {
    return response()->json([
        'hit'   => true,
        'url'   => $r->fullUrl(),
        'body'  => $r->all(),
        'meth'  => $r->method(),
    ]);
});
