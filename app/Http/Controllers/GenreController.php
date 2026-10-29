<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreGenreRequest;
use App\Models\Book;
use App\Models\Genre;

class GenreController extends Controller
{
    // ジャンル一覧
    public function index()
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    // ジャンル登録画面の表示
    public function create()
    {
        return view('genres.create');
    }

    // ジャンル登録
    public function store(StoreGenreRequest $request)
    {
        Genre::create([
            'name' => $request->name,
        ]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを登録しました。');
    }

    // ジャンル詳細
    public function show(Genre $genre)
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    // ジャンル編集画面の表示
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    // ジャンル編集
    public function update(StoreGenreRequest $request, Genre $genre)
    {
        $genre->update([
            'name' => $request->name,
        ]);

        return redirect()->route('genres.index', $genre)
            ->with('success', 'ジャンルを更新しました。');
    }

    // ジャンル削除
    public function destroy(Genre $genre)
    {
        if ($genre->books()->exists()) {
            return redirect()
                ->route('genres.index')
                ->with('error', '書籍が登録されているジャンルは削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
