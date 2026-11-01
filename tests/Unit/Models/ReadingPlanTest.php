<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\User;
use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_statusがEnum型にキャストされること(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
        ]);

        $this->assertInstanceOf(
            ReadingPlanStatus::class,
            $readingPlan->status
        );

        $this->assertEquals(
            ReadingPlanStatus::Completed,
            $readingPlan->status
        );
    }

    public function test_target_dateがdate型にキャストされること()
    {
        $readingplan = ReadingPlan::factory()->create([
            'target_date' => '2026-10-30',
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $readingplan->target_date
        );
    }

    public function test_completed_atがdatetime型にキャストされること(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'completed_at' => '2026-11-01 12:30:00',
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $readingPlan->completed_at
        );
    }

    public function test_userリレーションが正しく定義されていること()
    {
        $readingplan = new ReadingPlan();

        $this->assertInstanceOf(
            BelongsTo::class,
            $readingplan->user()
        );
    
        $this->assertInstanceOf(
            User::class,
            $readingplan->user()->getRelated()
        );
    }

    public function test_bookリレーションが正しく定義されていること()
    {
        $readingplan = new ReadingPlan();

        $this->assertInstanceOf(
            BelongsTo::class,
            $readingplan->book()
        );
    
        $this->assertInstanceOf(
            Book::class,
            $readingplan->book()->getRelated()
        );
    }
}
