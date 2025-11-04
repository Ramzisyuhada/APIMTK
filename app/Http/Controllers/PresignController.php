<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Storage\StorageClient;
use RuntimeException;
use Throwable;

class PresignController extends Controller
{
    /**
     * POST /api/presign/upload
     * Body: { "key": "uploads/test.pdf", "contentType": "application/pdf" }
     */
    public function upload(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'contentType' => 'nullable|string',
        ]);

        // Normalisasi & sanitasi key agar stabil untuk signing dan aman di URL
        $key = $this->sanitizeKey($data['key']);
        $contentType = $data['contentType'] ?? $this->guessContentTypeFromExt($key) ?? 'application/octet-stream';

        try {
            return $this->presignUploadGcs($key, $contentType, 600); // 10 menit
        } catch (Throwable $e) {
            Log::error('PRESIGN_UPLOAD_GCS_ERROR', [
                'key' => $key,
                'contentType' => $contentType,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat URL upload',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/presign/download
     * Body: { "key": "uploads/test.pdf", "filename"?: "my.pdf", "disposition"?: "inline|attachment" }
     */
    public function download(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'filename'    => 'nullable|string',
            'disposition' => 'nullable|in:inline,attachment',
        ]);

        $key         = $this->sanitizeKey($data['key']);
        $filename    = $data['filename'] ?? basename($key);
        $disposition = $data['disposition'] ?? 'attachment';

        try {
            [$storage, $bucket] = $this->makeGcs();
            $object = $bucket->object($key);

            if (!$object->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                ], 404);
            }

            // Coba tebak content-type dari ekstensi, fallback PDF agar aman
            $responseType = $this->guessContentTypeFromExt($filename) ?? 'application/pdf';

            $url = $object->signedUrl(
                now()->addMinutes(5)->toDateTime(), // 5 menit
                [
                    'version'             => 'v4',
                    'method'              => 'GET',
                    'responseType'        => $responseType,
                    'responseDisposition' => $disposition . '; filename="' . addslashes($filename) . '"',
                ]
            );

            return response()->json([
                'success'    => true,
                'url'        => $url,
                'expires_in' => 300,
                'key'        => $key,
            ]);
        } catch (Throwable $e) {
            Log::error('PRESIGN_DOWNLOAD_GCS_ERROR', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat URL download',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* ====================== Helpers ====================== */

    /**
     * Inisialisasi StorageClient + Bucket.
     * Env yang dipakai:
     * - GOOGLE_CLOUD_PROJECT
     * - GOOGLE_APPLICATION_CREDENTIALS
     * - GOOGLE_CLOUD_STORAGE_BUCKET
     */
    private function makeGcs(): array
    {
        $projectId = env('GOOGLE_CLOUD_PROJECT') ?: env('GCP_PROJECT_ID');
        $keyFile   = env('GOOGLE_APPLICATION_CREDENTIALS') ?: env('GCP_KEY_FILE');
        $bucket    = env('GOOGLE_CLOUD_STORAGE_BUCKET') ?: env('GCP_BUCKET');

        if (!$projectId || !$keyFile || !$bucket) {
            throw new RuntimeException('GCP creds tidak valid. Cek GOOGLE_CLOUD_PROJECT / GOOGLE_APPLICATION_CREDENTIALS / GOOGLE_CLOUD_STORAGE_BUCKET.');
        }
        if (!is_readable($keyFile)) {
            throw new RuntimeException("GCP key tidak bisa dibaca: $keyFile");
        }

        $storage = new StorageClient([
            'projectId'   => $projectId,
            'keyFilePath' => $keyFile,
        ]);

        $bucketObj = $storage->bucket($bucket);
        if (!$bucketObj->exists()) {
            throw new RuntimeException("GCS bucket tidak ditemukan atau tidak terakses: $bucket");
        }

        return [$storage, $bucketObj];
    }

    /**
     * Membuat signed URL v4 untuk PUT upload.
     */
    private function presignUploadGcs(string $key, string $contentType, int $expiresSec)
    {
        [$storage, $bucket] = $this->makeGcs();
        $object = $bucket->object($key);

        // Header yang HARUS konsisten saat client melakukan PUT
        $headers = [
            'Content-Type' => $contentType, // gunakan kapitalisasi standar
            // Jika ingin tidak menghitung hash payload di klien:
            // 'x-goog-content-sha256' => 'UNSIGNED-PAYLOAD',
        ];

        $url = $object->signedUrl(
            now()->addSeconds($expiresSec)->toDateTime(),
            [
                'version' => 'v4',
                'method'  => 'PUT',
                'headers' => $headers,
            ]
        );

        Log::info('PRESIGN_UPLOAD_GCS', ['key' => $key, 'headers' => $headers]);

        return response()->json([
            'success' => true,
            'url'     => $url,
            'method'  => 'PUT',       // <-- tambahkan agar client mudah validasi
            'headers' => $headers,    // <-- kunci header kapital
            'expires' => $expiresSec,
            'key'     => $key,
        ]);
    }

    /**
     * Sanitasi key/path agar stabil dan aman:
     * - Normalisasi separator
     * - Bersihkan karakter berisiko pada filename
     */
    private function sanitizeKey(string $key): string
    {
        $key = trim($key);
        $key = str_replace('\\', '/', $key);
        $key = ltrim($key, '/'); // jangan leading slash

        $dir = trim(dirname($key), '.\\/'); // "uploads"
        $ext = pathinfo($key, PATHINFO_EXTENSION);
        $base = pathinfo($key, PATHINFO_FILENAME);

        // Ubah spasi, +, koma, dll jadi '-'
        $base = preg_replace('/[^\p{L}\p{N}\.\-_]+/u', '-', $base);
        $base = trim($base, '-_');

        $clean = ($dir ? $dir.'/' : '') . ($base !== '' ? $base : 'file');
        if ($ext !== '') {
            $clean .= '.'.$ext;
        }

        return $clean;
    }

    /**
     * Tebak Content-Type dari ekstensi file (sederhana).
     */
    private function guessContentTypeFromExt(string $key): ?string
    {
        $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            'txt'  => 'text/plain',
            'csv'  => 'text/csv',
            'json' => 'application/json',
            'zip'  => 'application/zip',
            'mp4'  => 'video/mp4',
            default => null,
        };
    }
}
