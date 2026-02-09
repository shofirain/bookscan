<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Buku')
                    ->schema([
                        Forms\Components\Select::make('collection_id')
                            ->label('Koleksi')
                            ->relationship('collection', 'koleksi')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('location_id')
                            ->label('Lokasi')
                            ->relationship('location', 'lokasi')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('subject_id')
                            ->label('Subyek')
                            ->relationship('subject', 'subyek')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->maxLength(255)
                            ->default('tersedia'),
                    ])->columns(2),

                Forms\Components\Section::make('Upload Cover Buku')
                    ->description('Upload cover depan dan belakang untuk hasil OCR yang lebih lengkap.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('cover_depan')
                                    ->label('Cover Depan')
                                    ->image()
                                    ->directory('book-covers')
                                    ->required()
                                    ->maxSize(10240) // 10MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('ocr_triggered', false);

                                        // Reset OCR fields
                                        $this->resetOcrFields($set);

                                        Notification::make()
                                            ->title('Cover depan diupload')
                                            ->body('Upload cover belakang jika ada, lalu klik "Scan OCR"')
                                            ->info()
                                            ->send();
                                    }),

                                Forms\Components\FileUpload::make('cover_belakang')
                                    ->label('Cover Belakang')
                                    ->image()
                                    ->directory('book-covers')
                                    ->maxSize(10240)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        Notification::make()
                                            ->title('Cover belakang diupload')
                                            ->body('Klik "Scan OCR" untuk memproses kedua gambar')
                                            ->info()
                                            ->send();
                                    }),
                            ]),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('scanOcr')
                                ->label('Scan OCR Sekarang')
                                ->icon('heroicon-o-document-magnifying-glass')
                                ->color('primary')
                                ->size('lg')
                                ->visible(function (Get $get) {
                                    return $get('cover_depan') !== null;
                                })
                                ->action(function (Get $get, Set $set) {
                                    $this->scanOcr($get, $set);
                                })
                                ->extraAttributes([
                                    'class' => 'w-full justify-center',
                                ]),
                        ])->columnSpanFull()
                            ->alignCenter(),

                        Forms\Components\Placeholder::make('ocr_status')
                            ->label('Status OCR')
                            ->content(function (Get $get) {
                                if ($get('ocr_processing')) {
                                    return '🔄 Sedang memproses OCR...';
                                }
                                if ($get('ocr_triggered')) {
                                    return '✅ OCR selesai. Silakan review data di bawah.';
                                }
                                return '📁 Upload cover buku terlebih dahulu';
                            })
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('ocr_result')
                            ->label('Hasil OCR (JSON)')
                            ->rows(5)
                            ->readOnly()
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => !empty($get('ocr_result'))),
                    ]),

                Forms\Components\Section::make('Metadata Buku')
                    ->description('Data hasil OCR - silakan review dan edit jika diperlukan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('judul')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('pengarang')
                                    ->label('Pengarang')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('penerbit')
                                    ->label('Penerbit')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('tahun_terbit')
                                    ->label('Tahun Terbit')
                                    ->numeric()
                                    ->minValue(1800)
                                    ->maxValue(date('Y'))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('edisi')
                                    ->label('Edisi')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('isbn')
                                    ->label('ISBN')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('issn')
                                    ->label('ISSN')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('jumlah_halaman')
                                    ->label('Jumlah Halaman')
                                    ->numeric()
                                    ->minValue(1)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('ukuran')
                                    ->label('Ukuran')
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('sinopsis')
                            ->label('Sinopsis')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn(Get $get) => !$get('ocr_triggered')),
            ]);
    }
}
