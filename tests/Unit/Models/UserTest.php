<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use App\Models\ReadingPlan;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_passwordがハッシュ化されること(): void
    {
        $password = 'password123';

        $user = User::factory()->create([
            'password' => $password,
        ]);

        // 平文のパスワードと保存された値が異なること
        $this->assertNotEquals($password, $user->password);

        // 元のパスワードで照合できること
        $this->assertTrue(Hash::check($password, $user->password));
    }
    
    public function test_booksリレーションが正しく定義されていること()
    {
        $user = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $user->books()
        );
    
        $this->assertInstanceOf(
            Book::class,
            $user->books()->getRelated()
        );
    }

    public function test_favoriteBooksリレーションが正しく定義されていること()
    {
        $user = new User();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->favoriteBooks()
        );

        $this->assertInstanceOf(
            Book::class,
            $user->favoriteBooks()->getRelated()
        );
    }

    public function test_reviewsリレーションが正しく定義されていること()
    {
        $user = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $user->reviews()
        );
    
        $this->assertInstanceOf(
            Review::class,
            $user->reviews()->getRelated()
        );
    }

    public function test_likedReviewsリレーションが正しく定義されていること()
    {
        $user = new User();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->likedReviews()
        );

        $this->assertInstanceOf(
            Review::class,
            $user->likedReviews()->getRelated()
        );
    }

    public function test_readingPlansリレーションが正しく定義されていること()
    {
        $user = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $user->readingPlans()
        );
    
        $this->assertInstanceOf(
            ReadingPlan::class,
            $user->readingPlans()->getRelated()
        );
    }
}
