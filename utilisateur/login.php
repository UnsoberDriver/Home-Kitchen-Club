<?php
session_start();
require_once __DIR__ . '/db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    // 1) On regarde d'abord si c'est un compte admin
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($mdp, $admin['mot_de_passe_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nom'] = $admin['nom'] ?: $admin['email'];

        $pdo->prepare("UPDATE admins SET derniere_connexion = NOW() WHERE id = ?")
            ->execute([$admin['id']]);

        if (!empty($_POST['rester_connecte'])) {
            $selecteur = bin2hex(random_bytes(9));
            $validateur = bin2hex(random_bytes(33));
            $validateur_hash = hash('sha256', $validateur);
            $expiration = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 30 jours

            $pdo->prepare("INSERT INTO remember_tokens (admin_id, selecteur, validateur_hash, expiration) VALUES (?, ?, ?, ?)")
                ->execute([$admin['id'], $selecteur, $validateur_hash, $expiration]);

            setcookie(
                'remember_admin',
                $selecteur . ':' . $validateur,
                [
                    'expires' => time() + 60 * 60 * 24 * 30,
                    'path' => '/',
                    'httponly' => true,
                    'secure' => true,
                    'samesite' => 'Lax',
                ]
            );
        }

        header('Location: dashboard.php');
        exit;
    }

    // 2) Sinon, on regarde si c'est un utilisateur inscrit normalement
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mdp, $user['mot_de_passe_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'] ?: $user['email'];

        $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?")
            ->execute([$user['id']]);

        // Compte utilisateur promu admin (est_admin = 1) : on lui ouvre l'espace admin.
        // On n'utilise PAS $_SESSION['admin_id'] pour ne pas entrer en conflit avec la
        // table `admins` (remember_tokens a une clé étrangère dessus).
        if ((int) $user['est_admin'] === 1) {
            $_SESSION['user_admin_id'] = $user['id'];
            $_SESSION['admin_nom'] = $user['nom'] ?: $user['email'];
            header('Location: dashboard.php');
            exit;
        }

        header('Location: index.php');
        exit;
    }

    $erreur = "Email ou mot de passe incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — Home Kitchen Club</title>
<link rel="icon" href="logo.png">
<link rel="stylesheet" href="style.css">
<style>
  body{display:flex;align-items:center;justify-content:center;min-height:100vh}
  .box{max-width:380px;width:100%;background:#fff;border:2px solid var(--ink);border-radius:6px;padding:32px}
  .box h1{font-family:var(--font-display);font-size:1.6rem;margin-top:0}
  label{display:block;font-size:.85rem;font-family:var(--font-mono);margin:14px 0 6px}
  input{width:100%;padding:10px 12px;border:2px solid var(--ink);border-radius:4px;font-family:var(--font-body);font-size:.95rem}
  button{margin-top:20px;width:100%;padding:12px;background:var(--ink);color:var(--paper);border:none;border-radius:999px;font-weight:600;cursor:pointer}
  button:hover{background:var(--tomato)}
  .msg.err{background:#f8d7da;color:#8a1c25;padding:12px;border-radius:4px;font-size:.9rem;margin-bottom:10px}

  @media (max-width:600px){
    body{padding:16px}
    .box{max-width:100%;padding:32px 24px;border-radius:14px}
    .box h1{font-size:2.2rem;margin-bottom:10px}
    label{font-size:1rem;margin:18px 0 8px;display:block}
    input{padding:14px 14px;font-size:1.1rem;border-radius:8px}
    input[type="checkbox"]{width:20px;height:20px}
    button{padding:16px;font-size:1.1rem;margin-top:26px;border-radius:999px}
    .msg.err{font-size:1rem;padding:14px}
    p{font-size:1rem;margin-top:16px}
  }
</style>
</head>
<body>
  <div class="box">
    <h1>Espace connexion</h1>
    <?php if ($erreur): ?><div class="msg err"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>
    <form method="post">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autofocus>

      <label for="mot_de_passe">Mot de passe</label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" required>

      <label style="display:flex;align-items:center;gap:8px;font-family:var(--font-body);text-transform:none;margin-top:16px">
        <input type="checkbox" name="rester_connecte" style="width:auto"> Rester connecté
      </label>

      <button type="submit">Se connecter</button>
    </form>
    <p style="font-size:.88rem;text-align:center;margin-top:16px">Pas encore de compte ? <a href="register">S'inscrire</a></p>
  </div>
  <script>
    document.querySelectorAll('input').forEach(function (el) {
      el.addEventListener('focus', function () {
        setTimeout(function () {
          el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }, 300);
      });
    });
  </script>
</body>
</html>