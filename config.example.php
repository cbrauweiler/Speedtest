<?php

// Konfigurationstemplate. Vor dem ersten Start nach 'config.php' kopieren
// und an die eigene Umgebung anpassen. 'config.php' ist in .gitignore und
// wird nicht committet.

declare(strict_types=1);

return [
    // Anzeigename im Header und Footer.
    'app_name'      => 'Speedtest',

    // Versionsnummer (erscheint im Footer).
    'app_version'   => '0.1.0',

    // Link zum Quellcode-Repo (Footer-Button). Leer lassen, um den Button
    // auszublenden.
    'repo_url'      => '',

    // Pfad zur SQLite-Datei. Wird beim ersten Start automatisch erzeugt;
    // das Verzeichnis braucht Schreibrechte.
    'db_path'       => __DIR__ . '/data/speedtest.sqlite',

    // Verzeichnis für optionale Static-Blobs (für >1 Gbit/s-Setups, in denen
    // der PHP-Streaming-Pfad zum Bottleneck wird). Wird von bin/seed.php
    // befüllt.
    'blob_dir'      => __DIR__ . '/blob',

    // Erlaubte Download-Größen in MB (Whitelist für /api/download?size=).
    // Werte, die nicht in dieser Liste stehen, werden mit 400 abgelehnt.
    'allowed_sizes' => [2, 5, 10, 25, 50, 100],

    // Maximale Anzahl Einträge, die unter /history angezeigt werden.
    'history_limit' => 100,

    // Salt für die IP-Anonymisierung (SHA-256-Hash der Client-IP wird
    // gespeichert, nicht die IP selbst). **Unbedingt durch einen langen
    // Zufallswert ersetzen**, z.B. erzeugt mit:
    //   php -r "echo bin2hex(random_bytes(16));"
    'ip_hash_salt'  => 'CHANGE-ME-RANDOM-STRING',

    // URL-Präfix, falls die App nicht im DocumentRoot des Hosts liegt.
    // Leer lassen, wenn die App unter '/' erreichbar ist.
    // Beispiel: '/tools/speedtest' (ohne Slash am Ende).
    'base_path'     => '',
];
