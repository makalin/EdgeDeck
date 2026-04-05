<?php

declare(strict_types=1);

const EDGEDECK_STORAGE_DIR = __DIR__ . '/../storage';

function edgedeck_apply_api_http_layer(array $config): void
{
    $origin = $config['http']['cors_allow_origin'] ?? '*';

    if (is_string($origin) && $origin !== '') {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');
        $headers = $config['http']['cors_allow_headers'] ?? 'Content-Type, Authorization, X-API-Key';
        header('Access-Control-Allow-Headers: ' . $headers);
        header('Access-Control-Max-Age: 86400');
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $path = edgedeck_request_path();
    $path = preg_replace('#^/api#', '', $path) ?: '/';
    $publicPaths = $config['security']['public_paths'] ?? ['/health'];

    $key = (string) ($config['security']['api_key'] ?? '');
    if ($key === '') {
        return;
    }

    foreach ($publicPaths as $p) {
        if ($path === $p) {
            return;
        }
    }

    $token = edgedeck_extract_api_token();

    if (!hash_equals($key, $token)) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Unauthorized',
        ], 401);
        exit;
    }
}

function edgedeck_extract_api_token(): string
{
    if (!empty($_SERVER['HTTP_X_API_KEY'])) {
        return (string) $_SERVER['HTTP_X_API_KEY'];
    }

    $auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');

    if (preg_match('/Bearer\s+(\S+)/i', $auth, $m)) {
        return $m[1];
    }

    return '';
}

/**
 * @return array<string, mixed>
 */
function edgedeck_public_config(array $config): array
{
    $out = $config;

    if (isset($out['security']['api_key'])) {
        $out['security']['api_key'] = $out['security']['api_key'] !== '' ? '********' : '';
    }

    return $out;
}

function edgedeck_clamp_int(?int $value, int $default, int $min, int $max): int
{
    if ($value === null) {
        return $default;
    }

    return max($min, min($max, $value));
}

/**
 * @return list<array<string, mixed>>
 */
function edgedeck_read_jsonl_tail(string $file, int $limit): array
{
    $path = edgedeck_storage_path($file);

    if (!is_file($path) || $limit <= 0) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);

    if ($lines === false) {
        return [];
    }

    $lines = array_values(array_filter($lines, static fn (string $l): bool => trim($l) !== ''));
    $lines = array_slice($lines, -$limit);
    $out = [];

    foreach ($lines as $line) {
        $decoded = json_decode($line, true);

        if (is_array($decoded)) {
            $out[] = $decoded;
        }
    }

    return $out;
}

function edgedeck_count_text_file_lines(string $file): int
{
    $path = edgedeck_storage_path($file);

    if (!is_file($path)) {
        return 0;
    }

    $handle = fopen($path, 'rb');

    if ($handle === false) {
        return 0;
    }

    $count = 0;

    while (!feof($handle)) {
        $count += substr_count((string) fread($handle, 65536), "\n");
    }

    fclose($handle);

    return $count;
}

function edgedeck_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function edgedeck_read_json_input(): array
{
    $raw = file_get_contents('php://input');

    if (($raw === false || $raw === '') && PHP_SAPI === 'cli') {
        $raw = stream_get_contents(STDIN);
    }

    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        edgedeck_json_response([
            'ok' => false,
            'error' => 'Invalid JSON payload',
        ], 400);
        exit;
    }

    return $decoded;
}

function edgedeck_storage_path(string $file): string
{
    if (!is_dir(EDGEDECK_STORAGE_DIR)) {
        mkdir(EDGEDECK_STORAGE_DIR, 0775, true);
    }

    return EDGEDECK_STORAGE_DIR . '/' . $file;
}

function edgedeck_read_json_file(string $file, array $fallback = []): array
{
    $path = edgedeck_storage_path($file);

    if (!is_file($path)) {
        file_put_contents($path, json_encode($fallback, JSON_PRETTY_PRINT));
        return $fallback;
    }

    $contents = file_get_contents($path);
    $decoded = json_decode($contents ?: '', true);

    return is_array($decoded) ? $decoded : $fallback;
}

function edgedeck_write_json_file(string $file, array $payload): void
{
    $path = edgedeck_storage_path($file);
    file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function edgedeck_append_json_record(string $file, array $record): void
{
    $path = edgedeck_storage_path($file);
    file_put_contents(
        $path,
        json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

function edgedeck_request_path(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}
