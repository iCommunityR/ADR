<?php
// public/index.php - Front controller / simple router
require __DIR__ . '/../src/bootstrap.php';

$path = $_GET['p'] ?? 'home';

// simple whitelist of pages
$allowed = ['home','countries','documents','cases','institutions','research','document'];
if (!in_array($path, $allowed, true)) {
    $path = 'home';
}

// serve API-like download separately
if ($path === 'document' && isset($_GET['id'])) {
    require __DIR__ . '/download.php';
    exit;
}

// include page
$pageFile = __DIR__ . '/../src/pages/' . $path . '.php';
if (!file_exists($pageFile)) {
    $path = 'home';
    $pageFile = __DIR__ . '/../src/pages/home.php';
}

require __DIR__ . '/../src/views/header.php';
require $pageFile;
require __DIR__ . '/../src/views/footer.php';
