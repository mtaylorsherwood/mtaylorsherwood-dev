<?php

declare(strict_types=1);

use App\Http\Controllers\BookshelfController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index'])->name('index');
Route::get('/bookshelf', [BookshelfController::class, 'index'])->name('bookshelf');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::prefix('admin')->group(function () {
    Route::resource('posts', PostController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware('auth');
});

Route::get('posts/{post}/publish', [PostController::class, 'publish'])
    ->middleware('auth')
    ->name('posts.publish');

require __DIR__.'/auth.php';
