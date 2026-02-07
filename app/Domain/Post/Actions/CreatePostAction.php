<?php

namespace App\Domain\Post\Actions;

use App\Domain\Post\Models\Post;
use App\Domain\Post\DataTransferObjects\PostDTO;

class CreatePostAction
{
    public function execute(PostDTO $dto): Post
    {
        return Post::create([
            // 'title' => $dto->title,
        ]);
    }
}
