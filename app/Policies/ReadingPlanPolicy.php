<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReadingPlanPolicy
{
    // 読書計画の閲覧
    public function view(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    // 読書計画の編集
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    // 読書計画の削除
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }
}
