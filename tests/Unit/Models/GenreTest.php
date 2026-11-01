<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_booksリレーションが正しく定義されていること()
    {
        $genre = new Genre();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $genre->books()
        );
    
        $this->assertInstanceOf(
            Book::class,
            $genre->books()->getRelated()
        );
    }
}
