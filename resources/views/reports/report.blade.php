<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table.summary { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.summary td { padding: 6px 10px; border: 1px solid #ddd; }
        table.summary td.label { font-weight: bold; background: #f5f5f5; width: 40%; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 11px; }
        table.data th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] }}</h1>
    <p class="meta">{{ $report['filters_description'] }} &middot; Gerado em {{ now()->format('d/m/Y H:i') }}</p>

    <table class="summary">
        @foreach ($report['summary'] as $item)
            <tr>
                <td class="label">{{ $item['label'] }}</td>
                <td>{{ $item['value'] }}</td>
            </tr>
        @endforeach
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach ($report['columns'] as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($report['columns']) }}">Nenhum dado encontrado para os filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
