<?php

namespace App\Enums;

enum GoalPeriod: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Diária',
            self::Weekly => 'Semanal',
            self::Monthly => 'Mensal',
        };
    }
}
