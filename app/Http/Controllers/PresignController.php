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



}
