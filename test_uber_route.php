<?php
// Simple test to verify Uber Eats endpoint

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://posfagotto.cl/api/uber-eats/auth/callback?code=test&state=test");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Headers: " . $headers . "\n";
echo "Body: " . $body . "\n";
if ($error) {
    echo "Error: " . $error . "\n";
}
