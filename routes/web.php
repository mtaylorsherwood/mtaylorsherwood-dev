<?php

declare(strict_types=1);

use App\Http\Controllers\BookController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index'])->name('index');

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
    Route::resource('books', BookController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->middleware('auth');
});

Route::get('posts/{post}/publish', [PostController::class, 'publish'])
    ->middleware('auth')
    ->name('posts.publish');

Route::get('bookshelf', [BookController::class, 'index'])
    ->name('bookshelf');

require __DIR__.'/auth.php';
