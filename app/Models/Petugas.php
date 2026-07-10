<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $fillable = [
        'user_id',    
        'nomor_induk',
        'name',
        'email',
        'phone',
        'address',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        // Hapus juga akun user terkait saat data petugas dihapus,
        // supaya tidak ada akun login yang menggantung tanpa profil.
        static::deleting(function (Petugas $petugas) {
            $petugas->user?->delete();
        });
    }
}
