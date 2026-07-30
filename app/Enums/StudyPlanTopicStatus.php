<?php

namespace App\Enums;

enum StudyPlanTopicStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::InProgress => 'Em andamento',
            self::Done => 'Concluído',
        };
    }
}
