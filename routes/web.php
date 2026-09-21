<?php

// use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

//コントローラー
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;

// 認証処理：コントローラー
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Requests\EmailVerificationRequest; //FormRequest
// use Illuminate\Foundation\Auth\EmailVerificationRequest; //laravelデフォルト仕様

// 一般ユーザー：会員登録・ログイン
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

/*---------------------認証関連---------------------*/
// メール未認証のユーザーに「/email/verify」へ誘導
// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->name('verification.notice');
// })->middleware('auth')->name('verification.notice'); //laravelデフォルト仕様

// 認証メール再送
// Route::post('/email/verification-notification', function (Request $request) {
//     session()->get('unauthenticated_user')->sendEmailVerificationNotification();
//     // $request->user()->sendEmailVerificationNotification(); //laravelデフォルト仕様
//     session()->put('resent', true);
//     return back()->with('message', 'Verification link sent!');
// })->name('verification.send');
// })->middleware(['auth', 'throttle:6,1'])->name('verification.send'); //laravelデフォルト仕様

// 認証メールのリンククリック処理
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//     $request->fulfill();
//     session()->forget('unauthenticated_user');
//     return redirect('/attendance');
// })->name('verification.verify');
// })->middleware(['auth', 'signed'])->name('verification.verify'); //laravelデフォルト仕様
/*---------------------------------------------------*/

//ログイン後：
Route::middleware('auth')->group(function () {
    // 書籍登録
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store'); //パス：仕様に記載がない

    // 書籍編集
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    // Route::put('/books/{book}/update', [BookController::class, 'update'])->name('books.update'); //パス：仕様に記載がない
    // Route::delete('/books/{book}/destroy', [BookController::class, 'destroy'])->name('books.destroy'); //パス：仕様に記載がない

    // ジャンル一覧
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

    // ジャンル詳細
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

    // ジャンル登録
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    // Route::post('/genres', [GenreController::class, 'store'])->name('genres.store'); //パス：仕様に記載がない

    // ジャンル編集
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    // Route::put('/genres/{genre}/update', [GenreController::class, 'update'])->name('genres.update'); //パス：仕様に記載がない
    // Route::delete('/genres/{genre}/destroy', [GenreController::class, 'destroy'])->name('genres.destroy'); //パス：仕様に記載がない

    // レビュー編集
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // ランキング
    Route::get('/ranking', [BookController::class, 'ranking'])->name('ranking.index');
});

//ログイン前：
// 書籍一覧（トップ）
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
