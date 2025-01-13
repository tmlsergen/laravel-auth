<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'ok']);
})->name('healthcheck');

Route::group(['middleware' => 'api.auth'], function () {
    Route::get('/users/me', [UserController::class, 'me'])->name('api.user.me');
    Route::put('/users/me', [UserController::class, 'update'])->name('api.user.me.update');

});
