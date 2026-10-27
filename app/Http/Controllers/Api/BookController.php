<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookDetailResource;
use App\Http\Requests\SearchBookApiRequest;
use App\Http\Requests\StoreBookApiRequest;
use App\Http\Requests\UpdateBookApiRequest;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchBookApiRequest $request)
    {
        $books = Book::query()
            ->with(['genres', 'user'])
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

        $books = $books->paginate(10)->withQueryString();

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookApiRequest $request)
    {
        $book = Book::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'author'=> $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        $book->genres()->attach($request->genres);

        $book->load('genres');

        return new BookResource($book);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['genres', 'user', 'reviews']);

        return new BookDetailResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookApiRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $data = $request->validated();

        $genres = $data['genres'];
        unset($data['genres']);

        $book->update($data);
        $book->genres()->sync($genres);

        $book->load('genres');

        return new BookDetailResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
