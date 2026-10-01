<?php

require_once __DIR__ . '/../../includes/db.php';

session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $mdp_confirm = $_POST['mot_de_passe_confirm'] ?? '';

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inscriptions_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            cree_le DATETIME NOT NULL,
            INDEX (ip, cree_le)
        )
    ");
    $fenetre = date('Y-m-d H:i:s', time() - 60 * 60);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions_log WHERE ip = ? AND cree_le > ?");
    $stmt->execute([$ip, $fenetre]);
    $trop_inscriptions = (int) $stmt->fetchColumn() >= 5;

    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $message = "Jeton de sécurité invalide, veuillez réessayer.";
    } elseif ($trop_inscriptions) {
        $message = "Trop d'inscriptions depuis cette adresse. Merci de réessayer plus tard.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } elseif (strlen($mdp) < 8) {
        $message = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($mdp !== $mdp_confirm) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = "Un compte existe déjà avec cet email.";
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, mot_de_passe_hash, nom, est_admin) VALUES (?, ?, ?, 0)");
            $stmt->execute([$email, $hash, $nom]);
            $pdo->prepare("INSERT INTO inscriptions_log (ip, cree_le) VALUES (?, NOW())")->execute([$ip]);

            $user_id = $pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_nom'] = $nom ?: $email;

            header('Location: /index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription — Home Kitchen Club</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap"></noscript>
<link rel="stylesheet" href="/assets/style.css">
<style>
  body{display:flex;align-items:center;justify-content:center;min-height:100vh}
  .box{max-width:420px;width:100%;background:#fff;border:2px solid var(--ink);border-radius:6px;padding:32px}
  .box h1{font-family:var(--font-display);font-size:1.6rem;margin-top:0}
  label{display:block;font-size:.85rem;font-family:var(--font-mono);margin:14px 0 6px}
  input{width:100%;padding:10px 12px;border:2px solid var(--ink);border-radius:4px;font-family:var(--font-body);font-size:.95rem}
  button{margin-top:20px;width:100%;padding:12px;background:var(--ink);color:var(--paper);border:none;border-radius:999px;font-weight:600;cursor:pointer}
  button:hover{background:var(--tomato)}
  .msg{padding:12px;border-radius:4px;margin-bottom:10px;font-size:.9rem}
  .msg.err{background:#f8d7da;color:#8a1c25}
  .box p{font-size:.88rem;text-align:center;margin-top:16px}
  .pwd-wrap{position:relative}
  .pwd-wrap input{padding-right:44px}
  .pwd-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;margin:0;width:auto;color:#666}
  .pwd-toggle svg{display:block}

  @media (max-width:600px){
    .box{max-width:100%;padding:28px 22px;border-radius:10px}
    .box h1{font-size:2rem;margin-bottom:6px}
    label{font-size:1rem;margin:18px 0 8px}
    input{padding:14px 14px;font-size:1.1rem;border-radius:6px}
    button{padding:16px;font-size:1.1rem;margin-top:26px}
    .msg{font-size:1rem;padding:14px}
    .box p{font-size:1rem}
  }
</style>
</head>
<body>
  <div class="box">
    <h1>Créer un compte</h1>
    <?php if ($message): ?>
      <div class="msg err"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

      <label for="nom">Nom</label>
      <input type="text" id="nom" name="nom" placeholder="Votre nom">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="mot_de_passe">Mot de passe (8 caractères min.)</label>
      <div class="pwd-wrap">
        <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">
        <button type="button" class="pwd-toggle" onclick="togglePwd('mot_de_passe',this)" aria-label="Voir le mot de passe">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>

      <label for="mot_de_passe_confirm">Confirmer le mot de passe</label>
      <div class="pwd-wrap">
        <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required minlength="8">
        <button type="button" class="pwd-toggle" onclick="togglePwd('mot_de_passe_confirm',this)" aria-label="Voir le mot de passe">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>

      <button type="submit">S'inscrire</button>
    </form>
    <p>Déjà un compte ? <a href="/login">Se connecter</a></p>
  </div>
<script>
function togglePwd(id, btn) {
  const input = document.getElementById(id);
  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  btn.innerHTML = show
    ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
    : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
</script>
</body>
</html>