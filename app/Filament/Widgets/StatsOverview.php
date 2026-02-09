<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Collection;
use App\Models\Location;
use App\Models\Subject;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Buku', Book::count())
                ->label('Total Buku')
                ->icon('heroicon-o-book-open')
                ->color('primary'),

            Stat::make('Total Subyek', Subject::count())
                ->label('Total Subyek')
                ->icon('heroicon-o-bookmark')
                ->color('secondary'),

            Stat::make('Total Koleksi', Collection::count())
                ->label('Total Koleksi')
                ->icon('heroicon-o-archive-box')
                ->color('success'),

            Stat::make('Total Lokasi', Location::count())
                ->label('Total Lokasi')
                ->icon('heroicon-o-map-pin')
                ->color('warning'),
        ];
    }

    
}
