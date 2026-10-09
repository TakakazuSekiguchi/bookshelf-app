<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Requests\SearchBookRequest;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchBookRequest $request)
    {
        $genres = Genre::all();

        $books = Book::query()
            ->with('genres')
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->keyword;

                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('genre_id'), function ($query) use ($request) {
                $query->whereHas('genres', function ($query) use ($request) {
                    $query->where('genres.id', $request->genre_id);
                });
            });

        // 並び順
        switch ($request->sort) {
            case 'oldest':
                $books->orderBy('published_date', 'asc');
                break;

            case 'rating':
                $books->withAvg('reviews', 'rating')
                    ->orderByDesc('reviews_avg_rating');
                break;

            case 'title':
                $books->orderBy('title', 'asc');
                break;

            case 'newest':
            default:
                $books->orderBy('published_date', 'desc');
                break;
        }

        $books = $books->paginate(10)->withQueryString();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $book = Book::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'author'=> $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
            // 'genre_id',
        ]);

        $book->genres()->attach($request->genres);
        return redirect()->route('books.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book = Book::with('genres')
            ->with('user')
            ->with('reviews')
            ->findOrFail($book->id);

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'author'=> $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
