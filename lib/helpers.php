<?php

declare(strict_types=1);

function uuid_v4(): string
{
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    $hex = bin2hex($b);
    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function render(string $view, array $data = []): void
{
    $config = $GLOBALS['config'];
    extract($data, EXTR_SKIP);
    // View und Layout teilen sich denselben Scope, damit Views
    // Variablen wie $title oder $withSpeedtestJs ans Layout reichen koennen.
    ob_start();
    require __DIR__ . '/../views/' . $view . '.php';
    $__content = ob_get_clean();
    require __DIR__ . '/../views/layout.php';
}

function json_response(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function num_or_null(array $a, string $k): ?float
{
    return isset($a[$k]) && is_numeric($a[$k]) ? (float) $a[$k] : null;
}

function int_or_null(array $a, string $k): ?int
{
    return isset($a[$k]) && is_numeric($a[$k]) ? (int) $a[$k] : null;
}

function client_hash(string $ip, string $salt): ?string
{
    if ($ip === '') {
        return null;
    }
    return substr(hash('sha256', $ip . '|' . $salt), 0, 16);
}

function url(string $path): string
{
    return ($GLOBALS['config']['base_path'] ?? '') . $path;
}

function format_mbps(?float $mbps): string
{
    return $mbps === null ? '—' : number_format($mbps, 1, ',', '.');
}

function format_ms(?float $ms, int $digits = 1): string
{
    return $ms === null ? '—' : number_format($ms, $digits, ',', '.');
}
