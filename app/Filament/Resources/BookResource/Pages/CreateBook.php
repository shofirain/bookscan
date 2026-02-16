<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Services\OcrService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                    ->description('Upload cover depan dan belakang untuk hasil OCR yang lebih lengkap.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\FileUpload::make('cover_depan')
                                    ->label('Cover Depan')
                                    ->image()
                                    ->directory('book-covers/front')
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
                                    ->directory('book-covers/back')
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
                                
                                    // Forms\Components\FileUpload::make('copyright')
                                    // ->label('Copyright')
                                    // ->image()
                                    // ->directory('book-covers/copyright')
                                    // ->maxSize(10240)
                                    // ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    // ->live()
                                    // ->afterStateUpdated(function ($state, Set $set) {
                                    //     Notification::make()
                                    //         ->title('Copyright diupload')
                                    //         ->body('Klik "Scan OCR" untuk memproses ketiga gambar')
                                    //         ->info()
                                    //         ->send();
                                    // }),
                                    
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

    /**
     * Main OCR scanning function - FIXED FOR FILAMENT ARRAY FORMAT
     */
    protected function scanOcr(Get $get, Set $set): void
    {
        try {
            // Set processing state
            $set('ocr_processing', true);
            $set('ocr_triggered', false);
            
            set_time_limit(900);
            ini_set('max_execution_time', 900);

            $frontCover = $get('cover_depan');
            $backCover = $get('cover_belakang');

            // DEBUG: Log state
            Log::info('OCR Scan - File State', [
                'front_cover_type' => gettype($frontCover),
                'back_cover_type' => gettype($backCover),
                'front_is_array' => is_array($frontCover),
                'back_is_array' => is_array($backCover),
                'front_content' => is_array($frontCover) ? array_keys($frontCover) : 'not array',
                'back_content' => is_array($backCover) ? array_keys($backCover) : 'not array',
            ]);

            // Validate front cover
            if (!$frontCover) {
                Notification::make()
                    ->title('Cover depan belum diupload')
                    ->body('Silakan upload cover depan terlebih dahulu')
                    ->danger()
                    ->send();
                
                $set('ocr_processing', false);
                return;
            }

            // Get file paths - FIXED: Handle Filament array format
            $frontPath = $this->extractFilePath($frontCover, 'front');
            $backPath = $backCover ? $this->extractFilePath($backCover, 'back') : null;

            // DEBUG: Log paths
            Log::info('OCR Scan - Extracted File Paths', [
                'front_path' => $frontPath,
                'back_path' => $backPath,
                'front_exists' => $frontPath && file_exists($frontPath),
                'back_exists' => $backPath && file_exists($backPath),
            ]);

            if (!$frontPath || !file_exists($frontPath)) {
                throw new \Exception('File cover depan tidak valid. Path: ' . ($frontPath ?? 'null'));
            }

            // Show processing notification
            $hasBackCover = ($backPath && file_exists($backPath));
            
            Notification::make()
                ->title('Memproses OCR...')
                ->body($hasBackCover ? 
                    'Mengekstrak metadata dari 2 gambar (depan & belakang)' : 
                    'Mengekstrak metadata dari cover depan')
                ->info()
                ->send();

            Log::info('OCR Scan Started', [
                'has_front' => !empty($frontPath),
                'has_back' => $hasBackCover,
                'front_size' => filesize($frontPath),
                'back_size' => $hasBackCover ? filesize($backPath) : null,
            ]);

            // Call OCR Service
            $ocrService = app(OcrService::class);
            
            $startTime = microtime(true);
            
            if ($hasBackCover) {
                Log::info('Processing both covers');
                $result = $ocrService->extractMetadataMulti($frontPath, $backPath);
            } else {
                Log::info('Processing front cover only');
                $result = $ocrService->extractMetadata($frontPath);
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            Log::info("OCR completed in {$duration} seconds");

            // Set OCR results
            $this->setOcrResults($result, $set);
            
            // Update states
            $set('ocr_processing', false);
            $set('ocr_triggered', true);

            Notification::make()
                ->title('OCR Berhasil!')
                ->body("Metadata berhasil diekstrak ({$duration} detik)")
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Log::error('OCR Scan Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'front_cover' => $frontCover ?? null,
                'back_cover' => $backCover ?? null,
            ]);
            
            $set('ocr_processing', false);
            
            Notification::make()
                ->title('OCR Gagal')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    /**
     * Extract file path from Filament FileUpload array format
     * Format: {uuid: TemporaryUploadedFile}
     */
    protected function extractFilePath($fileData, string $type = 'front'): ?string
    {
        if (!$fileData) {
            Log::warning("File {$type} data is null or empty");
            return null;
        }

        Log::info("Extracting file path for {$type}", [
            'data_type' => gettype($fileData),
            'is_array' => is_array($fileData),
            'array_keys' => is_array($fileData) ? array_keys($fileData) : 'not array',
        ]);

        // CASE 1: Already a string path (when saved)
        if (is_string($fileData)) {
            // Check if it's an absolute path
            if (file_exists($fileData)) {
                Log::info("String path found for {$type}: {$fileData}");
                return $fileData;
            }
            
            // Check if it's a storage path
            if (Storage::disk('public')->exists($fileData)) {
                $path = Storage::disk('public')->path($fileData);
                Log::info("Storage path found for {$type}: {$path}");
                return $path;
            }
            
            return null;
        }

        // CASE 2: Filament array format {uuid: TemporaryUploadedFile}
        if (is_array($fileData)) {
            if (empty($fileData)) {
                Log::warning("Empty array for {$type}");
                return null;
            }
            
            // Get the first element (should be TemporaryUploadedFile)
            $firstKey = array_key_first($fileData);
            $firstValue = $fileData[$firstKey];
            
            Log::info("Array first element for {$type}", [
                'key' => $firstKey,
                'value_type' => gettype($firstValue),
                'is_temporary' => $firstValue instanceof TemporaryUploadedFile,
            ]);
            
            // Check if it's a TemporaryUploadedFile
            if ($firstValue instanceof TemporaryUploadedFile) {
                $path = $firstValue->getRealPath();
                Log::info("TemporaryUploadedFile path for {$type}: {$path}");
                return $path;
            }
            
            // Check if it's another array containing TemporaryUploadedFile
            if (is_array($firstValue)) {
                foreach ($firstValue as $subKey => $subValue) {
                    if ($subValue instanceof TemporaryUploadedFile) {
                        $path = $subValue->getRealPath();
                        Log::info("Nested TemporaryUploadedFile path for {$type}: {$path}");
                        return $path;
                    }
                }
            }
            
            Log::warning("No TemporaryUploadedFile found in array for {$type}");
            return null;
        }

        // CASE 3: Direct TemporaryUploadedFile
        if ($fileData instanceof TemporaryUploadedFile) {
            $path = $fileData->getRealPath();
            Log::info("Direct TemporaryUploadedFile path for {$type}: {$path}");
            return $path;
        }

        Log::warning("Unknown file data type for {$type}", [
            'actual_type' => gettype($fileData),
        ]);
        
        return null;
    }

    /**
     * Alternative: Simple method to handle Filament format
     */
    protected function getFileFromFilamentFormat($fileData)
    {
        if (!$fileData) {
            return null;
        }

        // If already TemporaryUploadedFile
        if ($fileData instanceof TemporaryUploadedFile) {
            return $fileData;
        }

        // If string (saved path)
        if (is_string($fileData)) {
            return $fileData;
        }

        // If array (Filament format)
        if (is_array($fileData)) {
            // Flatten array and find first TemporaryUploadedFile
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator($fileData)
            );
            
            foreach ($iterator as $value) {
                if ($value instanceof TemporaryUploadedFile) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Set OCR results to form fields
     */
    protected function setOcrResults(array $result, Set $set): void
    {
        try {
            $data = $result['data'] ?? $result;

            // Reset semua field OCR terlebih dahulu
            $this->resetOcrFields($set);
            
            // Set field baru dari hasil OCR
            $set('judul', $data['title'] ?? null);
            $set('pengarang', $data['author'] ?? null);
            $set('penerbit', $data['publisher'] ?? null);
            
            // Handle year variations
            $tahun = $data['year'] ?? $data['publication_year'] ?? null;
            if (is_numeric($tahun)) {
                $set('tahun_terbit', (int) $tahun);
            } else {
                $set('tahun_terbit', null);
            }
            
            // Set other fields
            $set('isbn', $data['isbn'] ?? null);
            $set('issn', $data['issn'] ?? null);
            $set('edisi', $data['edition'] ?? null);
            $set('sinopsis', $data['synopsis'] ?? $data['description'] ?? null);
            
            // Handle page numbers
            $pages = $data['page'] ?? $data['pages'] ?? null;
            if (is_numeric($pages)) {
                $set('jumlah_halaman', (int) $pages);
            } else {
                $set('jumlah_halaman', null);
            }
            
            $set('ukuran', $data['size'] ?? null);
            
            // Save raw OCR result (formatted JSON)
            $formattedResult = [
                'success' => $result['success'] ?? true,
                'data' => $data,
                'timestamp' => now()->toDateTimeString()
            ];
            
            $set('ocr_result', json_encode($formattedResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info('OCR Results Applied', [
                'judul' => $data['title'] ?? null,
                'pengarang' => $data['author'] ?? null,
                'has_synopsis' => !empty($data['synopsis']),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to set OCR results: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reset OCR-related fields
     */
    protected function resetOcrFields(Set $set): void
    {
        $ocrFields = [
            'judul', 'pengarang', 'penerbit', 'tahun_terbit',
            'edisi', 'sinopsis', 'isbn', 'issn', 
            'jumlah_halaman', 'ukuran', 'ocr_result'
        ];
        
        foreach ($ocrFields as $field) {
            $set($field, null);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        // Clean OCR result if present
        if (isset($data['ocr_result']) && empty($data['ocr_result'])) {
            unset($data['ocr_result']);
        }
        
        // Hapus field temporary
        unset($data['ocr_processing'], $data['ocr_triggered'], $data['ocr_status']);
        
        return $data;
    }
    
    /**
     * Mount method untuk inisialisasi state
     */
    public function mount(): void
    {
        parent::mount();
        
        $this->form->fill([
            'ocr_processing' => false,
            'ocr_triggered' => false,
            'status' => 'tersedia'
        ]);
    }
}