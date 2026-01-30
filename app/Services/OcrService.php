<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    public function extractMetadata(string $imagePath): array
    {
        // Validasi file
        if (!file_exists($imagePath)) {
            throw new \Exception('File tidak ditemukan: ' . $imagePath);
        }

        $fileHandle = fopen($imagePath, 'r');
        
        if (!$fileHandle) {
            throw new \Exception('Tidak dapat membuka file: ' . $imagePath);
        }

        try {
            $response = Http::timeout(300)
                ->attach(
                    'file',
                    $fileHandle,
                    basename($imagePath)
                )
                ->post('http://127.0.0.1:8001/ocr/book');

            if (!$response->successful()) {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                Log::error('OCR API Error', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                    'file' => $imagePath
                ]);
                
                throw new \Exception(
                    "OCR gagal (HTTP {$statusCode}): {$errorBody}"
                );
            }
            
            $data = $response->json();
            
            if (empty($data)) {
                throw new \Exception('OCR tidak mengembalikan data yang valid');
            }
            
            return $data;
            
        } catch (ConnectionException $e) {
            throw new \Exception('Tidak dapat terhubung ke server OCR. Pastikan server berjalan di http://127.0.0.1:8001');
        } finally {
            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }
        }
    }
}