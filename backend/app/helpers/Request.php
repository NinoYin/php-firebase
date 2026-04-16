<?php

class Request
{
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }

    public static function body(): array
    {
        $content = file_get_contents('php://input');
        if (!$content) {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function header(string $key): ?string
    {
        $headers = getallheaders();
        foreach ($headers as $headerKey => $value) {
            if (strtolower($headerKey) === strtolower($key)) {
                return $value;
            }
        }
        return null;
    }

    public static function bearerToken(): ?string
    {
        $auth = self::header('Authorization');
        if (!$auth) return null;

        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}