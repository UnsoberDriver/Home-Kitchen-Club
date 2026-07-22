<?php
// auth_check.php — à inclure en haut de toute page admin protégée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id']) && !empty($_COOKIE['remember_admin'])) {
    require_once __DIR__ . '/db.php';

    $parts = explode(':', $_COOKIE['remember_admin'], 2);
    if (count($parts) === 2) {
        [$selecteur, $validateur] = $parts;

        $stmt = $pdo->prepare("SELECT * FROM remember_tokens WHERE selecteur = ? AND expiration > NOW()");
        $stmt->execute([$selecteur]);
        $token = $stmt->fetch();

        if ($token && hash_equals($token['validateur_hash'], hash('sha256', $validateur))) {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$token['admin_id']]);
            $admin = $stmt->fetch();

            if ($admin) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nom'] = $admin['nom'] ?: $admin['email'];

                // Fait tourner le jeton (rotation) pour limiter les risques de vol de cookie
                $pdo->prepare("DELETE FROM remember_tokens WHERE selecteur = ?")->execute([$selecteur]);

                $nouveau_selecteur = bin2hex(random_bytes(9));
                $nouveau_validateur = bin2hex(random_bytes(33));
                $expiration = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

                $pdo->prepare("INSERT INTO remember_tokens (admin_id, selecteur, validateur_hash, expiration) VALUES (?, ?, ?, ?)")
                    ->execute([$admin['id'], $nouveau_selecteur, hash('sha256', $nouveau_validateur), $expiration]);

                setcookie(
                    'remember_admin',
                    $nouveau_selecteur . ':' . $nouveau_validateur,
                    [
                        'expires' => time() + 60 * 60 * 24 * 30,
                        'path' => '/',
                        'httponly' => true,
                        'secure' => true,
                        'samesite' => 'Lax',
                    ]
                );
            }
        } else {
            // Jeton invalide ou volé : on supprime le cookie par précaution
            setcookie('remember_admin', '', ['expires' => time() - 3600, 'path' => '/']);
        }
    }
}

if (empty($_SESSION['admin_id']) && empty($_SESSION['user_admin_id'])) {
    header('Location: login.php');
    exit;
}