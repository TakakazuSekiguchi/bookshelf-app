<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Book;
use App\Enums\ReadingPlanStatus;

class ReadingPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        // 'due_date',
        'target_date',
    ];

    // protected $casts = [
    //     'due_date' => 'date',
    // ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'status' => ReadingPlanStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
