<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{

    // レビューの編集
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    // レビューの削除
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    // レビューに対するいいね
    public function like(User $user, Review $review)
    {
        return $user->id !== $review->user_id;
    }
}