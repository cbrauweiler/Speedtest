<?php

declare(strict_types=1);

return [
    'app_name'      => 'Speedtest',
    'app_version'   => '0.1.0',
    'repo_url'      => 'https://github.com/cbrauweiler/Speedtest',
    'db_path'       => __DIR__ . '/data/speedtest.sqlite',
    'blob_dir'      => __DIR__ . '/blob',
    'allowed_sizes' => [2, 5, 10, 25, 50, 100],
    'history_limit' => 100,
    'ip_hash_salt'  => 'CHANGE-ME-RANDOM-STRING',
    'base_path'     => '/Projekte/speedtest',
];
