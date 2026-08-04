<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Completed => 'Concluída',
            self::Skipped => 'Pulada',
        };
    }
}
