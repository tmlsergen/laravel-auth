<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckPasswordTokenMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'guest',
], function () {
    // home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // login
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // register
    Route::get('/register', [AuthController::class, 'registerPage'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // password reset
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])
        ->name('password.email');

    // password reset
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])
        ->name('password.reset')
        ->middleware(CheckPasswordTokenMiddleware::class);
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->name('password.update');
});

Route::group([
    'middleware' => ['auth', 'auth.session', 'password.expire', '2fa'],
], function () {
    // password
    Route::get('/password/expired', [PasswordController::class, 'expired'])
        ->name('password.expired');
    Route::post('/password/expired', [PasswordController::class, 'updateExpiredPassword'])
        ->name('password.expired_update');

    // user
    Route::get('/users/me', [UserController::class, 'me'])
        ->name('user.me');
    Route::put('/users/me', [UserController::class, 'update'])
        ->name('user.me.update');
    Route::put('/users/me/password', [UserController::class, 'updatePassword'])
        ->name('user.me.password');
    Route::get('/users/me/sessions', [UserController::class, 'sessions'])
        ->name('user.me.sessions');
    Route::delete('/users/me/sessions/{session_id}', [UserController::class, 'logoutFromDevice'])
        ->name('user.me.sessions.logout');

    // 2fa
    Route::post('/2fa/enable', [UserController::class, 'enable2FA'])
        ->name('2fa.enable');
    Route::get('/2fa/verify', [UserController::class, 'verify'])
        ->name('2fa.verify');
    Route::post('/2fa/verify', [UserController::class, 'verify2FA'])
        ->name('2fa.verify.post');
    Route::get('/2fa/backup-codes', [UserController::class, 'getBackupCodes'])
        ->name('2fa.backup-codes');
    Route::post('/2fa/regenerate-backup-codes', [UserController::class, 'regenerateBackupCodes'])
        ->name('2fa.regenerate-backup-codes');
    Route::get('/2fa/backup', [UserController::class, 'backup'])
        ->name('2fa.backup');
    Route::post('/2fa/backup', [UserController::class, 'verifyBackup'])
        ->name('2fa.backup.post');

    // logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // oauth
    Route::get('/oauth/authorize', [OAuthController::class, 'index'])
        ->name('oauth.index');
    Route::post('/oauth/authorize', [OAuthController::class, 'authorize'])
        ->name('oauth.authorize');
});

// oauth
Route::post('/oauth/token', [OAuthController::class, 'token'])
    ->name('oauth.token');
Route::post('/oauth/token/revoke', [OAuthController::class, 'revoke'])
    ->name('oauth.refresh')
    ->middleware('api.auth');
