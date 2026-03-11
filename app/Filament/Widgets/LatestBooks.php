<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBooks extends BaseWidget
{
    protected static ?string $heading = '📚 Buku Terbaru';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s'; // Refresh setiap 30 detik

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Book::with(['subject', 'collection', 'location'])
                    ->latest()
                    ->limit(10) // Tambah jadi 10
            )
            ->columns([
                TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->wrap()
                    ->weight('bold'), // Tebalkan judul

                TextColumn::make('pengarang')
                    ->label('Pengarang')
                    ->toggleable()
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) > 40) {
                            return $state;
                        }

                        return null;
                    }),

                TextColumn::make('penerbit')
                    ->label('Penerbit')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('tahun_terbit')
                    ->label('Tahun')
                    ->toggleable()
                    ->badge() // Ubah jadi badge
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('subject.subyek')
                    ->label('Subyek')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('collection.koleksi')
                    ->label('Koleksi')
                    ->toggleable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('location.lokasi')
                    ->label('Lokasi')
                    ->toggleable()
                    ->icon('heroicon-m-map-pin')
                    ->size('sm'),
            ])
            ->actions([
                // Tambah aksi cepat untuk lihat detail
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Book $record): string => route('filament.admin.resources.books.edit', $record)),
            ])
            ->emptyStateHeading('Belum ada buku')
            ->emptyStateDescription('Tambah buku baru untuk mulai mengelola perpustakaan')
            ->emptyStateIcon('heroicon-o-book-open');
    }
}