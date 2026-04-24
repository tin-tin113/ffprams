<?php
use Illuminate\Support\Facades\Http;

$url = 'https://smsapiph.onrender.com/api/v1/send/sms';
$key = 'ca01383c188276fb28ff05f6f4f1d430';
$data = ['recipient' => '09170000000', 'message' => 'test'];

echo "Testing X-API-KEY with 'sk-' prefix...\n";
echo "Response: " . Http::withHeaders(['x-api-key' => 'sk-' . $key])->post($url, $data)->body() . "\n\n";

echo "Testing X-API-KEY with uppercase...\n";
echo "Response: " . Http::withHeaders(['x-api-key' => strtoupper($key)])->post($url, $data)->body() . "\n\n";

echo "Testing X-API-KEY with 'Bearer ' prefix...\n";
echo "Response: " . Http::withHeaders(['x-api-key' => 'Bearer ' . $key])->post($url, $data)->body() . "\n\n";
