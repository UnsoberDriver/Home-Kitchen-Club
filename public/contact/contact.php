<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $message = "Jeton de sécurité invalide, veuillez réessayer.";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sujet = trim($_POST['sujet'] ?? '');
        $texte = trim($_POST['message'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Adresse email invalide.";
        } elseif ($texte === '') {
            $message = "Le message ne peut pas être vide.";
        } elseif (mb_strlen($texte) > 5000) {
            $message = "Le message est trop long (5000 caractères maximum).";
        } else {
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
            $succes = true;
            $message = "Votre message a bien été envoyé. Merci, nous reviendrons vers vous rapidement.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nous contacter — Home Kitchen Club</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap"></noscript>
<link rel="stylesheet" href="style.css">
<style>
  body{display:flex;align-items:center;justify-content:center;min-height:100vh}
  .box{max-width:480px;width:100%;background:#fff;border:2px solid var(--ink);border-radius:6px;padding:32px;margin:40px 16px}
  .box h1{font-family:var(--font-display);font-size:1.6rem;margin-top:0}
  .box p.intro{color:#666;font-size:.92rem;margin-top:-6px}
  label{display:block;font-size:.85rem;font-family:var(--font-mono);margin:14px 0 6px}
  input,textarea{width:100%;padding:10px 12px;border:2px solid var(--ink);border-radius:4px;font-family:var(--font-body);font-size:.95rem;box-sizing:border-box}
  textarea{resize:vertical;min-height:130px}
  button{margin-top:20px;width:100%;padding:12px;background:var(--ink);color:var(--paper);border:none;border-radius:999px;font-weight:600;cursor:pointer}
  button:hover{background:var(--tomato)}
  .msg{padding:12px;border-radius:4px;margin-bottom:10px;font-size:.9rem}
  .msg.ok{background:#dff0d8;color:#2c5c2c}
  .msg.err{background:#f8d7da;color:#8a1c25}
  .box p.back{font-size:.88rem;text-align:center;margin-top:16px}
</style>
</head>
<body>
  <div class="box">
    <h1>Nous contacter</h1>
    <p class="intro">Une question, une suggestion de recette, un problème sur le site ? Écrivez-nous.</p>

    <?php if ($message): ?>
      <div class="msg <?= $succes ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$succes): ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

      <label for="nom">Nom</label>
      <input type="text" id="nom" name="nom" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

      <label for="sujet">Sujet</label>
      <input type="text" id="sujet" name="sujet" placeholder="Objet de votre message" value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>">

      <label for="message">Message</label>
      <textarea id="message" name="message" required maxlength="5000"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

      <button type="submit">Envoyer</button>
    </form>
    <?php else: ?>
      <p class="back"><a href="index">← Retour à l'accueil</a></p>
    <?php endif; ?>
  </div>
</body>
</html>