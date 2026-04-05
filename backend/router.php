<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

if (str_starts_with($uri, '/api')) {
    require __DIR__ . '/api/index.php';
    return true;
}

if (str_starts_with($uri, '/admin')) {
    require __DIR__ . '/admin/index.php';
    return true;
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'ok' => false,
    'error' => 'Not found',
    'path' => $uri,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
