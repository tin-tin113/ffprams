<?php
use Illuminate\Support\Facades\Http;

$url = 'https://smsapiph.onrender.com/api/v1/send/sms';
$key = 'ca01383c188276fb28ff05f6f4f1d430';
$data = ['recipient' => '09170000000', 'message' => 'test'];

echo "Testing X-API-KEY header...\n";
echo "Response: " . Http::withHeaders(['x-api-key' => $key])->post($url, $data)->body() . "\n\n";

echo "Testing Authorization: Bearer header...\n";
echo "Response: " . Http::withToken($key)->post($url, $data)->body() . "\n\n";

echo "Testing Query Parameter ?token=...\n";
echo "Response: " . Http::post($url . '?token=' . $key, $data)->body() . "\n\n";

echo "Testing Body Parameter 'token'...\n";
echo "Response: " . Http::post($url, array_merge($data, ['token' => $key]))->body() . "\n\n";

echo "Testing Body Parameter 'apikey'...\n";
echo "Response: " . Http::post($url, array_merge($data, ['apikey' => $key]))->body() . "\n\n";
