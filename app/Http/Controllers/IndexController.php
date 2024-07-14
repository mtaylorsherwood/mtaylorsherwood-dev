<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::query()->whereNotNull('published_at')->orderByDesc('published_at')->get();

        return view('index', compact('posts'));
    }
}
