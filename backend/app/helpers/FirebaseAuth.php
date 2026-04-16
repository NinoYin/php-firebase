<?php

class FirebaseAuth
{
    public static function getAccessToken(array $firebaseConfig): string
    {
        $now = time();

        $jwtHeader = self::base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));

        $jwtClaimSet = self::base64UrlEncode(json_encode([
            'iss' => $firebaseConfig['client_email'],
            'scope' => 'https://www.googleapis.com/auth/datastore',
            'aud' => $firebaseConfig['token_uri'],
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        $unsignedJwt = $jwtHeader . '.' . $jwtClaimSet;

        $signature = '';
        openssl_sign($unsignedJwt, $signature, $firebaseConfig['private_key'], 'sha256WithRSAEncryption');

        $signedJwt = $unsignedJwt . '.' . self::base64UrlEncode($signature);

        $postFields = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $signedJwt
        ]);

        $ch = curl_init($firebaseConfig['token_uri']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new Exception('No fue posible obtener access token de Firebase');
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode >= 400 || empty($decoded['access_token'])) {
            throw new Exception('Error al autenticar con Firebase: ' . $response);
        }

        return $decoded['access_token'];
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}