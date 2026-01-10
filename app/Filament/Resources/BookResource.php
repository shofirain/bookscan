<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Books';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Section::make('Informasi Buku')
                //     ->schema([
                //         Forms\Components\Select::make('collection_id')
                //             ->label('Koleksi')
                //             ->relationship('collection', 'koleksi')
                //             ->required(),

                //         Forms\Components\Select::make('location_id')
                //             ->label('Lokasi')
                //             ->relationship('location', 'lokasi')
                //             ->required(),

                //         Forms\Components\Select::make('subject_id')
                //             ->label('Subyek')
                //             ->relationship('subject', 'subyek')
                //             ->required(),
                        
                //         Forms\Components\TextInput::make('status')
                //             ->label('Status')
                //             ->maxLength(255),
                //     ])->columns(2),

                // Forms\Components\Section::make('Upload Cover Buku')
                //     ->schema([
                //         Forms\Components\FileUpload::make('cover_depan')
                //             ->label('Cover Depan')
                //             ->image()
                //             ->required()
                //             ->directory('book-covers/front')
                //             ->columnSpanFull(),
        
                //         Forms\Components\FileUpload::make('cover_belakang')
                //             ->label('Cover Belakang')
                //             ->image()
                //             ->required()
                //             ->directory('book-covers/back')
                //             ->columnSpanFull(),
                //     ])->columns(2),

                // Forms\Components\Section::make('Data Buku dari AI')
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
                //             ->minValue(0)
                //             ->maxValue(date('Y')),

                //         Forms\Components\TextInput::make('edisi')
                //             ->label('Edisi')
                //             ->maxLength(255),
                            
                //         Forms\Components\Textarea::make('sinopsis')
                //             ->label('Sinopsis')
                //             ->rows(5)
                //             ->maxLength(2000),
                            
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
                //     ])->columns(2),

                // Forms\Components\Section::make('OCR Results')
                //     ->schema([
                //         Forms\Components\Textarea::make('ocr_front_text')
                //             ->label('OCR Cover Depan')
                //             ->rows(3)
                //             ->disabled(),

                //         Forms\Components\Textarea::make('ocr_back_text')
                //             ->label('OCR Cover Belakang')
                //             ->rows(3)
                //             ->disabled(),
                //     ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_depan')
                    ->label('Cover Depan')
                    ->circular()
                    ->size(60),
                
                    Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->sortable()
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) > 40) {
                            return $state;
                        }

                        return null;
                    }),
                
                Tables\Columns\TextColumn::make('pengarang')
                    ->label('Pengarang')
                    ->sortable()
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('penerbit')
                    ->label('Penerbit')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('tahun_terbit')
                    ->label('Tahun Terbit')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sinopsis')
                    ->label('Sinopsis')
                    ->limit(50),

                Tables\Columns\TextColumn::make('collection.koleksi')
                    ->label('Koleksi')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
