<?php

declare(strict_types=1);

use App\Response;
use App\Request;

$router->get('/docs', function (Request $request) {
    $indexFile = __DIR__ . '/../../docs/index.html';
    if (!is_file($indexFile)) {
        (new Response())->error('Documentation not found', 404);
        return;
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo file_get_contents($indexFile);
})->register();

$router->get('/docs/(.*)', function (Request $request, string $path) {
    $base = realpath(__DIR__ . '/../../docs');
    $dest = realpath($base . '/' . ltrim($path, '/'));

    if (!$dest || !str_starts_with($dest, $base) || !is_file($dest)) {
        (new Response())->error('Not Found', 404);
        return;
    }

    $ext = strtolower(pathinfo($dest, PATHINFO_EXTENSION));
    $mimeTypes = [
        'html' => 'text/html; charset=UTF-8',
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];

    header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
    echo file_get_contents($dest);
})->register();
