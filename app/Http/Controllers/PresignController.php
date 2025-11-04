<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Storage\StorageClient;
use RuntimeException;

class PresignController extends Controller
{
    public function upload(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'contentType' => 'nullable|string',
        ]);

        $key         = $data['key'];
        $contentType = $data['contentType'] ?? 'application/octet-stream';

        // generate URL upload (PUT) ke GCS
        return $this->presignUploadGcs($key, $contentType);
    }

    // POST /api/presign/download  { key, filename?, disposition?(inline|attachment) }
    public function download(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'filename'    => 'nullable|string',
            'disposition' => 'nullable|in:inline,attachment',
        ]);

        [$storage, $bucket] = $this->makeGcs();
        $object = $bucket->object($data['key']);

        if (!$object->exists()) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan'], 404);
        }

        $filename    = $data['filename'] ?? basename($data['key']);
        $disposition = $data['disposition'] ?? 'attachment';

        $url = $object->signedUrl(
            now()->addMinutes(5)->toDateTime(),
            [
                'version'             => 'v4',
                'method'              => 'GET',
                'responseType'        => 'application/pdf', // optional, sesuaikan
                'responseDisposition' => $disposition . '; filename="' . addslashes($filename) . '"',
            ]
        );

        return response()->json([
            'success'    => true,
            'url'        => $url,
            'expires_in' => 300,
        ]);
    }

    /** ---------- Helpers ---------- */

    private function makeGcs(): array
    {
        $projectId = env('GCP_PROJECT_ID');
        $keyFile   = env('GCP_KEY_FILE');
        $bucket    = env('GCP_BUCKET');

        if (!$projectId || !$keyFile || !$bucket) {
            throw new RuntimeException('GCP creds tidak valid. Cek GCP_PROJECT_ID / GCP_KEY_FILE / GCP_BUCKET.');
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

    private function presignUploadGcs(string $key, string $contentType)
    {
        [$storage, $bucketObj] = $this->makeGcs();
        $object = $bucketObj->object($key);

        // header minimal untuk signed PUT (v4)
        $headers = [
            'content-type' => $contentType,
            // Jika ingin payload tidak di-hash oleh klien (opsional):
            // 'x-goog-content-sha256' => 'UNSIGNED-PAYLOAD',
        ];

        $url = $object->signedUrl(new \DateTime('+10 minutes'), [
            'version' => 'v4',
            'method'  => 'PUT',
            'headers' => $headers,
        ]);

        Log::info('PRESIGN_UPLOAD_GCS', ['key' => $key, 'headers' => $headers]);

        return response()->json([
            'url'     => $url,
            'headers' => $headers,
            'expires' => 600,
        ]);
    }
}
