<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/admin', function () {
//     return redirect('/admin/login');
// });

// Route::get('/', function () {
//     return view('landing-page');
// });

// Route::get('/', [LandingController::class, 'index']);

// Landing Page (Publik)
Route::get('/', [LandingController::class, 'index'])->name('home');

// Admin redirect ke login (jika belum login)
// Route::get('/admin', function () {
//     return redirect('/admin/login');
// })->name('admin.redirect');