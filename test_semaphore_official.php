<?php
use Illuminate\Support\Facades\Http;

$url = 'https://api.semaphore.co/api/v4/messages';
$key = 'ca01383c188276fb28ff05f6f4f1d430';
$data = [
    'apikey' => $key,
    'number' => '09170000000',
    'message' => 'test',
    'sendername' => 'FFPRAMS'
];

echo "Testing Official Semaphore API...\n";
$response = Http::post($url, $data);
echo "Status: " . $response->status() . "\n";
echo "Response: " . $response->body() . "\n\n";
