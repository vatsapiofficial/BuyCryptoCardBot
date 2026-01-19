<?php
require_once 'config.php';

$webhook_url = $app_url;
$api_url = "https://api.telegram.org/bot$token/setWebhook?url=$webhook_url";

echo "Setting webhook to: $webhook_url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($response) {
    echo "Response from Telegram: " . $response . "\n";
} else {
    echo "Error: " . $error . "\n";
}
