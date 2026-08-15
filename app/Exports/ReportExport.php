<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Generic single-sheet export shared by every report type: the filter
 * summary and headline metrics are written as rows above the data table,
 * mirroring the PDF layout (resources/views/reports/report.blade.php) so
 * both exports present the same information.
 */
class ReportExport implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param  array{title: string, filters_description: string, summary: array<int, array{label: string, value: string}>, columns: array<int, string>, rows: array<int, array<int, string>>}  $report
     */
    public function __construct(private readonly array $report) {}

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        $rows = [
            [$this->report['filters_description']],
            [],
        ];

        foreach ($this->report['summary'] as $item) {
            $rows[] = [$item['label'], $item['value']];
        }

        $rows[] = [];
        $rows[] = $this->report['columns'];

        foreach ($this->report['rows'] as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function title(): string
    {
        return $this->report['title'];
    }
}
