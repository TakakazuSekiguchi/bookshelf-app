<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $users[0]->favoriteBooks()->syncWithoutDetaching([
            $books[0]->id,
            $books[2]->id,
            $books[4]->id,
        ]);

        $users[1]->favoriteBooks()->syncWithoutDetaching([
            $books[1]->id,
            $books[3]->id,
            $books[5]->id,
        ]);

        $users[2]->favoriteBooks()->syncWithoutDetaching([
            $books[0]->id,
            $books[4]->id,
            $books[6]->id,
        ]);

        $users[3]->favoriteBooks()->syncWithoutDetaching([
            $books[2]->id,
            $books[5]->id,
            $books[7]->id,
        ]);

        $users[4]->favoriteBooks()->syncWithoutDetaching([
            $books[1]->id,
            $books[6]->id,
            $books[8]->id,
        ]);
    }
}
