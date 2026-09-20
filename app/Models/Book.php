<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReadingPlan;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn_13',
        'published_date',
        'description',
        'image_url',
        'genre_id',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Genre::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'book_favorite');
    }

    public function reviews()
    {
        return $this->hasmany(Review::class);
    }
    
    public function readingPlans()
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
