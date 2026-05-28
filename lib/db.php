<?php

declare(strict_types=1);

function db_open(string $path): PDO
{
    $isNew = !file_exists($path);
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA synchronous = NORMAL');
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNew) {
        db_migrate($pdo);
    }

    return $pdo;
}

function db_migrate(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS results (
            id            TEXT PRIMARY KEY,
            created_at    INTEGER NOT NULL,
            client_hash   TEXT,
            user_agent    TEXT,
            label         TEXT,
            ping_ms       REAL,
            jitter_ms     REAL,
            down_mbps     REAL,
            up_mbps       REAL,
            down_bytes    INTEGER,
            up_bytes      INTEGER,
            down_ms       INTEGER,
            up_ms         INTEGER,
            raw_json      TEXT
        );
    SQL);

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_results_created_at ON results(created_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_results_client_hash ON results(client_hash)');
}
