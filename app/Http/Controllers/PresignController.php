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
        $key = $req->string('key')->toString();
        $contentType = $req->string('contentType', 'application/octet-stream')->toString();

        // Validasi env
        $project = env('GCP_PROJECT_ID');
        $keyFile = env('GCP_KEY_FILE');
        $bucket  = env('GCS_BUCKET');
        $signer  = env('GCS_SIGNING_EMAIL'); // harus sama dengan client_email di JSON

        if (!$project || !$keyFile || !is_readable($keyFile) || !$bucket) {
            throw new \RuntimeException('GCP creds tidak valid. Cek GCP_PROJECT_ID / GCP_KEY_FILE.');
        }

        // Hati2 dengan spasi/plus. Simpan persis nama object yg kamu mau di GCS (tanpa urlencode).
        // Sebaiknya ganti spasi menjadi %20 atau underscore saat MENENTUKAN KEY di sisi client.
        // Yang penting: nama yg disign = nama yang di-request saat PUT (harus identik).
        $headers = [
            'content-type' => $contentType,
            // GCS V4 + unsigned payload
            'x-goog-content-sha256' => 'UNSIGNED-PAYLOAD',
        ];

        $storage = new StorageClient([
            'projectId'   => $project,
            'keyFilePath' => $keyFile,
        ]);

        $bucketObj = $storage->bucket($bucket);
        $object    = $bucketObj->object($key);

        // Expire 5 menit
        $expiresAt = new \DateTimeImmutable('+5 minutes');

        // Buat signed URL V4 PUT
        $url = $object->signedUrl($expiresAt, [
            'version' => 'v4',
            'method'  => 'PUT',
            'headers' => $headers,
        ]);

        return response()->json([
            'url'     => $url,
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
