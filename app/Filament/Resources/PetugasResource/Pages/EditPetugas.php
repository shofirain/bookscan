<?php

namespace App\Filament\Resources\PetugasResource\Pages;

use App\Filament\Resources\PetugasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditPetugas extends EditRecord
{
    protected static string $resource = PetugasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Sinkronkan perubahan name/email/password ke tabel users
        $user = $this->record->user;
 
        if ($user) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                ...(filled($data['password'] ?? null)
                    ? ['password' => Hash::make($data['password'])]
                    : []
                ),
            ]);
        }
 
        // Password tidak disimpan di tabel petugas
        unset($data['password']);
 
        return $data;
    }
}
