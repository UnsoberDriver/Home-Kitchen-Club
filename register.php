<?php
/**
 * register — page d'inscription publique
 * Crée un compte dans la table `utilisateurs` (est_admin = 0 par défaut).
 * Ces comptes ne peuvent jamais accéder à l'espace admin (dashboard, modifier...).
 */

require_once __DIR__ . '/db.php';

session_start();

$message = '';
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $mdp_confirm = $_POST['mot_de_passe_confirm'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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

            $user_id = $pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_nom'] = $nom ?: $email;

            header('Location: index.php');
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
<link rel="stylesheet" href="style.css">
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
      <label for="nom">Nom</label>
      <input type="text" id="nom" name="nom" placeholder="Votre nom">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="mot_de_passe">Mot de passe (8 caractères min.)</label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">

      <label for="mot_de_passe_confirm">Confirmer le mot de passe</label>
      <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required minlength="8">

      <button type="submit">S'inscrire</button>
    </form>
    <p>Déjà un compte ? <a href="login">Se connecter</a></p>
  </div>
</body>
</html>