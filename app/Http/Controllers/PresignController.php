<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;
use RuntimeException;

class PresignController extends Controller
{
    private function gcs(): StorageClient
    {
        $projectId = env('GCP_PROJECT_ID');
        $keyFile   = env('GCP_KEY_FILE');

        if (!$projectId || !$keyFile || !file_exists($keyFile)) {
            throw new RuntimeException('GCP creds tidak valid. Cek GCP_PROJECT_ID / GCP_KEY_FILE.');
        }

        return new StorageClient([
            'projectId'   => $projectId,
            'keyFilePath' => $keyFile,
        ]);
    }

    // POST /api/presign/upload  { key: "nama.pdf", contentType: "application/pdf" }
    public function upload(Request $req)
    {
        $data = $req->validate([
            'key'         => 'required|string',
            'contentType' => 'required|string|in:application/pdf',
        ]);

        if (!preg_match('/\.pdf$/i', $data['key'])) {
            return response()->json(['error' => 'Key must end with .pdf'], 400);
        }

        $base    = basename($data['key']);
        $safe    = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
        $safeKey = 'uploads/pdf/' . $safe;

        $bucket = $this->gcs()->bucket(env('GCS_BUCKET'));
        $object = $bucket->object($safeKey);

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
