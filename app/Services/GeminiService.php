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

    public function extractMetadata(string $ocrText): array
    {
        try {
            Log::info('GeminiService: Starting extraction for OCR test', ['text_length' => strlen($ocrText)]);
            
            $prompt = $this->buildPrompt($ocrText);

            $response = Http::timeout(30)
                ->post($this->apiUrl, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('GeminiService: API response received', ['response' => $result]);

                $extractedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                return [
                    'success' => true,
                    'raw_response' => $extractedText,
                    'json_data' => json_decode($this->cleanJsonResponse($extractedText), true)
                ];

            } else {
                Log::error('GeminiService: API request failed', [
                    'status' => $response->status(), 
                    'body' => $response->body()
                ]);

                return [
                    'success' => false, 
                    'error' => 'API request failed' . $response->status(),
                    'raw_response' => $response->body(),
                    'json_data' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('GeminiService: Exception occurred', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false, 
                'error' => 'Exception: ' . $e->getMessage(),
                'raw_response' => null,
                'json_data' => null,
                'parsed_data' => null
            ];
        }
    }

    protected function buildPrompt(string $ocrText): string
    {
        return "{$ocrText}
        Ini adalah hasil OCR dari buku.
        Ekstrak metadata buku dari teks OCR berikut. Berikan hasilnya dalam format JSON berikut:
        
        {
        \"judul\": \"\",
        \"pengarang\": \"\",
        \"penerbit\": \"\",
        \"tahun_terbit\": \"\",
        \"edisi\": \"\",
        \"isbn\": \"\",
        \"issn\": \"\",
        \"jumlah_halaman\": 0,
        \"sinopsis\": \"\"
        }
        Jika data tidak ditemukan, kosongkan field.
        Berikan HANYA JSON tanpa penjelasan tambahan.";
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
