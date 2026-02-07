<?php

namespace App\Domain\Post\Repositories;

use App\Domain\Post\Models\Post;

interface PostRepositoryInterface
{
    public function all();
    public function find(int $id): ?Post;
}
