<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBooks extends BaseWidget
{
    protected static ?string $heading = 'Buku Terbaru';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                Book::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('pengarang')
                    ->label('Pengarang')
                    ->toggleable(),

                TextColumn::make('tahun_terbit')
                    ->label('Tahun Terbit')
                    ->toggleable(),

            ]);
    }
}
