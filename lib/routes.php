<?php

declare(strict_types=1);

function route_index(): void
{
    render('index');
}

function route_history(PDO $db): void
{
    $cfg = $GLOBALS['config'];
    $stmt = $db->prepare(
        'SELECT id, created_at, label, ping_ms, jitter_ms, down_mbps, up_mbps
         FROM results ORDER BY created_at DESC LIMIT :limit'
    );
    $stmt->bindValue(':limit', $cfg['history_limit'], PDO::PARAM_INT);
    $stmt->execute();
    render('history', ['rows' => $stmt->fetchAll()]);
}

function route_result(PDO $db, string $id): void
{
    $stmt = $db->prepare('SELECT * FROM results WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        render('result', ['notFound' => true]);
        return;
    }
    $row['raw'] = $row['raw_json'] ? json_decode((string)$row['raw_json'], true) : null;
    render('result', ['r' => $row]);
}

function route_404(): void
{
    http_response_code(404);
    render('not_found');
}

function api_ping(): void
{
    json_response(['t' => (int) (microtime(true) * 1000)]);
}

function api_download(): void
{
    $cfg = $GLOBALS['config'];
    $sizeMb = (int) ($_GET['size'] ?? 10);
    if (!in_array($sizeMb, $cfg['allowed_sizes'], true)) {
        json_response(['error' => 'invalid_size', 'allowed' => $cfg['allowed_sizes']], 400);
        return;
    }

    // Bevorzugt Static-Blob (Apache wäre schneller, aber wenn wir hier sind, schicken wir's selbst).
    $blob = $cfg['blob_dir'] . '/blob-' . $sizeMb . 'mb.bin';
    $total = $sizeMb * 1024 * 1024;

    @set_time_limit(0);
    @ini_set('zlib.output_compression', '0');
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . $total);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Content-Encoding: identity');
    header('X-Accel-Buffering: no');

    if (is_file($blob) && filesize($blob) === $total) {
        readfile($blob);
        return;
    }

    // PHP-streamed Random-Bytes. Wir wiederholen einen einmal erzeugten 256KB-Chunk;
    // mit Content-Encoding: identity wird nichts komprimiert, also reicht das messtechnisch.
    $chunkSize = 256 * 1024;
    $chunk = random_bytes($chunkSize);
    $sent = 0;
    while ($sent < $total) {
        $remaining = $total - $sent;
        if ($remaining < $chunkSize) {
            echo substr($chunk, 0, $remaining);
            return;
        }
        echo $chunk;
        $sent += $chunkSize;
    }
}

function api_upload(): void
{
    $start = microtime(true);
    $bytes = 0;
    $stream = fopen('php://input', 'rb');
    if ($stream === false) {
        json_response(['error' => 'no_body'], 400);
        return;
    }
    while (!feof($stream)) {
        $buf = fread($stream, 64 * 1024);
        if ($buf === false || $buf === '') {
            break;
        }
        $bytes += strlen($buf);
    }
    fclose($stream);
    $ms = (int) round((microtime(true) - $start) * 1000);
    json_response(['bytes' => $bytes, 'ms' => $ms]);
}

function api_results_create(PDO $db): void
{
    $cfg = $GLOBALS['config'];
    $raw = file_get_contents('php://input') ?: '';
    $body = json_decode($raw, true);
    if (!is_array($body)) {
        json_response(['error' => 'invalid_body'], 400);
        return;
    }

    $id = uuid_v4();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

    $stmt = $db->prepare(
        'INSERT INTO results
            (id, created_at, client_hash, user_agent, label,
             ping_ms, jitter_ms, down_mbps, up_mbps,
             down_bytes, up_bytes, down_ms, up_ms, raw_json)
         VALUES
            (:id, :created_at, :client_hash, :user_agent, :label,
             :ping_ms, :jitter_ms, :down_mbps, :up_mbps,
             :down_bytes, :up_bytes, :down_ms, :up_ms, :raw_json)'
    );
    $stmt->execute([
        ':id'          => $id,
        ':created_at'  => time(),
        ':client_hash' => client_hash($ip, $cfg['ip_hash_salt']),
        ':user_agent'  => substr($ua, 0, 255),
        ':label'       => isset($body['label']) ? substr((string)$body['label'], 0, 80) : null,
        ':ping_ms'     => num_or_null($body, 'ping_ms'),
        ':jitter_ms'   => num_or_null($body, 'jitter_ms'),
        ':down_mbps'   => num_or_null($body, 'down_mbps'),
        ':up_mbps'     => num_or_null($body, 'up_mbps'),
        ':down_bytes'  => int_or_null($body, 'down_bytes'),
        ':up_bytes'    => int_or_null($body, 'up_bytes'),
        ':down_ms'     => int_or_null($body, 'down_ms'),
        ':up_ms'       => int_or_null($body, 'up_ms'),
        ':raw_json'    => isset($body['raw']) ? json_encode($body['raw']) : null,
    ]);

    json_response(['id' => $id, 'url' => url('/r/' . $id)], 201);
}

function api_results_get(PDO $db, string $id): void
{
    $stmt = $db->prepare('SELECT * FROM results WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        json_response(['error' => 'not_found'], 404);
        return;
    }
    $row['raw'] = $row['raw_json'] ? json_decode((string)$row['raw_json'], true) : null;
    unset($row['raw_json']);
    json_response($row);
}
