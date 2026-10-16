<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Planned = 'planned';
    case Reading = 'reading';
    case Completed = 'completed';

    /**
     * 画面に表示する状態名
     */
    public function label(): string
    {
        return match ($this) {
            self::Planned => '読書予定',
            self::Reading => '読書中',
            self::Completed => '読了',
        };
    }

    /**
     * 状態ごとのCSSクラス
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Planned => 'bg-gray-100 text-gray-800',
            self::Reading => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
        };
    }
}
