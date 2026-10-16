<?php

namespace App\Http\Controllers;

use App\Models\ReadingPlan;
use App\Models\Book;
use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReadingPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 状態の取得・バリデーション
        $validated = $request->validate([
            'status' => [
                'nullable',
                Rule::enum(ReadingPlanStatus::class),
            ],
        ]);

        // 選択されている状態を取得
        $currentStatus = $validated['status'] ?? '';

        // ログインユーザーの読書計画を取得するクエリ
        $query = ReadingPlan::where('user_id', auth()->id())
            ->with('book');

        // 状態が指定されている場合は絞り込む
        if ($currentStatus !== '') {
            $query->where('status', $currentStatus);
        }

        // 読書計画を取得
        $readingPlans = $query
            ->orderBy('target_date', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Bladeにデータを渡す
        return view('reading-plans.index', compact(
            'readingPlans',
            'currentStatus'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReadingPlanRequest $request)
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Planned,
        ]);

        return redirect()->route('reading-plans.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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