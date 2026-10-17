<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Enums\ReadingPlanStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReadingReportController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. 基本統計
        $totalReviews = Review::where('user_id', $userId)->count();

        $booksRead = ReadingPlan::where('user_id', $userId)
            ->where('status', ReadingPlanStatus::Completed->value)
            ->distinct()
            ->count('book_id');

        $averageRating = Review::where('user_id', $userId)
            ->avg('rating') ?? 0;

        // 2. 評価分布（評価1〜5、それぞれのレビュー件数）
        $ratingCounts = Review::where('user_id', $userId)
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        // Bladeでは index + 1 が評価になるため、キーは0〜4にする
        $ratingDistribution = collect(range(1, 5))
            ->mapWithKeys(function ($rating) use ($ratingCounts) {
                return [
                    $rating - 1 => (int) ($ratingCounts[$rating] ?? 0),
                ];
            });

        // 3. 高評価書籍 TOP5
        $topRatedBooks = Review::where('user_id', $userId)
            ->where('rating', '>=', 4)
            ->with('book:id,title,author')
            ->orderByDesc('rating')
            ->limit(5)
            ->get()
            ->filter(fn ($review) => $review->book !== null)
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            })
            ->values()
            ->all();

        // 4. ジャンル別評価 TOP5
        $genreRatings = DB::table('genres')
            ->join('book_genre', 'genres.id', '=', 'book_genre.genre_id')
            ->join('books', 'book_genre.book_id', '=', 'books.id')
            ->join('reviews', 'books.id', '=', 'reviews.book_id')
            ->where('reviews.user_id', $userId)
            ->select(
                'genres.id',
                'genres.name',
                DB::raw('COUNT(reviews.id) as count'),
                DB::raw('AVG(reviews.rating) as average_rating')
            )
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'count' => (int) $genre->count,
                    'average_rating' => (float) $genre->average_rating,
                ];
            })
            ->all();

        // Bladeに渡すデータ
        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => (float) $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}