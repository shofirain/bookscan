<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class BookPerMonthChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Buku Per Bulan';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $year = now()->year;

        // Ambil jumlah buku per bulan untuk tahun berjalan
        $data = Book::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        // Pastikan semua 12 bulan tampil, meski datanya 0
        $counts = collect(range(1, 12))->map(function ($month) use ($data) {
            return $data[$month] ?? 0;
        });

        $labels = collect(range(1, 12))->map(function ($month) {
            return Carbon::create()->month($month)->translatedFormat('M');
        });

        return [
            'datasets' => [
                [
                    'label' => "Jumlah Buku {$year}",
                    'data' => $counts->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getFilters(): ?array
    {
        $years = Book::selectRaw('DISTINCT YEAR(created_at) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun', 'tahun')
            ->toArray();

        return $years ?: [now()->year => now()->year];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
