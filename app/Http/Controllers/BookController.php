<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreBookRequest;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::simplepaginate(10);
        return view('books.index', compact('books'));
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
