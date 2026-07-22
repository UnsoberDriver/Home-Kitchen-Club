<?php
/**
 * contact_envoyer.php — endpoint AJAX appelé par la popup "Nous contacter"
 * Retourne toujours du JSON : { "success": bool, "message": string }
 */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function repondre(bool $success, string $message): void
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(false, "Méthode non autorisée.");
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    repondre(false, "Jeton de sécurité invalide, veuillez recharger la page.");
}

$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$sujet = trim($_POST['sujet'] ?? '');
$texte = trim($_POST['message'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(false, "Adresse email invalide.");
}
if ($texte === '') {
    repondre(false, "Le message ne peut pas être vide.");
}
if (mb_strlen($texte) > 5000) {
    repondre(false, "Le message est trop long (5000 caractères maximum).");
}

$stmt = $pdo->prepare("
    INSERT INTO messages_contact (nom, email, sujet, message)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([
    $nom !== '' ? $nom : null,
    $email,
    $sujet !== '' ? $sujet : null,
    $texte,
]);

repondre(true, "Votre message a bien été envoyé. Merci, nous reviendrons vers vous rapidement.");
