<?php
session_start();

define('API_BASE', 'http://localhost:8888/api');

function apiRequest(string $method, string $endpoint, ?array $body = null, bool $withAuth = true): array
{
    $url = API_BASE . $endpoint;
    $ch = curl_init($url);

    $headers = [
        'Content-Type: application/json'
    ];

    if ($withAuth && !empty($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers
    ];

    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    return [
        'status' => $httpCode,
        'data' => is_array($decoded) ? $decoded : [],
        'raw' => $response
    ];
}