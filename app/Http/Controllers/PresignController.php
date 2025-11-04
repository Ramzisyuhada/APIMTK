<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;
use RuntimeException;
use Aws\Exception\AwsException;   // <-- TAMBAHKAN
use RuntimeException;             // <-- TAMBAHKAN

class PresignController extends Controller
{
    private function gcs()
    {
        $projectId = env('GCP_PROJECT_ID');
        $keyFile   = env('GCP_KEY_FILE');

        if (empty($projectId) || empty($keyFile) || !file_exists($keyFile)) {
            throw new RuntimeException("GCP creds kosong atau tidak valid. Cek .env (GCP_PROJECT_ID / GCP_KEY_FILE).");
        }

        return new StorageClient([
            'projectId'   => $projectId,
            'keyFilePath' => $keyFile,
        ]);
    }

    public function upload(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'contentType' => 'required|string|in:application/pdf',
        ]);

        // wajib .pdf
        if (!preg_match('/\.pdf$/i', $data['key'])) {
            return response()->json(['error' => 'Key must end with .pdf'], 400);
        }

        // sanitize nama file
        $base    = basename($data['key']);
        $safe    = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
        $safeKey = "uploads/pdf/" . $safe;

        $bucketName = env('GCS_BUCKET');
        $storage    = $this->gcs();
        $bucket     = $storage->bucket($bucketName);
        $object     = $bucket->object($safeKey);

        // Buat signed URL untuk upload (PUT)
        $url = $object->signedUrl(
            now()->addMinutes(5)->toDateTime(),
            [
                'version'     => 'v4',
                'method'      => 'PUT',
                'contentType' => 'application/pdf',
            ]
        );

        return response()->json([
            'url'        => $url,
            'key'        => $safeKey,
            'method'     => 'PUT',
            'headers'    => ['Content-Type' => 'application/pdf'],
            'expires_in' => 300,
        ]);
    }

    public function download(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',        // contoh: uploads/pdf/namafile.pdf
            'filename'    => 'nullable|string',        // opsional: nama file saat di-save
            'disposition' => 'nullable|in:inline,attachment', // default attachment
        ]);

        $bucketName  = env('GCS_BUCKET');
        $key         = $data['key'];
        $filename    = $data['filename'] ?? basename($key);
        $disposition = $data['disposition'] ?? 'attachment';

        $storage = $this->gcs();
        $bucket  = $storage->bucket($bucketName);
        $object  = $bucket->object($key);

        // Cek apakah file ada
        if (!$object->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan di GCS: ' . $key,
            ], 404);
        }

        // Signed URL untuk download (GET)
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
