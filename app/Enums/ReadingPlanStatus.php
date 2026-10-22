<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Overdue = 'overdue';

    /**
     * 画面に表示する状態名
     */
    public function label(): string
    {
        return match ($this) {
            self::InProgress => '進行中',
            self::Completed => '読了',
            self::Overdue => '期日超過',
        };
    }

    /**
     * 状態ごとのCSSクラス
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::InProgress => 'bg-gray-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Overdue => 'bg-green-100 text-red-800',
        };
    }
}
