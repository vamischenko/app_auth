<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/2fa/enable', [TwoFactorController::class, 'showEnableForm'])->name('2fa.enable');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable.post');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
});

Route::middleware('guest')->group(function () {
    Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])->name('auth.social');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.social.callback');

    Route::get('/magic-link', [MagicLinkController::class, 'showRequestForm'])->name('magic-link.request');
    Route::post('/magic-link', [MagicLinkController::class, 'sendMagicLink'])->name('magic-link.send');
});

Route::get('/magic-link/verify/{token}', [MagicLinkController::class, 'verify'])->name('magic-link.verify');

Route::get('/2fa/challenge', [TwoFactorController::class, 'showChallengeForm'])->name('2fa.challenge');
Route::post('/2fa/challenge', [TwoFactorController::class, 'verify'])->name('2fa.verify');

require __DIR__.'/auth.php';
