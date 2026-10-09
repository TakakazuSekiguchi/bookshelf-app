<?php

// use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// コントローラー
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;

// 認証処理：コントローラー
use App\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

// 会員登録・ログイン
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// ログイン後：
Route::middleware('auth')->group(function () {
    // 書籍登録
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    // 書籍編集
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}/update', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}/destroy', [BookController::class, 'destroy'])->name('books.destroy');

    // ジャンル一覧
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

    // ジャンル登録
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');

    // ジャンル詳細
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

    // ジャンル編集
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{genre}/update', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}/destroy', [GenreController::class, 'destroy'])->name('genres.destroy');

    // レビュー投稿
    Route::post('/reviews/{book}', [ReviewController::class, 'store'])->name('reviews.store');

    // レビュー編集
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}/update', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}/destroy', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // いいね
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'store'])->name('reviews.like');

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{book}', [FavoriteController::class, 'store'])->name('favorites.toggle');

    // マイ読書レポート
    Route::get('/reports', [ReadingPlanController::class, 'index'])->name('reports.index');

    // 読書計画
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
});

// ログイン前：
// 書籍一覧（トップ）
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
