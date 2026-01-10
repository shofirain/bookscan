<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Log;

class BookMetadataService
{
    protected $ocrService;
    protected $geminiService;

    public function __construct(OcrService $ocrService, GeminiService $geminiService)
    {
        $this->ocrService = $ocrService;
        $this->geminiService = $geminiService;
    }

    public function processBook(Book $book): bool
    {
        try {
            $book->update(['status' => 'processing']);

            // Step 1: OCR pada cover depan
            $frontOcr = $this->ocrService->extractText(storage_path('app/public/' . $book->front_cover));
            if (!$frontOcr['success']) {
                throw new \Exception('Front cover OCR failed: ' . $frontOcr['error']);
            }

            // Step 2: OCR pada cover belakang
            $backOcr = $this->ocrService->extractText(storage_path('app/public/' . $book->back_cover));
            if (!$backOcr['success']) {
                throw new \Exception('Back cover OCR failed: ' . $backOcr['error']);
            }

            // Simpan hasil OCR
            $book->update([
                'ocr_front_text' => $frontOcr['text'],
                'ocr_back_text' => $backOcr['text']
            ]);

            // Step 3: Ekstrak metadata dengan Gemini AI
            $metadata = $this->geminiService->extractMetadata(
                $frontOcr['text'],
                $backOcr['text']
            );

            if (!$metadata['success']) {
                throw new \Exception('Metadata extraction failed: ' . $metadata['error']);
            }

            // Step 4: Update book dengan metadata
            $book->update([
                'title' => $metadata['metadata']['title'] ?? null,
                'author' => $metadata['metadata']['author'] ?? null,
                'publisher' => $metadata['metadata']['publisher'] ?? null,
                'isbn' => $metadata['metadata']['isbn'] ?? null,
                'publication_year' => $metadata['metadata']['publication_year'] ?? null,
                'description' => $metadata['metadata']['description'] ?? null,
                'category' => $metadata['metadata']['category'] ?? null,
                'price' => $metadata['metadata']['price'] ?? null,
                'raw_metadata' => $metadata['metadata'],
                'status' => 'completed'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Book Processing Error: ' . $e->getMessage());
            $book->update(['status' => 'failed']);
            return false;
        }
    }
}