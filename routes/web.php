<?php

declare(strict_types=1);

use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Site\BookController as SiteBookController;
use App\Http\Controllers\Site\FeedController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PostController as SitePostController;
use App\Http\Controllers\Site\ProjectController as SiteProjectController;
use App\Http\Controllers\Site\RecapController as SiteRecapController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::inertia('about', 'site/About')->name('about');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('feed.xml', FeedController::class)->name('feed');
Route::get('robots.txt', RobotsController::class)->name('robots');

Route::get('projects', [SiteProjectController::class, 'index'])->name('projects');
Route::get('projects/{slug}', [SiteProjectController::class, 'show'])->name('projects.show');

Route::get('blog', [SitePostController::class, 'index'])->name('blog.index');
Route::get('blog/{slug}', [SitePostController::class, 'show'])->name('blog.show');

Route::get('books', [SiteBookController::class, 'index'])->name('books');

Route::get('recaps', [SiteRecapController::class, 'index'])->name('recaps.index');
Route::get('recaps/{slug}', [SiteRecapController::class, 'show'])->name('recaps.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('dashboard/books', BookController::class)
        ->except('show')
        ->names('dashboard.books');

    Route::patch('dashboard/books/{book}/start', [BookController::class, 'start'])
        ->name('dashboard.books.start');

    Route::patch('dashboard/books/{book}/finish', [BookController::class, 'finish'])
        ->name('dashboard.books.finish');

    Route::resource('dashboard/posts', PostController::class)
        ->except('show')
        ->names('dashboard.posts');

    Route::post('dashboard/posts/{post}/publish', [PostController::class, 'publish'])
        ->name('dashboard.posts.publish');

    Route::post('dashboard/images', [ImageController::class, 'store'])
        ->name('dashboard.images.store');

    Route::resource('dashboard/projects', ProjectController::class)
        ->except('show')
        ->names('dashboard.projects');
});

require __DIR__.'/settings.php';
