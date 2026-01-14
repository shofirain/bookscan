<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OcrService
{
    private string $apiUrl = 'https://api-inference.huggingface.co/models/microsoft/Florence-2-large';
    private string $apiToken;

    public function __construct()
    {
        $this->apiToken = config('services.huggingface.token');
    }

    /**
     * Process OCR on an image with detailed text extraction
     */
    public function processImage(string $imagePath): string
    {
        $fullPath = storage_path('app/' . $imagePath);

        $response = Http::timeout(60)->post('http://127.0.0.1:8000/ocr', [
            'image_path' => $fullPath,
        ]);

        if ($response->failed()) {
            return '';
        }

        return $response->json('text') ?? '';
    }

    public function extractMetadata(string $frontText, string $backText): array
    {
        return []; // metadata sudah ditangani Python
    }
    
    // public function processImage(string $imagePath): ?string
    // {
    //     try {
    //         // Check if path is valid
    //         if (empty($imagePath)) {
    //             Log::error('Image path is empty');
    //             return null;
    //         }

    //         // Check if it's an absolute path (from temp upload) or relative path (from storage)
    //         if (file_exists($imagePath)) {
    //             $fullPath = $imagePath; // Already absolute path from temp upload
    //             Log::info('Using absolute path: ' . $fullPath);
    //         } else {
    //             $fullPath = Storage::path($imagePath); // Relative path from storage
    //             Log::info('Using storage path: ' . $fullPath);
    //         }

    //         if (!file_exists($fullPath)) {
    //             Log::error('Image file not found: ' . $fullPath);
    //             return null;
    //         }

    //         // Check if file is readable
    //         if (!is_readable($fullPath)) {
    //             Log::error('Image file is not readable: ' . $fullPath);
    //             return null;
    //         }

    //         $imageData = file_get_contents($fullPath);

    //         if ($imageData === false) {
    //             Log::error('Failed to read image data: ' . $fullPath);
    //             return null;
    //         }

    //         $base64Image = base64_encode($imageData);

    //         // Use Florence-2 with OCR_WITH_REGION task for detailed text extraction
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . $this->apiToken,
    //             'Content-Type' => 'application/json',
    //         ])
    //         ->timeout(120)
    //         ->post($this->apiUrl, [
    //             'inputs' => $base64Image,
    //             'parameters' => [
    //                 'task' => '<OCR_WITH_REGION>', // Task untuk OCR detail dengan region
    //             ],
    //             'options' => [
    //                 'wait_for_model' => true,
    //                 'use_cache' => false,
    //             ]
    //         ]);

    //         if ($response->successful()) {
    //             $result = $response->json();
    //             return $this->extractTextFromResponse($result);
    //         }

    //         // Retry with simple OCR if detailed OCR fails
    //         if ($response->status() === 503 || $response->status() === 500) {
    //             sleep(5); // Wait for model to load
    //             return $this->processImageSimple($imagePath);
    //         }

    //         Log::error('OCR API error: ' . $response->body());
    //         return null;

    //     } catch (\Exception $e) {
    //         Log::error('OCR processing error: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    // /**
    //  * Simple OCR fallback
    //  */
    // private function processImageSimple(string $imagePath): ?string
    // {
    //     try {
    //         $fullPath = Storage::path($imagePath);
    //         $imageData = file_get_contents($fullPath);
    //         $base64Image = base64_encode($imageData);

    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . $this->apiToken,
    //             'Content-Type' => 'application/json',
    //         ])
    //         ->timeout(120)
    //         ->post($this->apiUrl, [
    //             'inputs' => $base64Image,
    //             'parameters' => [
    //                 'task' => '<OCR>', // Simple OCR task
    //             ],
    //             'options' => [
    //                 'wait_for_model' => true,
    //             ]
    //         ]);

    //         if ($response->successful()) {
    //             return $this->extractTextFromResponse($response->json());
    //         }

    //         return null;

    //     } catch (\Exception $e) {
    //         Log::error('Simple OCR error: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    // /**
    //  * Extract text from Florence-2 response
    //  */
    // private function extractTextFromResponse($response): ?string
    // {
    //     if (is_array($response)) {
    //         // Handle different response formats from Florence-2
    //         if (isset($response[0]['generated_text'])) {
    //             return $response[0]['generated_text'];
    //         }

    //         if (isset($response['generated_text'])) {
    //             return $response['generated_text'];
    //         }

    //         // Handle OCR_WITH_REGION response
    //         if (isset($response[0]) && is_array($response[0])) {
    //             $texts = [];
    //             foreach ($response as $item) {
    //                 if (isset($item['text'])) {
    //                     $texts[] = $item['text'];
    //                 }
    //             }
    //             if (!empty($texts)) {
    //                 return implode("\n", $texts);
    //             }
    //         }
    //     }

    //     if (is_string($response)) {
    //         return $response;
    //     }

    //     return null;
    // }

    // /**
    //  * Extract metadata from OCR texts using pattern matching and NLP
    //  */
    // public function extractMetadata(string $frontText, string $backText): array
    // {
    //     $metadata = [];
    //     $combinedText = $frontText . "\n\n" . $backText;

    //     // Extract ISBN (ISBN-10 or ISBN-13)
    //     if (preg_match('/ISBN[\s:-]*(\d{1,5}[-\s]?\d{1,7}[-\s]?\d{1,7}[-\s]?[\dXx])/i', $combinedText, $matches)) {
    //         $metadata['isbn'] = preg_replace('/[^\dXx]/', '', $matches[1]);
    //     }

    //     // Extract ISSN
    //     if (preg_match('/ISSN[\s:-]*(\d{4}[-\s]?\d{3}[\dXx])/i', $combinedText, $matches)) {
    //         $metadata['issn'] = preg_replace('/[^\dXx]/', '', $matches[1]);
    //     }

    //     // Extract year (1800-current year)
    //     $currentYear = (int) date('Y');
    //     if (preg_match('/\b(1[89]\d{2}|20[0-2]\d)\b/', $combinedText, $matches)) {
    //         $year = (int) $matches[1];
    //         if ($year >= 1800 && $year <= $currentYear) {
    //             $metadata['tahun_terbit'] = $year;
    //         }
    //     }

    //     // Extract page count
    //     if (preg_match('/(\d{1,4})\s*(hal|halaman|pages?|pg|pp)/i', $combinedText, $matches)) {
    //         $metadata['jumlah_halaman'] = (int) $matches[1];
    //     }

    //     // Extract dimensions/size (e.g., "21 x 14 cm" or "21x14cm")
    //     if (preg_match('/(\d{1,3}\s*[xX×]\s*\d{1,3}\s*(?:cm|mm)?)/i', $combinedText, $matches)) {
    //         $metadata['ukuran'] = trim($matches[1]);
    //     }

    //     // Extract edition
    //     if (preg_match('/(edisi|edition|ed\.?)\s*:?\s*([^\n]+)/i', $combinedText, $matches)) {
    //         $metadata['edisi'] = trim($matches[2]);
    //     } elseif (preg_match('/\b(\d{1,2}(?:st|nd|rd|th)\s+(?:edition|ed\.?))/i', $combinedText, $matches)) {
    //         $metadata['edisi'] = trim($matches[1]);
    //     }

    //     // Extract title (usually the first large text or line with caps)
    //     $lines = explode("\n", $frontText);
    //     $lines = array_filter(array_map('trim', $lines));

    //     foreach ($lines as $line) {
    //         // Skip very short lines or lines with common words
    //         if (strlen($line) > 5 && !preg_match('/^(ISBN|ISSN|Penerbit|Publisher|Oleh|By|Author)/i', $line)) {
    //             // Check if line is mostly uppercase or has significant text
    //             if (!isset($metadata['judul']) && strlen($line) > 10) {
    //                 $metadata['judul'] = $this->cleanText($line);
    //                 break;
    //             }
    //         }
    //     }

    //     // Extract author (look for "Oleh:", "By:", "Author:", etc.)
    //     if (preg_match('/(?:oleh|by|author|penulis|pengarang)[\s:]+([^\n]+)/i', $combinedText, $matches)) {
    //         $metadata['pengarang'] = $this->cleanText($matches[1]);
    //     }

    //     // Extract publisher
    //     if (preg_match('/(?:penerbit|publisher|published by)[\s:]+([^\n]+)/i', $combinedText, $matches)) {
    //         $metadata['penerbit'] = $this->cleanText($matches[1]);
    //     }

    //     // Extract synopsis from back cover (usually longer paragraphs)
    //     $backLines = explode("\n", $backText);
    //     $paragraphs = [];
    //     $currentParagraph = '';

    //     foreach ($backLines as $line) {
    //         $line = trim($line);
    //         if (strlen($line) > 50) { // Likely a synopsis line
    //             $currentParagraph .= $line . ' ';
    //         } elseif (!empty($currentParagraph)) {
    //             $paragraphs[] = trim($currentParagraph);
    //             $currentParagraph = '';
    //         }
    //     }

    //     if (!empty($paragraphs)) {
    //         // Take the longest paragraph as synopsis
    //         usort($paragraphs, fn($a, $b) => strlen($b) - strlen($a));
    //         $metadata['sinopsis'] = substr($paragraphs[0], 0, 2000);
    //     }

    //     return array_filter($metadata, function($value) {
    //         return !empty($value);
    //     });
    // }

    // /**
    //  * Clean and normalize extracted text
    //  */
    // private function cleanText(string $text): string
    // {
    //     // Remove extra whitespace
    //     $text = preg_replace('/\s+/', ' ', $text);

    //     // Remove common OCR artifacts
    //     $text = preg_replace('/[^\p{L}\p{N}\s\-.,()&]/u', '', $text);

    //     // Trim and title case if all caps
    //     $text = trim($text);

    //     if ($text === strtoupper($text) && strlen($text) > 5) {
    //         $text = ucwords(strtolower($text));
    //     }

    //     return $text;
    // }

    // /**
    //  * Validate and sanitize metadata
    //  */
    // public function sanitizeMetadata(array $metadata): array
    // {
    //     $sanitized = [];

    //     // String fields with max length
    //     $stringFields = [
    //         'judul' => 255,
    //         'pengarang' => 255,
    //         'penerbit' => 255,
    //         'edisi' => 255,
    //         'isbn' => 20,
    //         'issn' => 20,
    //         'ukuran' => 50,
    //         'sinopsis' => 2000,
    //     ];

    //     foreach ($stringFields as $field => $maxLength) {
    //         if (isset($metadata[$field]) && !empty($metadata[$field])) {
    //             $sanitized[$field] = substr(trim($metadata[$field]), 0, $maxLength);
    //         }
    //     }

    //     // Integer fields
    //     if (isset($metadata['tahun_terbit'])) {
    //         $year = (int) $metadata['tahun_terbit'];
    //         $currentYear = (int) date('Y');
    //         if ($year >= 1800 && $year <= $currentYear) {
    //             $sanitized['tahun_terbit'] = $year;
    //         }
    //     }

    //     if (isset($metadata['jumlah_halaman'])) {
    //         $pages = (int) $metadata['jumlah_halaman'];
    //         if ($pages > 0 && $pages < 10000) {
    //             $sanitized['jumlah_halaman'] = $pages;
    //         }
    //     }

    //     return $sanitized;
    // }
}
