<?php

namespace App\Enums;

enum ReportType: string
{
    case Productivity = 'productivity';
    case Evolution = 'evolution';
    case Reviews = 'reviews';
    case Performance = 'performance';
    case Goals = 'goals';

    public function label(): string
    {
        return match ($this) {
            self::Productivity => 'Produtividade',
            self::Evolution => 'Evolução',
            self::Reviews => 'Revisões',
            self::Performance => 'Desempenho',
            self::Goals => 'Metas',
        };
    }
}
