<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::query()->get();

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        Gate::authorize('create', Post::class);

        $post = new Post();
        $post->setAttribute('title', $request->title);
        /** @phpstan-ignore  argument.type */
        $post->setAttribute('slug', Str::slug($request->title));
        $post->setAttribute('post_content', $request->post_content);
        $post->save();

        return redirect()->route('posts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        Gate::authorize('view', $post)->denyAsNotFound();

        return view('posts.show', compact('post'));
    }

    public function publish(Post $post): RedirectResponse
    {
        Gate::authorize('publish', $post);

        $post->publish();

        return redirect()->route('posts.show', $post);
    }
}
