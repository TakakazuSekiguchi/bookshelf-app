<?php

namespace App\Http\Controllers;
use App\Models\Book;

use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index()
    {
        $rankedBooks = Book::withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>=', 4)
            ->orderByDesc('reviews_avg_rating')
            ->limit(10)
            ->get();

            // withAvg() → リレーション先の指定カラムの平均値を取得
            // reviewsテーブルのratingの平均値を計算し、
            // 「reviews_avg_rating」という名前で取得する
            // ※reviews_avg_ratingはDB上のカラムではなく、withAvg()によって付与される別名

            // having() → withAvg()で計算した集計結果に条件を指定
            // withAvg()で計算した平均評価が4以上の書籍に絞り込む
            // 集計した値に対する条件指定のため、whereではなくhavingを使用

        return view('ranking.index', compact('rankedBooks'));
    }
}