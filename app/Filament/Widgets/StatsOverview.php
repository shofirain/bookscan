<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Collection;
use App\Models\Location;
use App\Models\Subject;
use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Data untuk chart 7 hari terakhir
        $booksPerDay = Book::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();

        return [
            Stat::make('Total Buku', Book::count())
                ->label('Total Buku')
                ->description('Jumlah semua buku di database')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-book-open')
                ->color('primary')
                ->chart($booksPerDay),

            Stat::make('Total Subyek', Subject::count())
                ->label('Total Subyek')
                ->description('Klasifikasi subyek buku')
                ->descriptionIcon('heroicon-m-tag')
                ->icon('heroicon-o-bookmark')
                ->color('info'),

            Stat::make('Total Koleksi', Collection::count())
                ->label('Total Koleksi')
                ->description('Jenis koleksi yang tersedia')
                ->descriptionIcon('heroicon-m-folder')
                ->icon('heroicon-o-archive-box')
                ->color('success'),

            Stat::make('Total Lokasi', Location::count())
                ->label('Total Lokasi')
                ->description('Rak/lokasi penyimpanan')
                ->descriptionIcon('heroicon-m-map')
                ->icon('heroicon-o-map-pin')
                ->color('warning'),
            
        ];
    }
}