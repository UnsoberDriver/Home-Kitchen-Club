<?php
$fichier = basename($_GET['f'] ?? '');

if ($fichier === '') {
    http_response_code(404);
    exit;
}

$chemin = __DIR__ . '/../../uploads/' . $fichier;

if (!is_file($chemin)) {
    http_response_code(404);
    exit;
}

$ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
$mimes = [
    'avif' => 'image/avif',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'svg'  => 'image/svg+xml',
];

if (!isset($mimes[$ext])) {
    http_response_code(403);
    exit;
}

header('Content-Type: ' . $mimes[$ext]);
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . filesize($chemin));

readfile($chemin);
exit;