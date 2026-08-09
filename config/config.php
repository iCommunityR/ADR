<?php

return [
    'app' => [
        'name' => 'African Disputes Resolution',
        'base_url' => rtrim(getenv('APP_URL') ?: 'http://localhost/adr', '/'),
        'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Kampala',
    ],

    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'africa_adr',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],

    'upload' => [
        'path' => dirname(__DIR__) . '/storage/uploads',
        'max_bytes' => 10 * 1024 * 1024,

        'extensions' => [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'csv',
            'txt',
        ],

        'mimes' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/csv',
            'text/plain',
            'application/zip',
            'application/x-ole-storage',
            'application/CDFV2',
            'application/octet-stream',
        ],
    ],
];