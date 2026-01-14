<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Services\OcrService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;
    
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
                    ->description('Upload cover depan dan belakang buku untuk proses OCR')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('cover_depan')
                                    ->label('Cover Depan')
                                    ->image()
                                    ->required()
                                    ->directory('book-covers/front')
                                    ->imagePreviewHeight(250)
                                    ->imageEditor()
                                    ->maxSize(5120) // Maksimum 5MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set) {
                                    }),
                
                                Forms\Components\FileUpload::make('cover_belakang')
                                    ->label('Cover Belakang')
                                    ->image()
                                    ->required()
                                    ->directory('book-covers/back')
                                    ->imagePreviewHeight(250)
                                    ->imageEditor()
                                    ->maxSize(5120) // Maksimum 5MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set) {
                                    }),
                            ]),

                            Forms\Components\Hidden::make('ocr_processed')
                                ->default(false),
                            
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('processOcr')
                                    ->label('Proses OCR & Ekstrak Metadata')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('primary')
                                    ->size('lg')
                                    ->visible(fn (Get $get) => $get('cover_depan') && $get('cover_belakang'))
                                    ->action(function (Get $get, Set $set) {
                                        $coverDepan = $get('cover_depan');
                                        $coverBelakang = $get('cover_belakang');

                                        if (!$coverDepan || !$coverBelakang) {
                                            Notification::make()
                                                ->title('Error')
                                                ->body('Harap upload kedua cover buku terlebih dahulu.')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        try {
                                            Notification::make()
                                                ->title('Memproses OCR...')
                                                ->body('Sistem sedang memproses OCR dan mengekstrak metadata buku. Mohon tunggu sebentar...')
                                                ->info()
                                                ->send();
                                            
                                            $ocrService = app(OcrService::class);

                                            // Process OCR for front cover
                                            $frontText = $ocrService->processImage($coverDepan);
                                            $set('ocr_front_text', $frontText);

                                            // Process OCR for back cover
                                            $backText = $ocrService->processImage($coverBelakang);
                                            $set('ocr_back_text', $backText);

                                            if (!$frontText && !$backText) {
                                                Notification::make()
                                                    ->title('OCR Gagal')
                                                    ->body('Tidak dapat membaca teks dari kedua cover buku. Pastikan cover buku jelas dan berkualitas baik.')
                                                    ->warning()
                                                    ->send();
                                                return;
                                            }

                                            // Extract metadata from OCR text
                                            Notification::make()
                                                ->title('Mengekstrak Metadata...')
                                                ->body('Sistem sedang menganalisis teks dan mengekstrak informasi buku...')
                                                ->info()
                                                ->send();

                                            $metadata = $ocrService->extractMetadata(
                                                $frontText ?? '', 
                                                $backText ?? ''
                                            );

                                            // Sanitize metadata
                                            $metadata = $ocrService->sanitizeMetadata($metadata);

                                            // Set extracted metadata to form fields
                                            if (!empty($metadata)) {
                                                foreach ($metadata as $key => $value) {
                                                    $set($key, $value);
                                                }

                                                $extractedCount = count($metadata);
                                                Notification::make()
                                                    ->title('Berhasil!')
                                                    ->body("Berhasil mengekstrak ($extractedCount) field metadata dari teks OCR. Silakan review dan lengkapi data yang masih kosong.")
                                                    ->success()
                                                    ->send();
                                            } else {
                                                Notification::make()
                                                    ->title('Metadata Tidak Lengkap')
                                                    ->body('OCR berhasil, tetapi metadata tidak dapat diekstrak otomatis. Silakan isi data buku secara manual.')
                                                    ->warning()
                                                    ->send();
                                            }

                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title('Error')
                                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    })
                                    ->requiresConfirmation()
                                    ->modalHeading('Proses OCR & Ekstrak Metadata?')
                                    ->modalDescription('Sistem akan membaca teks dari kedua cover dan mengekstrak metadata buku secara otomatis.')
                                    ->modalSubmitActionLabel('Ya, Proses Sekarang')
                                    ->modalIcon('heroicon-o-sparkles'),
                        ])->fullWidth(),

                    ])->collapsible(),
                
                Forms\Components\Section::make('Hasil OCR')
                    ->schema([
                        Forms\Components\Textarea::make('ocr_front_text')
                            ->label('Teks dari Cover Depan')
                            ->rows(5)
                            ->placeholder('Hasil OCR akan ditampilkan di sini...')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('ocr_back_text')
                            ->label('Teks dari Cover Belakang')
                            ->rows(5)
                            ->placeholder('Hasil OCR akan ditampilkan di sini...')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->visible(fn (Get $get) => $get('ocr_front_text') || $get('ocr_back_text'))
                    ->collapsible()
                    ->collapsed(false),

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
                            ->maxLength(255),

                        Forms\Components\TextInput::make('issn')
                            ->label('ISSN')
                            ->maxLength(255),

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
        unset($data['ocr_front_text']);
        unset($data['ocr_back_text']);
        
        return $data;
    }
}
