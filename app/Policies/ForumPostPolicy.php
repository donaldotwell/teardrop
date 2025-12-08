<?php

namespace App\Policies;

use App\Models\ForumPost;
use App\Models\User;

class ForumPostPolicy
{
    public function update(User $user, ForumPost $post)
    {
        return $user->id === $post->user_id || $user->hasAnyRole(['admin', 'moderator']);
    }

    public function delete(User $user, ForumPost $post)
    {
        return $user->id === $post->user_id || $user->hasAnyRole(['admin', 'moderator']);
    }
}
