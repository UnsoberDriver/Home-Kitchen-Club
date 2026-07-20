<?php
session_start();
require_once __DIR__ . '/db.php';

if (!empty($_COOKIE['remember_admin'])) {
    $parts = explode(':', $_COOKIE['remember_admin'], 2);
    if (count($parts) === 2) {
        $pdo->prepare("DELETE FROM remember_tokens WHERE selecteur = ?")->execute([$parts[0]]);
    }
    setcookie('remember_admin', '', ['expires' => time() - 3600, 'path' => '/']);
}

session_unset();
session_destroy();
header('Location: login.php');
exit;