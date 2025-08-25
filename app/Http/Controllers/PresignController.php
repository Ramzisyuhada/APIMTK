<?php

namespace App\Http\Controllers;
use Aws\S3\S3Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PresignController extends Controller
{
       private function s3()
{
    $region = env('AWS_DEFAULT_REGION', 'us-east-1'); // bucket kamu di us-east-1
    $key    = env('AWS_ACCESS_KEY_ID');
    $secret = env('AWS_SECRET_ACCESS_KEY');

    if (empty($key) || empty($secret)) {
        throw new RuntimeException("AWS creds kosong. Cek .env (AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY).");
    }

    return new S3Client([
        'version'     => 'latest',
        'region'      => $region,
        'credentials' => ['key' => $key, 'secret' => $secret],
    ]);
}

    public function upload(Request $req) {
        $data = $req->validate([
            'key' => 'required|string',
            'contentType' => 'required|string|in:application/pdf',
        ]);

        // wajib .pdf
        if (!preg_match('/\.pdf$/i', $data['key'])) {
            return response()->json(['error' => 'Key must end with .pdf'], 400);
        }

        // sanitize filename
        $base = basename($data['key']);
        $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
        $safeKey = "uploads/pdf/" . $safe;

        $cmd = $this->s3()->getCommand('PutObject', [
            'Bucket' => 'bucketmtk',
            'Key' => $safeKey,
            'ContentType' => 'application/pdf',
        ]);
        $reqUrl = $this->s3()->createPresignedRequest($cmd, '+5 minutes');
        return response()->json([
            'url' => (string)$reqUrl->getUri(),
            'key' => $safeKey
        ]);
    }
     public function download(Request $req)
    {
        $data = $req->validate([
            'key'        => 'required|string',       // contoh: uploads/pdf/namafile.pdf
            'filename'   => 'nullable|string',       // opsional: nama file saat di-save
            'disposition'=> 'nullable|in:inline,attachment', // default attachment
        ]);

        $bucket      = env('AWS_BUCKET', 'bucketmtk');
        $key         = $data['key'];
        $filename    = $data['filename'] ?? basename($key);
        $disposition = $data['disposition'] ?? 'attachment';

        $s3 = $this->s3();

        // Cek objek ada (biar kalau salah key, balas 404, bukan 500)
        try {
            $s3->headObject(['Bucket' => $bucket, 'Key' => $key]);
        } catch (AwsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan di S3: '.$key,
                'aws'     => $e->getAwsErrorMessage(),
            ], 404);
        }

        // Presign GET dengan header respons yang pas agar ter-download sebagai PDF
        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key'    => $key,
            'ResponseContentType'        => 'application/pdf',
            'ResponseContentDisposition' => $disposition.'; filename="'.addslashes($filename).'"',
        ]);

        $request = $s3->createPresignedRequest($cmd, '+5 minutes');

        return response()->json([
            'success'    => true,
            'url'        => (string)$request->getUri(),
            'expires_in' => 300,
        ]);
    }


}
