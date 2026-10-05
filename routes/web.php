<?php

use App\Http\Controllers\ChatSessionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'chat')->name('home');

Route::get('chat/models', [ChatSessionController::class, 'models'])->name('chat.models');
Route::post('chat/completions', [ChatSessionController::class, 'completions'])->name('chat.completions');

Route::prefix('chat/sessions')->name('chat.sessions.')->group(function () {
    Route::get('/', [ChatSessionController::class, 'index'])->name('index');
    Route::post('/', [ChatSessionController::class, 'store'])->name('store');
    Route::get('/{uuid}', [ChatSessionController::class, 'show'])->name('show');
    Route::delete('/{uuid}', [ChatSessionController::class, 'destroy'])->name('destroy');
    Route::post('/{uuid}/messages', [ChatSessionController::class, 'storeMessage'])->name('messages.store');
    Route::post('/{uuid}/send', [ChatSessionController::class, 'sendMessageAndReply'])->name('send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
