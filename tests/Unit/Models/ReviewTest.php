<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_userリレーションが正しく定義されていること()
    {
        $review = new Review();

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->user()
        );
    
        $this->assertInstanceOf(
            User::class,
            $review->user()->getRelated()
        );
    }

    public function test_bookリレーションが正しく定義されていること()
    {
        $review = new Review();

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->book()
        );
    
        $this->assertInstanceOf(
            Book::class,
            $review->book()->getRelated()
        );
    }

    public function test_likedByUsersリレーションが正しく定義されていること()
    {
        $review = new Review();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $review->likedByUsers()
        );

        $this->assertInstanceOf(
            User::class,
            $review->likedByUsers()->getRelated()
        );
    }
}
