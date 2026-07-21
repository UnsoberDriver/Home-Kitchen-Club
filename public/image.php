<?php
// Empêche d'accéder à n'importe quel fichier du serveur (sécurité anti path-traversal)
$fichier = basename($_GET['f'] ?? '');

if ($fichier === '') {
    http_response_code(404);
    exit;
}

$chemin = __DIR__ . '/../uploads/' . $fichier;

if (!is_file($chemin)) {
    http_response_code(404);
    exit;
}

// Détermine le type MIME selon l'extension
$ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
$mimes = [
    'avif' => 'image/avif',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
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