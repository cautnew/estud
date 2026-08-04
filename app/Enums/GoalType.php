<?php

namespace App\Enums;

enum GoalType: string
{
    case Hours = 'hours';
    case Subjects = 'subjects';
    case Topics = 'topics';
    case Reviews = 'reviews';
    case Questions = 'questions';

    public function label(): string
    {
        return match ($this) {
            self::Hours => 'Horas de estudo',
            self::Subjects => 'Disciplinas concluídas',
            self::Topics => 'Tópicos concluídos',
            self::Reviews => 'Revisões concluídas',
            self::Questions => 'Questões resolvidas',
        };
    }
}
