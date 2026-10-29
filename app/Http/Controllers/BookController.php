<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Requests\SearchBookRequest;
use App\Http\Requests\SearchISBNRequest;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    // 書籍一覧
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

    // 書籍登録画面の表示
    public function create()
    {
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    // ISBN検索
    public function searchIsbn(SearchISBNRequest $request)
    {
        // 1. バリデーション済みのISBNを取得
        $isbn = $request->validated()['isbn'];

        try {
            // 2. Google Books APIにリクエストを送信
            $response = Http::connectTimeout(3)
                ->timeout(5)
                ->get('https://www.googleapis.com/books/v1/volumes', [
                    'q' => 'isbn:' . $isbn,
                    'maxResults' => 1,
                    'key' => config('services.google_books.api_key'),
                ]);

            // 3. APIからエラーレスポンスが返された場合
            if ($response->failed()) {
                Log::warning('Google Books API returned an error.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'message' => '書籍情報の取得に失敗しました。',
                ], 502);
            }

            // 4. JSONレスポンスを取得
            $data = $response->json();

            // 5. 書籍が見つからない場合
            if (empty($data['items'][0]['volumeInfo'])) {
                return response()->json([
                    'message' => '書籍情報が見つかりませんでした。',
                ], 404);
            }

            // 6. 書籍情報を取り出す
            $volumeInfo = $data['items'][0]['volumeInfo'];

            // 7. JavaScriptが使用する形式に整形して返す
            return response()->json([
                'title' => $volumeInfo['title'] ?? '',
                'author' => implode(', ', $volumeInfo['authors'] ?? []),
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
                'published_date' => $volumeInfo['publishedDate'] ?? '',
            ]);

        } catch (ConnectionException $e) {
            // 8. APIとの接続に失敗した場合
            Log::warning('Google Books API connection failed.');

            return response()->json([
                'message' => 'Google Books APIに接続できませんでした。',
            ], 502);

        } catch (\Throwable $e) {
            // 9. その他の予期しないエラー
            Log::error('ISBN search failed.', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => '書籍情報の検索中にエラーが発生しました。',
            ], 500);
        }
    }

    // 書籍登録
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

    // 書籍詳細
    public function show(Book $book)
    {
        $book = Book::with('genres')
            ->with('user')
            ->with('reviews')
            ->findOrFail($book->id);

        return view('books.show', compact('book'));
    }

    // 書籍編集画面の表示
    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    // 書籍編集
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

    // 書籍削除
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
