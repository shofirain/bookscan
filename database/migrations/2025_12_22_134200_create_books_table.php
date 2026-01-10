<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->nullable()->constrained('collections')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('judul')->nullable();
            $table->string('pengarang')->nullable();
            $table->string('penerbit')->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->string('edisi')->nullable();
            $table->string('sinopsis')->nullable();
            $table->integer('jumlah_halaman')->nullable();
            $table->string('ukuran')->nullable();
            $table->string('isbn')->nullable()->unique();
            $table->string('issn')->nullable()->unique();
            $table->string('cover_depan')->nullable();
            $table->string('cover_belakang')->nullable();
            $table->string('status')->default('tersedia');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
