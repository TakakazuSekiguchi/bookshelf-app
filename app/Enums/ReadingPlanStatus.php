<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Planned = 'planned';
    case Completed = 'completed';

    /**
     * 画面に表示する状態名
     */
    public function label(): string
    {
        return match ($this) {
            self::Planned => '進行中',
            self::Completed => '読了',
        };
    }

    /**
     * 状態ごとのCSSクラス
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Planned => 'bg-gray-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
        };
    }
}
