<?php

namespace App\Enums;

enum ParticipationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'بانتظار المراجعة',
            self::Approved => 'معتمدة',
            self::Hidden => 'مخفية',
        };
    }

    /** اسم الفئة في نظام التصميم (badge--pending ...) */
    public function tone(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
