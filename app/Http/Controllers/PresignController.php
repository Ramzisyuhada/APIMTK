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
   // use Google\Cloud\Storage\StorageClient;

public function upload(Request $req)
{
    $data = $req->validate([
        'key'         => 'required|string',
        'contentType' => 'required|string|in:application/pdf',
    ]);

    // amankan nama file
    $base    = basename($data['key']);
    $safe    = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
    $safeKey = 'uploads/pdf/' . $safe;

    $storage = $this->gcs(); // method kamu sendiri
    $bucket  = $storage->bucket(env('GCS_BUCKET'));
    $object  = $bucket->object($safeKey);

    // pakai UTC biar aman
    $expires = (new \DateTime('now', new \DateTimeZone('UTC')))
                ->add(new \DateInterval('PT5M'));

    // SIGN HANYA HEADER2 berikut (lowercase):
    $url = $object->signedUrl($expires, [
        'version' => 'v4',
        'method'  => 'PUT',
        'headers' => [
            'content-type'          => 'application/pdf',
            'x-goog-content-sha256' => 'UNSIGNED-PAYLOAD',
        ],
    ]);

    return response()->json([
        'url'        => $url,
        'key'        => $safeKey,
        'method'     => 'PUT',
        'headers'    => [
            'Content-Type'          => 'application/pdf',
            'x-goog-content-sha256' => 'UNSIGNED-PAYLOAD',
        ],
        'expires_in' => 300,
    ]);
}


return response()->json([
    'url'        => $url,
    'key'        => $safeKey,
    'method'     => 'PUT',
    'headers'    => [ // untuk info ke klien
        'Content-Type'          => 'application/pdf',
        'x-goog-content-sha256' => 'UNSIGNED-PAYLOAD',
    ],
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
