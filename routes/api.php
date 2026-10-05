<?php

use App\Http\Controllers\ChatSessionController;
use Illuminate\Support\Facades\Route;

// Tokenportal AI models & completions endpoints
Route::get('/models', [ChatSessionController::class, 'models'])->name('api.models');
Route::post('/chat/completions', [ChatSessionController::class, 'completions'])->name('api.chat.completions');

// v1 prefix to mirror OpenAI & Tokenportal format
Route::prefix('v1')->group(function () {
    Route::get('/models', [ChatSessionController::class, 'models'])->name('api.v1.models');
    Route::post('/chat/completions', [ChatSessionController::class, 'completions'])->name('api.v1.chat.completions');
});
