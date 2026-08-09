<?php
// public/download.php - safe file download for documents
require __DIR__ . '/../src/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}

$stmt = $pdo->prepare('SELECT file_path, original_filename, mime_type FROM documents WHERE id = :id');
$stmt->execute([':id' => $id]);
$doc = $stmt->fetch();
if (!$doc || empty($doc['file_path'])) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$storageBase = realpath(dirname(__DIR__) . '/storage/uploads');
$requested = realpath(dirname(__DIR__) . '/' . ltrim($doc['file_path'], '/'));

if ($requested === false || strpos($requested, $storageBase) !== 0) {
    // prevent path traversal or outside-storage files
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$filename = $doc['original_filename'] ?: basename($requested);
$mime = $doc['mime_type'] ?: 'application/octet-stream';

if (!is_readable($requested)) {
    http_response_code(404);
    echo 'File not available';
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
header('Content-Length: ' . filesize($requested));
readfile($requested);
exit;
