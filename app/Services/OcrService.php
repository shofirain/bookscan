<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    protected $apiKey;
    protected $apiUrl = 'https://api-inference.huggingface.co/models/microsoft/Florence-2-large';

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
    }

    public function extractText(string $imagePath): array
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl, [
                'inputs' => $imageData,
                'parameters' => [
                    'task' => 'ocr',
                ]
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'text' => $this->parseOcrResponse($response->json())
                ];
            }

            return [
                'success' => false,
                'error' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('OCR Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function parseOcrResponse(array $response): string
    {
        // Parse response Florence-2
        if (isset($response[0]['generated_text'])) {
            return $response[0]['generated_text'];
        }
        
        return json_encode($response);
    }
}