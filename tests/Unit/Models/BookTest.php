<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_dateがdate型にキャストされること()
    {
        $book = Book::factory()->create([
            'published_date' => '2026-10-30',
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $book->published_date
        );
    }

    public function test_userリレーションが正しく定義されていること()
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsTo::class,
            $book->user()
        );
    
        $this->assertInstanceOf(
            User::class,
            $book->user()->getRelated()
        );
    }

    public function test_genresリレーションが正しく定義されていること()
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->genres()
        );

        $this->assertInstanceOf(
            Genre::class,
            $book->genres()->getRelated()
        );
    }

    public function test_favoritedByUsersリレーションが正しく定義されていること()
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->favoritedByUsers()
        );

        $this->assertInstanceOf(
            User::class,
            $book->favoritedByUsers()->getRelated()
        );
    }

    public function test_reviewsリレーションが正しく定義されていること()
    {
        $book = new Book();

        $this->assertInstanceOf(
            HasMany::class,
            $book->reviews()
        );

        $this->assertInstanceOf(
            Review::class,
            $book->reviews()->getRelated()
        );
    }

    public function test_readingPlansリレーションが正しく定義されていること()
    {
        $book = new Book();

        $this->assertInstanceOf(
            HasMany::class,
            $book->readingPlans()
        );

        $this->assertInstanceOf(
            ReadingPlan::class,
            $book->readingPlans()->getRelated()
        );
    }
}
