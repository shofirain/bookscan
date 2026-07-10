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
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    // Tombol Delete di header halaman edit
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

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
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\FileUpload::make('cover_depan')
                                    ->label('Cover Depan')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('200')
                                    ->directory('book-covers/front')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->required()
                                    ->maxSize(10240)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('ocr_triggered', false);
                                        $this->resetOcrFields($set);

                                        Notification::make()
                                            ->title('Cover depan diupdate')
                                            ->body('Klik Scan OCR untuk memperbarui metadata.')
                                            ->info()
                                            ->send();
                                    }),

                                Forms\Components\FileUpload::make('cover_belakang')
                                    ->label('Cover Belakang')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('200')
                                    ->directory('book-covers/back')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->maxSize(10240)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        Notification::make()
                                            ->title('Cover belakang diupdate')
                                            ->body('Upload halaman copyright jika ada.')
                                            ->info()
                                            ->send();
                                    }),

                                Forms\Components\FileUpload::make('copyright_path')
                                    ->label('Halaman Copyright')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('200')
                                    ->directory('book-covers/copyright')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->maxSize(10240)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        Notification::make()
                                            ->title('Halaman Copyright diupdate')
                                            ->body('Klik "Scan OCR" untuk memperbarui metadata.')
                                            ->info()
                                            ->send();
                                    }),
                            ]),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('scanOcr')
                                ->label('Scan OCR Ulang')
                                ->icon('heroicon-o-document-magnifying-glass')
                                ->color('warning') // Kuning/orange untuk membedakan dari Create (biru)
                                ->size('lg')
                                ->visible(fn(Get $get) => filled($get('cover_depan')))
                                ->action(fn(Get $get, Set $set) => $this->scanOcr($get, $set))
                                ->extraAttributes(['class' => 'w-full justify-center']),
                        ])->columnSpanFull()->alignCenter(),

                        Forms\Components\Placeholder::make('ocr_status')
                            ->label('Status OCR')
                            ->content(function (Get $get) {
                                if ($get('ocr_processing')) return '🔄 Sedang memproses OCR...';
                                if ($get('ocr_triggered')) return '✅ OCR selesai. Silakan review data di bawah.';
                                return '📁 Klik "Scan OCR Ulang" untuk memperbarui metadata dari cover buku';
                            })
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('ocr_result')
                            ->label('Hasil OCR (JSON)')
                            ->rows(5)
                            ->readOnly()
                            ->columnSpanFull()
                            ->visible(fn(Get $get) => filled($get('ocr_result'))),
                    ]),

                Forms\Components\Section::make('Metadata Buku')
                    ->description('Data buku - silakan review dan edit jika diperlukan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('judul')
                                    ->label('Judul')
                                    ->required()
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

                                Forms\Components\TextInput::make('isbn')
                                    ->label('ISBN')
                                    ->maxLength(17)
                                    ->unique(ignoreRecord: true) // ignoreRecord penting di Edit agar tidak konflik dengan dirinya sendiri
                                    ->rules([
                                        'nullable',
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail) {
                                                if (empty($value)) return;
                                                $clean = preg_replace('/[\s\-]/', '', $value);
                                                if (!$this->isValidIsbn($clean)) {
                                                    $fail('Format ISBN tidak valid. Gunakan ISBN-10 atau ISBN-13 yang benar.');
                                                }
                                            };
                                        },
                                    ])
                                    ->helperText('Contoh: 978-602-123-456-7 atau 9786021234567'),

                                Forms\Components\TextInput::make('issn')
                                    ->label('ISSN')
                                    ->maxLength(17)
                                    ->unique(ignoreRecord: true), // ignoreRecord penting di Edit

                                Forms\Components\TextInput::make('jumlah_halaman')
                                    ->label('Jumlah Halaman')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('ukuran')
                                    ->label('Ukuran')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Textarea::make('sinopsis')
                            ->label('Sinopsis')
                            ->rows(10)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false), // Di Edit, metadata selalu terbuka karena data sudah ada
            ]);
    }

    protected function isValidIsbn(string $isbn): bool
    {
        $length = strlen($isbn);

        if ($length === 10) {
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                if (!is_numeric($isbn[$i])) return false;
                $sum += (int)$isbn[$i] * (10 - $i);
            }
            $last = strtoupper($isbn[9]);
            $sum += ($last === 'X') ? 10 : (is_numeric($last) ? (int)$last : -1);
            return $sum % 11 === 0;
        }

        if ($length === 13) {
            if (!ctype_digit($isbn)) return false;
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$isbn[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $check = (10 - ($sum % 10)) % 10;
            return $check === (int)$isbn[12];
        }

        return false;
    }

    protected function scanOcr(Get $get, Set $set): void
    {
        $frontCover    = $get('cover_depan');
        $backCover     = $get('cover_belakang');
        $copyrightPage = $get('copyright_path');

        if (!$frontCover) {
            Notification::make()
                ->title('Cover depan belum diupload')
                ->danger()
                ->send();
            return;
        }

        try {
            $set('ocr_processing', true);
            $set('ocr_triggered', false);

            $frontPath     = $this->extractFilePath($frontCover, 'front');
            $backPath      = $backCover     ? $this->extractFilePath($backCover, 'back')          : null;
            $copyrightPath = $copyrightPage ? $this->extractFilePath($copyrightPage, 'copyright') : null;

            Log::info('OCR Scan (Edit) - Paths', compact('frontPath', 'backPath', 'copyrightPath'));

            if (!$frontPath || !file_exists($frontPath)) {
                throw new \Exception('File cover depan tidak valid. Path: ' . ($frontPath ?? 'null'));
            }

            $hasBack      = $backPath && file_exists($backPath);
            $hasCopyright = $copyrightPath && file_exists($copyrightPath);
            $imageCount   = 1 + ($hasBack ? 1 : 0) + ($hasCopyright ? 1 : 0);

            Notification::make()
                ->title('Memproses OCR...')
                ->body("Mengekstrak metadata dari {$imageCount} gambar")
                ->info()
                ->send();

            $ocrService = app(OcrService::class);
            $startTime  = microtime(true);

            $result = match(true) {
                $hasBack && $hasCopyright => $ocrService->extractMetadataMulti($frontPath, $backPath, $copyrightPath),
                $hasBack                  => $ocrService->extractMetadataMulti($frontPath, $backPath),
                $hasCopyright             => $ocrService->extractMetadataMulti($frontPath, null, $copyrightPath),
                default                   => $ocrService->extractMetadata($frontPath),
            };

            $duration = round(microtime(true) - $startTime, 2);

            $this->setOcrResults($result, $set);
            $set('ocr_processing', false);
            $set('ocr_triggered', true);

            Notification::make()
                ->title('OCR Berhasil!')
                ->body("Metadata diperbarui dalam {$duration} detik. Jangan lupa klik Simpan.")
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Log::error('OCR Scan Error (Edit)', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
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

    protected function extractFilePath($fileData, string $type = 'front'): ?string
    {
        if (!$fileData) {
            Log::warning("File {$type} data is null or empty");
            return null;
        }

        // CASE 1: String path langsung (file lama yang sudah tersimpan di storage)
        if (is_string($fileData)) {
            if (Storage::disk('public')->exists($fileData)) {
                return Storage::disk('public')->path($fileData);
            }
            if (file_exists($fileData)) {
                return $fileData;
            }
            Log::warning("String path tidak ditemukan untuk {$type}: {$fileData}");
            return null;
        }

        // CASE 2: Array Filament {uuid => TemporaryUploadedFile}
        if (is_array($fileData)) {
            if (empty($fileData)) {
                Log::warning("Empty array untuk {$type}");
                return null;
            }

            foreach ($fileData as $value) {
                if ($value instanceof TemporaryUploadedFile) {
                    return $this->resolveTemporaryFile($value, $type);
                }
                // Jika value adalah string (path file lama)
                if (is_string($value)) {
                    if (Storage::disk('public')->exists($value)) {
                        return Storage::disk('public')->path($value);
                    }
                }
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        if ($subValue instanceof TemporaryUploadedFile) {
                            return $this->resolveTemporaryFile($subValue, $type);
                        }
                    }
                }
            }

            Log::warning("Tidak ada TemporaryUploadedFile dalam array untuk {$type}");
            return null;
        }

        // CASE 3: Langsung TemporaryUploadedFile
        if ($fileData instanceof TemporaryUploadedFile) {
            return $this->resolveTemporaryFile($fileData, $type);
        }

        Log::warning("Tipe file tidak dikenali untuk {$type}", ['type' => gettype($fileData)]);
        return null;
    }

    protected function resolveTemporaryFile(TemporaryUploadedFile $file, string $type): ?string
    {
        $originalPath = null;
        try {
            $class = new \ReflectionClass($file);
            while ($class) {
                if ($class->hasProperty('originalPath')) {
                    $prop = $class->getProperty('originalPath');
                    $prop->setAccessible(true);
                    $originalPath = $prop->getValue($file);
                    break;
                }
                $class = $class->getParentClass() ?: null;
            }
        } catch (\Throwable $e) {
            Log::warning("Reflection gagal ({$type}): " . $e->getMessage());
        }

        $realPath = $file->getRealPath();
        if ($realPath && file_exists($realPath)) {
            return $realPath;
        }

        if ($originalPath) {
            $privatePath = storage_path('app/private/' . $originalPath);
            if (file_exists($privatePath)) {
                return $privatePath;
            }

            $basePath = storage_path('app/private/livewire-tmp/' . basename($originalPath));
            if (file_exists($basePath)) {
                return $basePath;
            }
        }

        Log::error("resolveTemporaryFile({$type}): semua opsi gagal", [
            'originalPath' => $originalPath,
        ]);

        return null;
    }

    protected function setOcrResults(array $result, Set $set): void
    {
        $data = $result['data'] ?? $result;

        $this->resetOcrFields($set);

        $set('judul',     $data['title']     ?? null);
        $set('pengarang', $data['author']    ?? null);
        $set('penerbit',  $data['publisher'] ?? null);
        $set('isbn',      $data['isbn']      ?? null);
        $set('issn',      $data['issn']      ?? null);
        $set('edisi',     $data['edition']   ?? null);
        $set('sinopsis',  $data['synopsis']  ?? $data['description'] ?? null);
        $set('ukuran',    $data['size']      ?? null);

        $tahun = $data['year'] ?? $data['publication_year'] ?? null;
        $set('tahun_terbit', is_numeric($tahun) && $tahun >= 1800 && $tahun <= date('Y')
            ? (int) $tahun
            : null
        );

        $pages = $data['page'] ?? $data['pages'] ?? null;
        $set('jumlah_halaman', $pages !== null ? (string) $pages : null);

        $set('ocr_result', json_encode([
            'success'   => $result['success'] ?? true,
            'data'      => $data,
            'timestamp' => now()->toDateTimeString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function resetOcrFields(Set $set): void
    {
        foreach ([
            'judul', 'pengarang', 'penerbit', 'tahun_terbit',
            'edisi', 'sinopsis', 'isbn', 'issn',
            'jumlah_halaman', 'ukuran', 'ocr_result',
        ] as $field) {
            $set($field, null);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Di Edit tidak perlu set user_id karena sudah ada dari saat Create
        // Hanya hapus field state sementara OCR
        $temporaryFields = [
            'ocr_processing',
            'ocr_triggered',
            'ocr_status',
            'ocr_result',
        ];

        foreach ($temporaryFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}