<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Services\GeminiService;
use App\Services\OcrService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    public ?array $geminiData = null;

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
                    ->description('Upload cover depan buku untuk proses OCR')
                    ->schema([

                        Forms\Components\FileUpload::make('cover_depan')
                            ->label('Cover Depan')
                            ->image()
                            ->directory('book-covers/front')
                            ->imagePreviewHeight(250)
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->visibility('public')
                            ->required()
                            ->live()
                            ->reactive()
                            ->columnSpanFull(),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('scanOcr')
                                ->label('Scan OCR')
                                ->icon('heroicon-o-camera')
                                ->color('primary')
                                ->action(function (Get $get, Set $set) {

                                    $cover = $get('cover_depan');

                                    if (is_array($cover)) {
                                        $cover = $cover[0] ?? null;
                                    }

                                    if (! $cover instanceof TemporaryUploadedFile) {
                                        Notification::make()
                                            ->title('Cover belum diupload')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $path = $cover->getRealPath();

                                    if (! file_exists($path)) {
                                        Notification::make()
                                            ->title('File cover tidak valid')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        $ocrService = app(\App\Services\OcrService::class);
                                        $result = $ocrService->extractMetadata($path);

                                        $set('ocr_result', json_encode($result, JSON_PRETTY_PRINT));

                                        $set('judul', $result['title'] ?? null);
                                        $set('pengarang', $result['author'] ?? null);
                                        $set('tahun_terbit', $result['year'] ?? null);

                                        Notification::make()
                                            ->title('OCR berhasil')
                                            ->success()
                                            ->send();
                                    } catch (\Throwable $e) {
                                        Notification::make()
                                            ->title('OCR gagal')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                }),
                        ])->columnSpanFull(),

                        Forms\Components\Textarea::make('ocr_result')
                            ->label('Hasil OCR (JSON)')
                            ->rows(8)
                            ->readOnly()
                            ->columnSpanFull(),
                    ]),


                Forms\Components\Section::make('Metadata Buku dari AI')
                    ->description('Informasi buku yang diekstrak dari OCR. Silakan review dan edit jika diperlukan.')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('pengarang')
                            ->label('Pengarang')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('penerbit')
                            ->label('Penerbit')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('tahun_terbit')
                            ->label('Tahun Terbit')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y')),

                        Forms\Components\TextInput::make('edisi')
                            ->label('Edisi')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('sinopsis')
                            ->label('Sinopsis')
                            ->rows(5)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('isbn')
                            ->label('ISBN')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('issn')
                            ->label('ISSN')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('jumlah_halaman')
                            ->label('Jumlah Halaman')
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\TextInput::make('ukuran')
                            ->label('Ukuran')
                            ->maxLength(255),
                    ])
                    ->visible()
                    ->collapsible(),

            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
