<?php
/**
 * creer_admin.php
 * -----------------------------------------------------------
 * Script à usage unique pour créer le premier compte administrateur.
 * -> Ouvrez-le une fois dans votre navigateur (ex: http://localhost/admin/creer_admin.php)
 * -> Une fois le compte créé, SUPPRIMEZ ce fichier du serveur (sécurité).
 * -----------------------------------------------------------
 */

require_once __DIR__ . '/db.php';

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
        // Vérifie qu'aucun admin n'existe déjà avec cet email
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = "Un compte existe déjà avec cet email.";
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (email, mot_de_passe_hash, nom) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hash, $nom]);
            $succes = true;
            $message = "Compte administrateur créé avec succès. Vous pouvez maintenant vous connecter, puis supprimer ce fichier (creer_admin.php).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Créer un compte admin — Home Kitchen Club</title>
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
  .msg.ok{background:#dff0d8;color:#2c5c2c}
  .msg.err{background:#f8d7da;color:#8a1c25}
</style>
</head>
<body>
  <div class="box">
    <h1>Créer le compte admin</h1>
    <?php if ($message): ?>
      <div class="msg <?= $succes ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$succes): ?>
    <form method="post">
      <label for="nom">Nom</label>
      <input type="text" id="nom" name="nom" placeholder="Votre nom">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="mot_de_passe">Mot de passe (8 caractères min.)</label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">

      <label for="mot_de_passe_confirm">Confirmer le mot de passe</label>
      <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required minlength="8">

      <button type="submit">Créer le compte</button>
    </form>
    <?php else: ?>
      <p><a href="login.php">→ Aller à la page de connexion</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
