<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookscan - OCR Metadata Buku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        /* GRADIENT HERO - Orange ke Ungu */
        .gradient-hero {
            background: linear-gradient(135deg, #f97316 40%, #8b5cf6 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
        }

        .step-circle {
            background: linear-gradient(135deg, #f97316, #8b5cf6);
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.3);
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.5;
                transform: scale(1.1);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        .gradient-text {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
        }

        .faq-item {
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            background: #f8fafc;
        }

        .dark .faq-item:hover {
            background: #1e293b;
        }

        /* Warna kustom untuk badge */
        .badge-orange {
            background: #fef3c7;
            color: #d97706;
        }
        .dark .badge-orange {
            background: #451a03;
            color: #fbbf24;
        }

        .badge-purple {
            background: #ede9fe;
            color: #7c3aed;
        }
        .dark .badge-purple {
            background: #2e1065;
            color: #a78bfa;
        }

        .badge-blue {
            background: #dbeafe;
            color: #2563eb;
        }
        .dark .badge-blue {
            background: #1e3a5f;
            color: #60a5fa;
        }

        /* CTA Gradient */
        .cta-gradient {
            background: linear-gradient(135deg, #f97316 30%, #8b5cf6 100%);
        }

        /* Button Gradient */
        .btn-primary {
            background: linear-gradient(135deg, #f97316, #f59e0b);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ea580c, #d97706);
        }

        /* Warna icon di feature */
        .icon-orange {
            background: linear-gradient(135deg, #f97316, #f59e0b);
        }
        .icon-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        .icon-blue {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }
        .icon-amber {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }
        .icon-indigo {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
        }
        .icon-teal {
            background: linear-gradient(135deg, #14b8a6, #0d9488);
        }
    </style>
</head>

<body class="antialiased">

    <div class="min-h-screen bg-white dark:bg-gray-950">

        {{-- ============================================ --}}
        {{-- 1. HEADER / NAVIGASI --}}
        {{-- ============================================ --}}
        <nav
            class="bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 fixed w-full z-50 transition-all">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-orange-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/25">
                        <i class="fas fa-book-open text-white text-lg"></i>
                    </div>
                    <span class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Book<span
                            class="text-orange-500">Scan</span></span>
                </div>
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#fitur"
                        class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-orange-400 transition">Fitur</a>
                    <a href="#cara-kerja"
                        class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-orange-400 transition">Cara
                        Kerja</a>
                    <a href="#faq"
                        class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-orange-400 transition">FAQ</a>
                    <a href="{{ url('/admin/login') }}"
                        class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white text-sm font-semibold rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40">
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                    </a>
                </div>
                <button class="lg:hidden text-gray-600 dark:text-gray-300">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </nav>

        {{-- ============================================ --}}
        {{-- 2. HERO SECTION --}}
        {{-- ============================================ --}}
        <section class="gradient-hero pt-32 pb-20 overflow-hidden relative">
            <!-- Background decoration -->
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-10">
                <div class="absolute top-20 right-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 left-20 w-64 h-64 bg-purple-400 rounded-full blur-3xl"></div>
            </div>

            <div class="container mx-auto px-6 relative z-10">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="lg:w-1/2 text-white">
                        <div
                            class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full text-sm mb-6 border border-white/10">
                            <span class="w-2 h-2 bg-green-400 rounded-full pulse-dot"></span>
                            <span>Solusi Digitalisasi Perpustakaan</span>
                        </div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-6 tracking-tight">
                            Otomatisasi Ekstraksi<br>
                            <span class="gradient-text">Metadata Buku</span>
                        </h1>
                        <p class="text-lg text-white/90 mb-8 max-w-lg leading-relaxed">
                            Ubah gambar sampul buku menjadi data katalog terstruktur secara otomatis dengan teknologi
                            OCR berbasis AI (Qwen2-VL & Gemini).
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ url('/admin/login') }}"
                                class="group inline-flex items-center px-8 py-4 bg-white text-orange-600 font-bold rounded-2xl hover:bg-orange-50 transition-all shadow-2xl shadow-black/20 hover:shadow-black/30">
                                <span>🚀 Mulai Sekarang</span>
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition"></i>
                            </a>
                            <a href="#demo"
                                class="inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-2xl hover:bg-white/20 transition border border-white/20">
                                <i class="fas fa-play-circle mr-2"></i>Lihat Demo
                            </a>
                        </div>
                        <div class="mt-8 flex items-center space-x-8 text-sm text-white/80">
                            <span><i class="fas fa-check-circle text-green-400 mr-2"></i>Tanpa instalasi</span>
                            <span><i class="fas fa-bolt text-yellow-300 mr-2"></i>Proses cepat</span>
                            <span><i class="fas fa-bullseye text-rose-400 mr-2"></i>Akurasi tinggi</span>
                        </div>
                    </div>
                    <div class="lg:w-1/2">
                        <div class="glass-card rounded-3xl p-8 floating">
                            <div class="bg-white/5 rounded-2xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-white/60 text-sm">📸 Preview OCR</span>
                                    <span
                                        class="px-3 py-1 bg-green-500/30 text-green-300 text-xs font-semibold rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>Active
                                    </span>
                                </div>
                                <img src="{{ asset('images/ocr-demo.png') }}" alt="OCR Demo" class="w-full rounded-xl">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================ --}}
        {{-- 3. MASALAH & SOLUSI --}}
        {{-- ============================================ --}}
        <section class="py-20 bg-gray-50 dark:bg-gray-900/50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="badge-orange inline-block px-4 py-1.5 text-sm font-semibold rounded-full">
                        MASALAH & SOLUSI
                    </span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-4 tracking-tight">
                        Mengapa Perlu Sistem Ini?
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    {{-- Sebelumnya (Manual) --}}
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-red-100 dark:border-red-900/30 shadow-lg hover:shadow-xl transition">
                        <div
                            class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-2xl flex items-center justify-center mb-4">
                            <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-red-600 dark:text-red-400 mb-3">Sebelumnya (Manual)</h3>
                        <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-400 mr-3 mt-1 text-sm"></i>
                                <span>Membutuhkan waktu 5-10 menit per buku</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-400 mr-3 mt-1 text-sm"></i>
                                <span>Rawan kesalahan input data</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-400 mr-3 mt-1 text-sm"></i>
                                <span>Proses ketik ulang informasi dari sampul</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-times text-red-400 mr-3 mt-1 text-sm"></i>
                                <span>Tidak terstruktur dengan baik</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Sekarang (Otomatis) --}}
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-green-100 dark:border-green-900/30 shadow-lg hover:shadow-xl transition">
                        <div
                            class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mb-4">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400 mb-3">Sekarang (Otomatis)</h3>
                        <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-400 mr-3 mt-1 text-sm"></i>
                                <span>Proses sekitar 1 menit per buku</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-400 mr-3 mt-1 text-sm"></i>
                                <span>Akurasi tinggi (85%+)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-400 mr-3 mt-1 text-sm"></i>
                                <span>OCR + AI (Qwen2-VL & Gemini)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-400 mr-3 mt-1 text-sm"></i>
                                <span>Data langsung terstruktur & tersimpan</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================ --}}
        {{-- 4. CARA KERJA --}}
        {{-- ============================================ --}}
        <section id="cara-kerja" class="py-20 bg-white dark:bg-gray-950">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="badge-purple inline-block px-4 py-1.5 text-sm font-semibold rounded-full">
                        CARA KERJA
                    </span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-4 tracking-tight">
                        Hanya 4 Langkah Mudah
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-3">Proses ekstraksi metadata buku menjadi lebih
                        sederhana dan cepat</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-6xl mx-auto">
                    <div class="text-center group">
                        <div
                            class="w-20 h-20 step-circle rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:scale-110 transition">
                            <i class="fas fa-upload text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Upload Gambar</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Unggah cover depan & belakang buku</p>
                        <div
                            class="hidden md:block w-16 h-0.5 bg-gradient-to-r from-orange-500 to-purple-600 mx-auto mt-4">
                        </div>
                    </div>
                    <div class="text-center group">
                        <div
                            class="w-20 h-20 step-circle rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:scale-110 transition">
                            <i class="fas fa-robot text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">OCR + AI</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Qwen2-VL ekstrak teks dari gambar</p>
                        <div
                            class="hidden md:block w-16 h-0.5 bg-gradient-to-r from-orange-500 to-purple-600 mx-auto mt-4">
                        </div>
                    </div>
                    <div class="text-center group">
                        <div
                            class="w-20 h-20 step-circle rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:scale-110 transition">
                            <i class="fas fa-magic text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Koreksi + Validasi</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Gemini perbaiki & validasi data</p>
                        <div
                            class="hidden md:block w-16 h-0.5 bg-gradient-to-r from-orange-500 to-purple-600 mx-auto mt-4">
                        </div>
                    </div>
                    <div class="text-center group">
                        <div
                            class="w-20 h-20 step-circle rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:scale-110 transition">
                            <i class="fas fa-save text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Review & Simpan</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Edit jika perlu, lalu simpan ke
                            database</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================ --}}
        {{-- 5. FITUR UNGGULAN --}}
        {{-- ============================================ --}}
        <section id="fitur" class="py-20 bg-gray-50 dark:bg-gray-900/50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="badge-blue inline-block px-4 py-1.5 text-sm font-semibold rounded-full">
                        FITUR UNGGULAN
                    </span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-4 tracking-tight">
                        Dirancang untuk Perpustakaan Modern
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 feature-card border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-14 h-14 icon-orange rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-orange-500/25">
                            <i class="fas fa-eye text-white text-xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">OCR Berbasis AI</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                            Menggunakan Vision-Language Model Qwen2-VL yang mampu memahami konteks visual dan teks
                            secara bersamaan.
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 feature-card border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-14 h-14 icon-purple rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-purple-500/25">
                            <i class="fas fa-wand-magic-sparkles text-white text-xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Koreksi + Validasi</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                            Gemini melakukan perbaikan teks, normalisasi, dan validasi semantik untuk hasil yang akurat.
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 feature-card border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-14 h-14 icon-amber rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-amber-500/25">
                            <i class="fas fa-pen-to-square text-white text-xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Review & Edit</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                            Petugas dapat meninjau dan mengedit hasil ekstraksi sebelum disimpan ke database.
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 feature-card border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-14 h-14 icon-indigo rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-indigo-500/25">
                            <i class="fas fa-list-check text-white text-xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Ekstrak 10+ Metadata</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                            Judul, pengarang, penerbit, tahun, ISBN, ISSN, sinopsis, halaman, edisi, dan ukuran.
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 feature-card border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-14 h-14 icon-orange rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-orange-500/25">
                            <i class="fas fa-shield-halved text-white text-xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Autentikasi & Role</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                            Sistem login dengan role admin dan petugas untuk keamanan data.
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-8 feature-card border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-14 h-14 icon-teal rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-teal-500/25">
                            <i class="fas fa-database text-white text-xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Penyimpanan Database</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                            Data tersimpan terstruktur di MySQL, mendukung pencarian dan pengelolaan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================ --}}
        {{-- 6. CTA SECTION --}}
        {{-- ============================================ --}}
        <section class="py-20 cta-gradient relative overflow-hidden">
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-10">
                <div class="absolute -top-20 -right-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            </div>
            <div class="container mx-auto px-6 text-center text-white relative z-10">
                <h2 class="text-3xl md:text-5xl font-black tracking-tight mb-4">
                    Wujudkan Transformasi Digital <br>Perpustakaan PENS
                </h2>
                <p class="text-white/90 text-lg mb-8 max-w-2xl mx-auto">
                    Dukung program digitalisasi perpustakaan dengan sistem ekstraksi metadata otomatis.
                    Mudah, cepat, dan terintegrasi dengan database perpustakaan.
                </p>
                <a href="{{ url('/admin/login') }}"
                    class="inline-flex items-center px-10 py-4 bg-white text-orange-600 font-bold rounded-2xl hover:bg-orange-50 transition-all shadow-2xl shadow-black/20 hover:shadow-black/30 text-lg">
                    <i class="fas fa-arrow-right mr-3"></i>
                    Akses Sistem
                </a>
                <p class="text-white/80 text-sm mt-4">Hanya untuk petugas Perpustakaan PENS</p>
            </div>
        </section>

        {{-- ============================================ --}}
        {{-- 7. FAQ --}}
        {{-- ============================================ --}}
        <section id="faq" class="py-20 bg-white dark:bg-gray-950">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="badge-purple inline-block px-4 py-1.5 text-sm font-semibold rounded-full">
                        FAQ
                    </span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-4 tracking-tight">
                        Pertanyaan Umum
                    </h2>
                </div>
                <div class="max-w-3xl mx-auto space-y-4">
                    <div
                        class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 faq-item border border-gray-100 dark:border-gray-800">
                        <div class="flex items-start">
                            <div
                                class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-question text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Apa itu OCR?</h4>
                                <p class="text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">OCR (Optical Character
                                    Recognition) adalah teknologi untuk mengubah gambar teks menjadi teks digital yang
                                    dapat diedit dan diproses lebih lanjut.</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 faq-item border border-gray-100 dark:border-gray-800">
                        <div class="flex items-start">
                            <div
                                class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-question text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Apa perbedaan Qwen2-VL dan OCR
                                    biasa?</h4>
                                <p class="text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">Qwen2-VL adalah
                                    Vision-Language Model yang tidak hanya membaca teks, tetapi juga memahami konteks
                                    visual, sehingga dapat mengelompokkan metadata dengan lebih akurat.</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 faq-item border border-gray-100 dark:border-gray-800">
                        <div class="flex items-start">
                            <div
                                class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-question text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Bagaimana proses OCR bekerja?</h4>
                                <p class="text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                                    Sistem menggunakan Qwen2-VL (Vision-Language Model) untuk membaca teks dari gambar
                                    cover buku,
                                    kemudian Gemini melakukan koreksi dan validasi metadata. Hasilnya langsung
                                    ditampilkan untuk
                                    ditinjau oleh petugas sebelum disimpan ke database.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 faq-item border border-gray-100 dark:border-gray-800">
                        <div class="flex items-start">
                            <div
                                class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center flex-shrink-0 mr-4">
                                <i class="fas fa-question text-amber-600 dark:text-amber-400"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Metadata apa saja yang diekstrak?
                                </h4>
                                <p class="text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                                    Sistem mengekstrak 10 field metadata: judul, pengarang, penerbit, tahun terbit,
                                    ISBN, ISSN,
                                    sinopsis, jumlah halaman, edisi, dan ukuran buku. Semua field dapat diedit oleh
                                    petugas
                                    sebelum disimpan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================ --}}
        {{-- 8. FOOTER --}}
        {{-- ============================================ --}}
        <footer class="bg-gray-900 text-white py-12">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-3 mb-4 md:mb-0">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-orange-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-book-open text-white"></i>
                        </div>
                        <span class="text-xl font-bold">Book<span class="text-orange-400">Scan</span></span>
                    </div>
                    <div class="flex space-x-6 text-sm text-gray-400">
                        <a href="#" class="hover:text-orange-400 transition">Kebijakan Privasi</a>
                        <a href="#" class="hover:text-orange-400 transition">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-orange-400 transition">Kontak</a>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} BookScan - Sistem OCR Metadata Buku. All rights reserved.
                </div>
            </div>
        </footer>

    </div>
</body>

</html>