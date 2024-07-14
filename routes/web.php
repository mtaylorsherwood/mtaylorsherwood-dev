<?php

declare(strict_types=1);

use App\Http\Controllers\PostController;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::view('/', 'index', [
    'posts' => Post::query()->whereNotNull('published_at')->get()
])->name('index');

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
