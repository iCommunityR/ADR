<?php
// config/config.php - example configuration for local/XAMPP use
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'africa_adr',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'African Disputes Resolution',
        // For XAMPP or hosting under a subfolder, set base_url to '/yourfolder' or leave empty for relative paths
        'base_url' => ''
    ],
];
