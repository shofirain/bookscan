<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function extractMetadata(string $frontText, string $backText): array
    {
        try {
            $prompt = $this->buildPrompt($frontText, $backText);

            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topK' => 40,
                    'topP' => 0.95,
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                return $this->parseMetadata($text);
            }

            return ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Gemini AI Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function buildPrompt(string $frontText, string $backText): string
    {
        return <<<PROMPT
Ekstrak metadata buku dari teks OCR berikut. Berikan hasilnya dalam format JSON yang valid.

Teks dari Cover Depan:
{$frontText}

Teks dari Cover Belakang:
{$backText}

Ekstrak informasi berikut (gunakan null jika tidak ditemukan):
- title: judul buku
- author: nama pengarang
- publisher: nama penerbit
- isbn: nomor ISBN (10 atau 13 digit)
- publication_year: tahun terbit (format: YYYY)
- description: deskripsi/sinopsis buku
- category: kategori/genre buku
- price: harga buku (jika ada)

Berikan hanya JSON tanpa penjelasan tambahan. Format:
{
    "title": "...",
    "author": "...",
    "publisher": "...",
    "isbn": "...",
    "publication_year": "...",
    "description": "...",
    "category": "...",
    "price": "..."
}
PROMPT;
    }

    protected function parseMetadata(string $text): array
    {
        // Bersihkan markdown code block jika ada
        $text = preg_replace('/```json\s*|\s*```/', '', $text);
        $text = trim($text);

        try {
            $metadata = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'success' => true,
                    'metadata' => $metadata
                ];
            }
        } catch (\Exception $e) {
            Log::error('JSON Parse Error: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'error' => 'Failed to parse metadata'
        ];
    }
}
