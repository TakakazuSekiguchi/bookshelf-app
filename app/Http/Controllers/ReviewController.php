<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\Book;

class ReviewController extends Controller
{
    // レビューの投稿
    public function store(ReviewRequest $request, Book $book)
    {
        $review = Review::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', [$book, $review])
            ->with('success', 'レビューを投稿しました。');
    }

    // レビュー編集画面の表示
    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    // レビューの編集
    public function update(ReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $book = $review->book_id;

        $review->update([
            'user_id' => auth()->id(),
            'book_id' => $book,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを更新しました。');
    }

    // レビューの削除
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $book = $review->book_id;

        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }
}
