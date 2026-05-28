<?php

declare(strict_types=1);

// Optional: erzeugt Static-Blobs in /blob/, damit Apache sie direkt liefern kann
// (statt PHP via /api/download). Nur sinnvoll, wenn der PHP-Streaming-Pfad
// messtechnisch zu langsam ist (Gigabit+ Setups). Andernfalls ist nichts zu tun.

require __DIR__ . '/../lib/db.php';
$config = require __DIR__ . '/../config.php';

echo "Init SQLite at {$config['db_path']}\n";
db_open($config['db_path']);
echo "  Schema OK\n";

if (!is_dir($config['blob_dir'])) {
    mkdir($config['blob_dir'], 0775, true);
}

foreach ($config['allowed_sizes'] as $mb) {
    $file = $config['blob_dir'] . '/blob-' . $mb . 'mb.bin';
    $bytes = $mb * 1024 * 1024;
    if (is_file($file) && filesize($file) === $bytes) {
        echo "  blob-{$mb}mb.bin already exists, skipping\n";
        continue;
    }
    echo "  Generating blob-{$mb}mb.bin ... ";
    $fh = fopen($file, 'wb');
    if ($fh === false) {
        throw new RuntimeException("Cannot open {$file} for writing");
    }
    $chunkSize = 256 * 1024;
    $chunks = $bytes / $chunkSize;
    for ($i = 0; $i < $chunks; $i++) {
        fwrite($fh, random_bytes($chunkSize));
    }
    fclose($fh);
    echo "done\n";
}

echo "Seed complete.\n";
