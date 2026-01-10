<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
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
                            ->required(),

                        Forms\Components\Select::make('location_id')
                            ->label('Lokasi')
                            ->relationship('location', 'lokasi')
                            ->required(),

                        Forms\Components\Select::make('subject_id')
                            ->label('Subyek')
                            ->relationship('subject', 'subyek')
                            ->required(),
                        
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
                                    ->reactive(),
                                    // ->afterStateUpdated(function (Set $set) {
                                    //     // Reset OCR text when cover is updated
                                    //     $set('ocr_front_text', null);
                                    //     $set('ocr_processed', false);
                                    // }),
                
                                Forms\Components\FileUpload::make('cover_belakang')
                                    ->label('Cover Belakang')
                                    ->image()
                                    ->required()
                                    ->directory('book-covers/back')
                                    ->imagePreviewHeight(250)
                                    ->imageEditor()
                                    ->reactive(),
                                    // ->afterStateUpdated(function (Set $set) {
                                    //     // Reset OCR text when cover is updated
                                    //     $set('ocr_back_text', null);
                                    //     $set('ocr_processed', false);
                                    // }),
                            ]),

                            Forms\Components\Hidden::make('ocr_processed')
                                ->default(false),
                            
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('processOcr')
                                    ->label('Proses OCR & Ekstrak Metadata')
                                    ->color('primary')
                                    ->size('lg')
                                    ->visible(fn (Get $get) =>
                                        $get('cover_depan') &&
                                        $get('cover_belakang') &&
                                        !$get('ocr_processed')
                                    )
                                    ->action(function (Set $set, Get $get, $livewire) {
                                        try {
                                            $coverDepan = $get('cover_depan');
                                            $coverBelakang = $get('cover_belakang');
        
                                            if (!$coverDepan || !$coverBelakang) {
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body('Cover depan dan belakang harus diupload terlebih dahulu.')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }
        
                                            Notification::make()
                                                ->title('Memproses...')
                                                ->body('Sedang melakukan OCR dan ekstrak metadata')
                                                ->info()
                                                ->send();
        
                                            // Get full paths
                                            $frontPath = Storage::disk('public')->path($coverDepan);
                                            $backPath = Storage::disk('public')->path($coverBelakang);
        
                                            // Call OCR service
                                            $ocrServide = app(\App\Services\OcrService::class);
        
                                            $frontOcr = $ocrServide->extractText($frontPath);
                                            $backOcr = $ocrServide->extractText($backPath);
        
                                            if (!$frontOcr['success'] || !$backOcr['success']) {
                                                throw new \Exception('OCR gagal: ' . ($frontOcr['error'] ?? $backOcr['error']));
                                            }
        
                                            // Set OCR text results
                                            $set('ocr_front_text', $frontOcr['text']);
                                            $set('ocr_back_text', $backOcr['text']);
        
                                            // Call Gemini service for metadata extraction
                                            $geminiService = app(\App\Services\GeminiService::class);
                                            $metadata = $geminiService->extractMetadata(
                                                $frontOcr['text'],
                                                $backOcr['text']
                                            );
        
                                            if ($metadata['success']) {
                                                $data = $metadata['metadata'];
                                                $set('judul', $data['judul'] ?? null);
                                                $set('pengarang', $data['pengarang'] ?? null);
                                                $set('penerbit', $data['penerbit'] ?? null);
                                                $set('tahun_terbit', $data['tahun_terbit'] ?? null);
                                                $set('edisi', $data['edisi'] ?? null);
                                                $set('sinopsis', $data['sinopsis'] ?? null);
                                                $set('jumlah_halaman', $data['jumlah_halaman'] ?? null);
                                                $set('ukuran', $data['ukuran'] ?? null);
                                                $set('isbn', $data['isbn'] ?? null);
                                                $set('issn', $data['issn'] ?? null);
        
                                                Notification::make()
                                                    ->title('Sukses')
                                                    ->body('OCR dan ekstrak metadata berhasil.')
                                                    ->success()
                                                    ->duration(5000)
                                                    ->send();
                                            } else {
                                                throw new \Exception('Gagal ekstrak metadata: ' . $metadata['error']);
                                            }
                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title('Proses Gagal')
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    })
                                    ->requiresConfirmation()
                                    ->modalHeading('Proses OCR & Ekstrak Metadata?')
                                    ->modalDescription('Sistem akan membaca teks dari kedua cover dan mengekstrak metadata buku secara otomatis.')
                                    ->modalSubmitActionLabel('Ya, Proses Sekarang'),
        
                        ])->fullWidth(),

                    ])->collapsible(),
                
                // Forms\Components\Section::make('Hasil OCR')
                //     ->schema([
                //         Forms\Components\Textarea::make('ocr_front_text')
                //             ->label('Teks dari Cover Depan')
                //             ->rows(5)
                //             ->placeholder('Hasil OCR akan ditampilkan di sini...')
                //             ->disabled()
                //             ->dehydrated(true),

                //         Forms\Components\Textarea::make('ocr_back_text')
                //             ->label('Teks dari Cover Belakang')
                //             ->rows(5)
                //             ->placeholder('Hasil OCR akan ditampilkan di sini...')
                //             ->disabled()
                //             ->dehydrated(true),
                //     ])
                //     ->visible(fn (Get $get) => $get('ocr_front_text') || $get('ocr_back_text'))
                //     ->collapsible()
                //     ->collapsed(false),

                // Forms\Components\Section::make('Metadata Buku dari AI')
                //     ->schema([
                //         Forms\Components\TextInput::make('judul')
                //             ->label('Judul')
                //             ->maxLength(255),

                //         Forms\Components\TextInput::make('pengarang')
                //             ->label('Pengarang')
                //             ->maxLength(255),

                //         Forms\Components\TextInput::make('penerbit')
                //             ->label('Penerbit')
                //             ->maxLength(255),
                        
                //         Forms\Components\TextInput::make('tahun_terbit')
                //             ->label('Tahun Terbit')
                //             ->numeric()
                //             ->minValue(1800)
                //             ->maxValue(date('Y')),

                //         Forms\Components\TextInput::make('edisi')
                //             ->label('Edisi')
                //             ->maxLength(255),
                            
                //         Forms\Components\Textarea::make('sinopsis')
                //             ->label('Sinopsis')
                //             ->rows(5)
                //             ->maxLength(2000)
                //             ->columnSpanFull(),
                            
                //         Forms\Components\TextInput::make('isbn')
                //             ->label('ISBN')
                //             ->maxLength(255),

                //         Forms\Components\TextInput::make('issn')
                //             ->label('ISSN')
                //             ->maxLength(255),

                //         Forms\Components\TextInput::make('jumlah_halaman')
                //             ->label('Jumlah Halaman')
                //             ->numeric()
                //             ->minValue(1),
                        
                //             Forms\Components\TextInput::make('ukuran')
                //             ->label('Ukuran')
                //             ->maxLength(255),
                //     ])
                //     ->visible(fn (Get $get) => $get('ocr_processed'))
                //     ->collapsible(),

            ]);
    }
}
