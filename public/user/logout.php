<?php
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si un "remember me" est actif, on supprime le jeton en base et le cookie
if (!empty($_COOKIE['remember_admin'])) {
    $parts = explode(':', $_COOKIE['remember_admin'], 2);
    if (count($parts) === 2) {
        [$selecteur, ] = $parts;
        $pdo->prepare("DELETE FROM remember_tokens WHERE selecteur = ?")->execute([$selecteur]);
    }
    setcookie('remember_admin', '', ['expires' => time() - 3600, 'path' => '/']);
}

// Vide et détruit la session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: /user/login');
exit;