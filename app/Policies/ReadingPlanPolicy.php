<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Enums\ReadingPlanStatus;
use Illuminate\Auth\Access\Response;

class ReadingPlanPolicy
{
    // 読書計画の編集
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status !== ReadingPlanStatus::Completed;
    }

    // 読書計画の削除
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }
}
