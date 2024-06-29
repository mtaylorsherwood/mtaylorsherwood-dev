<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->getAttribute('email') === User::EMAIL || $post->getAttribute('published_at') !== null;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->getAttribute('email') === User::EMAIL;
    }

    /**
     * Determine whether the user can update the published_at parameter on the model.
     */
    public function publish(User $user, Post $post): bool
    {
        return $user->getAttribute('email') === User::EMAIL && $post->getAttribute('published_at') === null;
    }
}
