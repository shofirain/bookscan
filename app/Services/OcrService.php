<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    /**
     * Extract metadata from single image
     */
    public function extractMetadata(string $imagePath): array
    {
        return $this->extractMetadataMulti($imagePath, null);
    }

    /**
     * Extract metadata from multiple images (front + back cover)
     */
    public function extractMetadataMulti(string $frontCoverPath, ?string $backCoverPath = null): array
    {
        set_time_limit(900);
        ini_set('max_execution_time', 900);

        if (!file_exists($frontCoverPath)) {
            throw new \Exception('Cover depan tidak ditemukan: ' . $frontCoverPath);
        }

        Log::info('OCR Multi Request', [
            'front' => $frontCoverPath,
            'back' => $backCoverPath,
            'front_size' => filesize($frontCoverPath),
        ]);

        $frontHandle = fopen($frontCoverPath, 'r');

        if (!$frontHandle) {
            throw new \Exception('Tidak dapat membuka cover depan');
        }

        $backHandle = null;
        $multiPart = [];

        try {
            $startTime = microtime(true);

            // Prepare multipart data
            $multiPart[] = [
                'name' => 'front_cover',
                'contents' => $frontHandle,
                'filename' => basename($frontCoverPath)
            ];

            // Add back cover if provided and exists
            if ($backCoverPath && file_exists($backCoverPath)) {
                $backHandle = fopen($backCoverPath, 'r');

                if ($backHandle) {
                    $multiPart[] = [
                        'name' => 'back_cover',
                        'contents' => $backHandle,
                        'filename' => basename($backCoverPath)
                    ];

                    Log::info('Both covers will be processed');
                } else {
                    Log::warning('Failed to open back cover, processing front only');
                }
            } else {
                Log::info('Only front cover will be processed');
            }

            // Send request
            $response = Http::timeout(900)
                ->connectTimeout(60)
                ->attach(
                    'front_cover',
                    fopen($frontCoverPath, 'r'),
                    basename($frontCoverPath)
                );

            if ($backCoverPath && file_exists($backCoverPath)) {
                $response = $response->attach(
                    'back_cover',
                    fopen($backCoverPath, 'r'),
                    basename($backCoverPath)
                );
            }

            $response = $response->post('http://127.0.0.1:8001/ocr/book-multi');

            $duration = round(microtime(true) - $startTime, 2);

            Log::info("OCR completed in {$duration} seconds");

            if (!$response->successful()) {
                $statusCode = $response->status();
                $errorBody = $response->body();

                Log::error('OCR API Error', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                ]);

                throw new \Exception(
                    "OCR gagal (HTTP {$statusCode}): " . substr($errorBody, 0, 200)
                );
            }

            $data = $response->json();

            if (!isset($data['success']) || $data['success'] === false) {
                $errorMsg = $data['error'] ?? 'Unknown error';
                throw new \Exception('OCR Error: ' . $errorMsg);
            }

            Log::info('OCR Success', [
                'title' => $data['data']['title'] ?? null,
                'author' => $data['data']['author'] ?? null,
                'has_data' => !empty($data['data'])
            ]);

            return $data;
        } catch (ConnectionException $e) {
            Log::error('OCR Connection Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('Tidak dapat terhubung ke server OCR: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('OCR Processing Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        } finally {
            // Ensure file handles are closed
            if (is_resource($frontHandle)) {
                fclose($frontHandle);
            }
            if (is_resource($backHandle)) {
                fclose($backHandle);
            }
        }
    }
}
