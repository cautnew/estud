<?php

namespace App\Enums;

enum StudySessionStatus: string
{
    case InProgress = 'in_progress';
    case Paused = 'paused';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'Em andamento',
            self::Paused => 'Pausada',
            self::Completed => 'Concluída',
        };
    }
}
