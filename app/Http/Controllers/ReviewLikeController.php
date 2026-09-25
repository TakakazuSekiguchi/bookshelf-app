<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use App\Models\User;

class ReviewLikeController extends Controller
{
    public function store(Review $review){
        $user = auth()->user();

        // 自分のレビューにはいいねできない
        if ($review->user_id === $user->id) {
            return back();
        }

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {
            $user->likedReviews()->detach($review->id);
        } else {
            $user->likedReviews()->attach($review->id);
        }

        return back();
    }
}
