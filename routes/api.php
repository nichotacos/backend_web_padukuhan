<?php

use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/konten', [PostController::class, 'store']);
    Route::put('/konten/{id}', [PostController::class, 'update']);
    Route::delete('/konten/{id}', [PostController::class, 'destroy']);
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout']);
Route::get('/konten', [PostController::class, 'index']);

// Route::get('/konten', [PostController::class, 'index']);
