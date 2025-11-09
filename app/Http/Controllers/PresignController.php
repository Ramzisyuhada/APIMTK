<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Storage\StorageClient;
use RuntimeException;
use Throwable;

class PresignController extends Controller
{
    public function upload(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'contentType' => 'nullable|string',
        ]);

        $key = $this->sanitizeKey($data['key']);
        $contentType = $data['contentType'] ?? $this->guessContentTypeFromExt($key) ?? 'application/octet-stream';

        try {
            return $this->presignUploadGcs($key, $contentType, 600);
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

            $responseType = $this->guessContentTypeFromExt($filename) ?? 'application/pdf';

            $url = $object->signedUrl(
                now()->addMinutes(5)->toDateTime(),
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

    /** ===================== Helpers ===================== */

    private function makeGcs(): array
    {
        $projectId = config('filesystems.disks.gcs.project_id');
        $bucket    = config('filesystems.disks.gcs.bucket');
        $keyPath   = config('filesystems.disks.gcs.key_file_path'); // <- dari ENV

        if (!$projectId || !$bucket || !$keyPath) {
            throw new RuntimeException('Konfigurasi GCS kurang: project_id/bucket/key_file_path kosong.');
        }
        if (!is_readable($keyPath)) {
            throw new RuntimeException("File kredensial tidak bisa dibaca: {$keyPath}");
        }

        $storage = new StorageClient([
            'projectId'   => $projectId,
            'keyFilePath' => $keyPath,
        ]);

        $bucketObj = $storage->bucket($bucket);
        // Panggilan ada auth; jika kredensial invalid, akan lempar exception di operasi berikut
        if (!$bucketObj->exists()) {
            throw new RuntimeException("Bucket tidak ditemukan / tidak terakses: {$bucket}");
        }

        return [$storage, $bucketObj];
    }

    private function presignUploadGcs(string $key, string $contentType, int $expiresSec)
    {
        [$storage, $bucket] = $this->makeGcs();
        $object = $bucket->object($key);

        $headers = [
            'Content-Type' => $contentType,
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
            'method'  => 'PUT',
            'headers' => $headers,
            'expires' => $expiresSec,
            'key'     => $key,
        ]);
    }

    private function sanitizeKey(string $key): string
    {
        $key = trim($key);
        $key = str_replace('\\', '/', $key);
        $key = ltrim($key, '/');

        $dir  = trim(dirname($key), '.\\/');
        $ext  = pathinfo($key, PATHINFO_EXTENSION);
        $base = pathinfo($key, PATHINFO_FILENAME);

        $base = preg_replace('/[^\p{L}\p{N}\.\-_]+/u', '-', $base);
        $base = trim($base, '-_');

        $clean = ($dir ? $dir.'/' : '') . ($base !== '' ? $base : 'file');
        if ($ext !== '') $clean .= '.'.$ext;

        return $clean;
    }

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
