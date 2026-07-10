<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'collection_id',
        'location_id',
        'subject_id',
        'user_id',
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'edisi',
        'sinopsis',
        'jumlah_halaman',
        'ukuran',
        'isbn',
        'issn',
        'cover_depan',
        'cover_belakang',
        'copyright_path',
        'status',
    ];

    protected $casts = [
        'tahun_terbit' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
}
