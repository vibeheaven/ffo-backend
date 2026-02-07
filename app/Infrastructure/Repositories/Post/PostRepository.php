<?php

namespace App\Infrastructure\Repositories\Post;

use App\Domain\Post\Repositories\PostRepositoryInterface;
use App\Domain\Post\Models\Post;

class PostRepository implements PostRepositoryInterface
{
    public function all()
    {
        return Post::all();
    }

    public function find(int $id): ?Post
    {
        return Post::find($id);
    }
}
