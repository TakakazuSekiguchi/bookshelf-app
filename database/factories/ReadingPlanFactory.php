<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\User;
use App\Models\ReadingPlan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingPlan>
 */
class ReadingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => fake()->date(),
            'completed_at' => null,
        ];
    }
}