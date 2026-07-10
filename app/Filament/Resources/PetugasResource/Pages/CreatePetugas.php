<?php

namespace App\Filament\Resources\PetugasResource\Pages;

use App\Filament\Resources\PetugasResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreatePetugas extends CreateRecord
{
    protected static string $resource = PetugasResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Buat akun user untuk login
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
 
            // 2. Assign role "petugas" lewat Spatie Permission
            $user->assignRole('petugas');
 
            // 3. Simpan data petugas, hubungkan ke user_id yang baru dibuat
            $data['user_id'] = $user->id;
            unset($data['password']);
 
            return static::getModel()::create($data);
        });
    }
}
