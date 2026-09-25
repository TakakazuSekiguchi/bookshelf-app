<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\Book;

class ReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $book = $review->book_id;

        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }
}
