<?php

// Lokale Overrides. Diese Datei zu 'config.local.php' kopieren und anpassen.
// Wird automatisch geladen, wenn vorhanden. Ist in .gitignore.

return [
    // Salt für die IP-Anonymisierung. Ein langer Random-String, einmal setzen, niemals teilen.
    'ip_hash_salt' => bin2hex(random_bytes(16)),

    // Wenn der Speedtest nicht im Root-Pfad liegt, sondern z.B. unter /tools/speedtest/,
    // hier den Pfad eintragen (ohne Slash am Ende). Default leer.
    // 'base_path' => '/tools/speedtest',
];
