<?php

namespace App\Providers;

use App\Models\Petugas;
use App\Policies\PetugasPolicy;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            // Tesseract.js from CDN
            Js::make('tesseract', 'https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/5.0.2/tesseract.min.js'),
            Js::make('book-ocr', asset('js/book-ocr.js')),
        ]);

        Gate::policy(Petugas::class, PetugasPolicy::class);
    }
}
