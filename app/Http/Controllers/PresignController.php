<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;
use RuntimeException;

class PresignController extends Controller
{
     public function upload(Request $req)
    {
        $key = $req->input('key');
        $contentType = $req->input('contentType', 'application/octet-stream');

        // pilih GCS
        return $this->gcs($key, $contentType);
    }

    private function gcs(string $key, string $contentType)
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
        $object    = $bucketObj->object($key);

        // header minimal – paling aman
        $headers = [
            'content-type' => $contentType,
            // kalau mau pakai mode A (UNSIGNED-PAYLOAD), tambahkan:
            // 'x-goog-content-sha256' => 'UNSIGNED-PAYLOAD',
        ];

        $url = $object->signedUrl(new \DateTime('+10 minutes'), [
            'version' => 'v4',
            'method'  => 'PUT',
            'headers' => $headers,
        ]);

        Log::info('PRESIGN', ['key' => $key, 'headers' => $headers, 'url' => $url]);

        return response()->json([
            'url' => $url,
            'headers' => $headers,
        ]);
    }

    // POST /api/presign/download  { key, filename?, disposition?(inline|attachment) }
    public function download(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'filename'    => 'nullable|string',
            'disposition' => 'nullable|in:inline,attachment',
        ]);

        $bucket = $this->gcs()->bucket(env('GCS_BUCKET'));
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
                'responseType'        => 'application/pdf',
                'responseDisposition' => $disposition . '; filename="' . addslashes($filename) . '"',
            ]
        );

        return response()->json([
            'success'    => true,
            'url'        => $url,
            'expires_in' => 300,
        ]);
    }
}
