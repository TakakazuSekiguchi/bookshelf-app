<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Book;
use App\Models\Review;
use App\Models\ReadingPlan;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function favoriteBooks()
    {
        return $this->belongsToMany(Book::class, 'book_favorite');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function likeReviews()
    {
        return $this->belongsToMany(Review::class, 'review_like');
    }

    public function readingPlans()
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
