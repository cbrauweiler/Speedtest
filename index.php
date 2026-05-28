<?php

declare(strict_types=1);

require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/routes.php';

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Konfiguration fehlt. Bitte 'config.example.php' nach 'config.php' kopieren und anpassen.\n";
    exit;
}
$config = require $configPath;
$GLOBALS['config'] = $config;

// Pfad ohne Base-Path & Query
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
if ($config['base_path'] !== '' && str_starts_with($path, $config['base_path'])) {
    $path = substr($path, strlen($config['base_path'])) ?: '/';
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// View-Routes ohne DB
if ($path === '/' && $method === 'GET') {
    route_index();
    return;
}

// Alles andere braucht die DB
$db = db_open($config['db_path']);

// Pattern: /r/<uuid>
if ($method === 'GET' && preg_match('#^/r/([0-9a-f-]{8,36})$#i', $path, $m)) {
    route_result($db, $m[1]);
    return;
}
if ($method === 'GET' && preg_match('#^/api/results/([0-9a-f-]{8,36})$#i', $path, $m)) {
    api_results_get($db, $m[1]);
    return;
}

switch ("$method $path") {
    case 'GET /history':       route_history($db); return;
    case 'GET /api/ping':      api_ping(); return;
    case 'GET /api/download':  api_download(); return;
    case 'POST /api/upload':   api_upload(); return;
    case 'POST /api/results':  api_results_create($db); return;
}

route_404();
