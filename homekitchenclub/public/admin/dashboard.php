<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$message = null;
$type_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $message = "Jeton de sécurité invalide, veuillez réessayer.";
    $type_message = 'err';
  } elseif (isset($_POST['supprimer_id'])) {
    $id = (int) $_POST['supprimer_id'];
    $stmt = $pdo->prepare("DELETE FROM recettes WHERE id = ?");
    $stmt->execute([$id]);
    $message = "Recette supprimée.";
    $type_message = 'ok';
  } elseif (isset($_POST['maj_id'])) {
    $id = (int) $_POST['maj_id'];

    // On récupère la recette existante pour garder les valeurs actuelles
    // sur les champs que l'admin n'a pas renseignés (mise à jour partielle).
    $stmtActuelle = $pdo->prepare("SELECT * FROM recettes WHERE id = ?");
    $stmtActuelle->execute([$id]);
    $actuelle = $stmtActuelle->fetch();

    if (!$actuelle) {
      $message = "Recette introuvable.";
      $type_message = 'err';
    } else {
      $titrePost = trim($_POST['titre'] ?? '');
      $categoriePost = (int) ($_POST['categorie_id'] ?? 0);
      $tempsPost = trim($_POST['temps_total'] ?? '');
      $diffPost = $_POST['difficulte'] ?? '';
      $nouveau = (int) $actuelle['nouveau'];
      $publie = 1;

      // Champ vide ou invalide -> on garde la valeur existante au lieu de bloquer.
      $titre = $titrePost !== '' ? $titrePost : $actuelle['titre'];
      $categorie_id = $categoriePost > 0 ? $categoriePost : $actuelle['categorie_id'];
      $temps = $tempsPost !== '' ? $tempsPost : $actuelle['temps_total'];
      $diff = in_array($diffPost, ['Facile', 'Moyen', 'Difficile'], true) ? $diffPost : $actuelle['difficulte'];

      $stmt = $pdo->prepare("
                UPDATE recettes
                SET titre = ?, categorie_id = ?, temps_total = ?, difficulte = ?, nouveau = ?, publie = ?
                WHERE id = ?
            ");
      $stmt->execute([$titre, $categorie_id, $temps, $diff, $nouveau, $publie, $id]);
      $message = "Recette mise à jour.";
      $type_message = 'ok';
    }
  }
}

$categorie_active = isset($_GET['categorie']) ? (int) $_GET['categorie'] : 0;

if ($categorie_active > 0) {
  $stmtR = $pdo->prepare("
        SELECT r.id, r.titre, r.temps_total, r.difficulte, r.nouveau, r.publie, r.categorie_id, r.image_url
        FROM recettes r
        WHERE r.categorie_id = ?
        ORDER BY r.titre ASC
    ");
  $stmtR->execute([$categorie_active]);
  $recettes = $stmtR->fetchAll();
} else {
  $recettes = $pdo->query("
        SELECT r.id, r.titre, r.temps_total, r.difficulte, r.nouveau, r.publie, r.categorie_id, r.image_url
        FROM recettes r
        ORDER BY r.titre ASC
    ")->fetchAll();
}

$categories = $pdo->query("SELECT id, nom FROM categories ORDER BY ordre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Kitchen Club — Recettes de saison</title>
  <link rel="icon" href="/image?f=logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style"
    href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap">
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap"
    media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap">
  </noscript>
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    html,
    body {
      height: 100%
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh
    }

    footer {
      margin-top: auto
    }

    @media (max-width:600px) {
      footer .wrap {
        flex-direction: column-reverse;
        gap: 16px;
        text-align: center
      }

      footer .wrap>span {
        padding-top: 16px;
        border-top: 1px solid rgba(241, 234, 218, .2);
        width: 100%
      }

      footer .wrap>div {
        justify-content: center;
        width: 100%
      }
    }

    .admin-toolbar {
      background: var(--ink);
      color: var(--paper);
      border-bottom: 2px solid var(--ink)
    }

    .admin-toolbar .wrap {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 28px;
      font-family: var(--font-mono);
      font-size: .82rem
    }

    .admin-toolbar a {
      border: 1px solid var(--paper);
      padding: 6px 14px;
      border-radius: 999px
    }

    .admin-toolbar a:hover {
      background: var(--paper);
      color: var(--ink)
    }

    .edit-field {
      border: none;
      background: transparent;
      padding: 2px 4px;
      margin: -2px -4px;
      border-radius: 4px;
      font-family: inherit;
      color: inherit;
      width: 100%;
    }

    .edit-field:hover {
      background: rgba(30, 42, 31, .05)
    }

    .edit-field:focus {
      outline: none;
      background: #fff;
      box-shadow: 0 0 0 2px var(--tomato)
    }

    select.edit-field {
      cursor: pointer;
      -webkit-appearance: none;
      appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231E2A1F' stroke-width='2'><path d='M6 9l6 6 6-6'/></svg>");
      background-repeat: no-repeat;
      background-position: right 4px center;
      background-size: 14px;
      padding-right: 20px
    }

    .card h3.edit-title {
      font-family: var(--font-display);
      font-size: 1.3rem;
      margin: 0 0 8px;
      font-weight: 700
    }

    .card input.edit-title {
      font-family: var(--font-display);
      font-size: 1.3rem;
      margin: 0 0 8px;
      font-weight: 700
    }

    .card .tag select.edit-field {
      font-family: var(--font-mono);
      text-transform: uppercase;
      font-size: .7rem;
      letter-spacing: .08em;
      color: var(--tomato);
      border: none;
      background: transparent;
      margin-bottom: 8px
    }

    .meta-row {
      display: flex;
      gap: 10px;
      font-family: var(--font-mono);
      font-size: .78rem
    }

    .meta-row input.edit-field {
      max-width: 90px
    }

    .checks-inline {
      display: flex;
      gap: 14px;
      align-items: center;
      font-family: var(--font-mono);
      font-size: .72rem;
      margin-top: 10px
    }

    .checks-inline label {
      display: flex;
      align-items: center;
      gap: 4px
    }

    .card-admin-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 18px;
      border-top: 1px dashed var(--line);
    }

    form.card-form {
      margin: 0
    }

    .btn-save {
      background: var(--ink);
      color: var(--paper);
      padding: 6px 14px;
      border-radius: 999px;
      font-family: var(--font-mono);
      font-size: .72rem;
      border: 1px solid var(--ink);
      cursor: pointer
    }

    .btn-save:hover {
      background: var(--tomato);
      border-color: var(--tomato)
    }

    form.delete {
      display: inline;
      margin: 0
    }

    .btn-del {
      background: none;
      border: 1px solid var(--tomato);
      color: var(--tomato);
      padding: 5px 10px;
      border-radius: 999px;
      font-family: var(--font-mono);
      font-size: .72rem;
      cursor: pointer
    }

    .btn-del:hover {
      background: var(--tomato);
      color: #fff
    }

    .msg {
      padding: 12px 16px;
      border-radius: 4px;
      margin: 20px 0;
      font-size: .9rem
    }

    .msg.ok {
      background: #dff0d8;
      color: #2c5c2c
    }

    .msg.err {
      background: #f8d7da;
      color: #8a1c25
    }

    .card-add {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 280px;
      border: 2px dashed var(--ink);
      background: transparent;
      color: var(--ink);
      text-align: center;
      gap: 10px;
      transition: background .15s;
    }

    .card-add:hover {
      background: rgba(30, 42, 31, .05);
      transform: translateY(-6px) rotate(-0.6deg)
    }

    .card-add-icon {
      font-family: var(--font-display);
      font-size: 2.4rem;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: 2px solid var(--ink);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card-add-label {
      font-family: var(--font-mono);
      font-size: .82rem;
      text-transform: uppercase;
      letter-spacing: .06em
    }

    .cat-strip a.active {
      background: var(--ink);
      color: var(--paper)
    }

    .contact-modal {
      border: 2px solid var(--ink);
      border-radius: 8px;
      padding: 0;
      max-width: 440px;
      width: 90%
    }

    .contact-modal::backdrop {
      background: rgba(30, 42, 31, .55)
    }

    .contact-modal .cm-inner {
      padding: 28px
    }

    .contact-modal h2 {
      font-family: var(--font-display);
      font-size: 1.4rem;
      margin: 0 0 6px
    }

    .contact-modal p.intro {
      color: #666;
      font-size: .9rem;
      margin: 0 0 18px
    }

    .contact-modal label {
      display: block;
      font-size: .82rem;
      font-family: var(--font-mono);
      margin: 12px 0 6px
    }

    .contact-modal input,
    .contact-modal textarea {
      width: 100%;
      padding: 9px 11px;
      border: 2px solid var(--ink);
      border-radius: 4px;
      font-family: var(--font-body);
      font-size: .92rem;
      box-sizing: border-box
    }

    .contact-modal textarea {
      resize: vertical;
      min-height: 110px
    }

    .contact-modal .cm-actions {
      display: flex;
      gap: 10px;
      margin-top: 18px
    }

    .contact-modal button {
      padding: 11px 18px;
      border-radius: 999px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit
    }

    .contact-modal .btn-envoyer {
      flex: 1;
      background: var(--ink);
      color: var(--paper);
      border: 1px solid var(--ink)
    }

    .contact-modal .btn-envoyer:hover {
      background: var(--tomato);
      border-color: var(--tomato)
    }

    .contact-modal .btn-annuler {
      background: transparent;
      border: 1px solid var(--line);
      color: var(--ink)
    }

    .contact-modal .cm-msg {
      padding: 10px 12px;
      border-radius: 4px;
      font-size: .86rem;
      margin-bottom: 10px
    }

    .contact-modal .cm-msg.ok {
      background: #dff0d8;
      color: #2c5c2c
    }

    .contact-modal .cm-msg.err {
      background: #f8d7da;
      color: #8a1c25
    }

    .contact-modal .cm-close {
      position: absolute;
      top: 10px;
      right: 14px;
      background: none;
      border: none;
      font-size: 1.3rem;
      cursor: pointer;
      color: var(--ink)
    }
  </style>
</head>

<body>

  <header class="site-header">
    <div class="wrap">
      <a href="index" class="logo"><img src="/image?f=logo-navbar.svg" alt="Home Kitchen Club"></a>
      <div style="display:flex;align-items:center;gap:8px;margin-left:auto">
        <span style="font-family:var(--font-mono);font-size:1rem;opacity:.8">Admin</span>
        <details class="profile-menu" style="margin-left:0">
          <summary aria-label="Compte">
            <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4"></circle>
              <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"></path>
            </svg>
          </summary>
          <div class="profile-dropdown">
            <a href="/user/logout">Déconnexion</a>
          </div>
        </details>
      </div>
    </div>
  </header>

  <div class="cat-strip">
    <div class="wrap">
      <a href="dashboard.php" class="<?= $categorie_active === 0 ? 'active' : '' ?>">Toutes</a>
      <?php foreach ($categories as $c): ?>
        <a href="dashboard.php?categorie=<?= $c['id'] ?>"
          class="<?= $categorie_active === (int) $c['id'] ? 'active' : '' ?>">
          <?= htmlspecialchars($c['nom']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <section class="section" id="recettes">
    <div class="wrap">
      <?php if ($message): ?>
        <div class="msg <?= $type_message ?>"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <div class="section-head">
        <h2>
          <?= $categorie_active > 0 ? htmlspecialchars(array_values(array_filter($categories, fn($c) => (int) $c['id'] === $categorie_active))[0]['nom'] ?? 'Recettes') : 'Toutes les recettes' ?>
        </h2>
        <span class="num"><?= count($recettes) ?> résultats</span>
      </div>

      <div class="grid">
        <?php foreach ($recettes as $r): ?>
          <div class="card" style="position:relative;cursor:pointer"
            onclick="if(!event.target.closest('input,select,button,label,a')){window.location='modifier?id=<?= $r['id'] ?>'}">
            <?php if ($r['nouveau']): ?><span class="badge">Nouveau</span><?php endif; ?>
            <div class="thumb"><?php if (!empty($r['image_url'])): ?><img src="/image?f=<?= urlencode($r['image_url']) ?>"
                  alt="" style="width:100%;height:100%;object-fit:cover"><?php else: ?>🍽<?php endif; ?></div>
            <form class="card-form" method="post">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="maj_id" value="<?= $r['id'] ?>">
              <div class="body">
                <span class="tag">
                  <select name="categorie_id" class="edit-field">
                    <?php foreach ($categories as $c): ?>
                      <option value="<?= $c['id'] ?>" <?= ($r['categorie_id'] == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </span>
                <input type="text" name="titre" class="edit-field edit-title"
                  value="<?= htmlspecialchars($r['titre']) ?>">
                <div class="meta-row">
                  <input type="text" name="temps_total" class="edit-field"
                    value="<?= htmlspecialchars($r['temps_total']) ?>">
                  <input type="hidden" name="difficulte" value="<?= htmlspecialchars($r['difficulte']) ?>">
                </div>
              </div>
              <div class="card-admin-actions">
                <button type="submit" form="delete-<?= $r['id'] ?>" class="btn-del">Supprimer</button>
                <button type="submit" class="btn-save">Enregistrer</button>
              </div>
            </form>
            <form id="delete-<?= $r['id'] ?>" class="delete" method="post"
              onsubmit="return confirm('Supprimer définitivement « <?= htmlspecialchars(addslashes($r['titre'])) ?> » ?');">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="supprimer_id" value="<?= $r['id'] ?>">
            </form>
          </div>
        <?php endforeach; ?>
        <a class="card card-add" href="ajouter">
          <span class="card-add-icon">+</span>
          <span class="card-add-label">Ajouter une recette</span>
        </a>
        <?php if (!$recettes): ?>
          <p style="color:#888">Aucune recette pour le moment.</p>
        <?php endif; ?>
      </div>
      <div style="text-align:center;margin-top:36px">
        <button type="button" onclick="window.scrollTo({top:0,behavior:'smooth'})"
          style="background:none;border:1px solid var(--ink);color:var(--ink);padding:10px 22px;border-radius:999px;font-family:var(--font-mono);font-size:.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px">
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.6" fill="none"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 19V5"></path>
            <path d="M6 11l6-6 6 6"></path>
          </svg>
          Remonter en haut
        </button>
      </div>
    </div>
  </section>

  <footer>
    <div class="wrap">
      <span>© <?= date('Y') ?> Home Kitchen Club</span>
      <div style="display:flex;gap:20px;align-items:center">
        <button type="button" onclick="document.getElementById('contact-modal').showModal()"
          style="background:none;border:none;color:inherit;text-decoration:none;cursor:pointer;font:inherit;padding:0">Nous
          contacter</button>
        <span style="opacity:.5">·</span>
        <a href="/mentions-legales" style="text-decoration:none">Mentions légales</a>
      </div>
    </div>
  </footer>

  <dialog id="contact-modal" class="contact-modal">
    <button type="button" class="cm-close" onclick="document.getElementById('contact-modal').close()"
      aria-label="Fermer">&times;</button>
    <div class="cm-inner">
      <h2>Nous contacter</h2>
      <p class="intro">Une question, une suggestion de recette, un problème sur le site ? Écrivez-nous.</p>

      <div id="contact-cm-msg"></div>

      <form id="contact-form">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <label for="cm-nom">Nom</label>
        <input type="text" id="cm-nom" name="nom" placeholder="Votre nom">

        <label for="cm-email">Email</label>
        <input type="email" id="cm-email" name="email" required>

        <label for="cm-sujet">Sujet</label>
        <input type="text" id="cm-sujet" name="sujet" placeholder="Objet de votre message">

        <label for="cm-message">Message</label>
        <textarea id="cm-message" name="message" required maxlength="5000"></textarea>

        <div class="cm-actions">
          <button type="button" class="btn-annuler"
            onclick="document.getElementById('contact-modal').close()">Annuler</button>
          <button type="submit" class="btn-envoyer">Envoyer</button>
        </div>
      </form>
    </div>
  </dialog>

  <script>
    document.getElementById('contact-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = e.target;
      const msgBox = document.getElementById('contact-cm-msg');
      const btn = form.querySelector('.btn-envoyer');
      btn.disabled = true;
      msgBox.innerHTML = '';

      fetch('contact_envoyer.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
      })
        .then(r => r.json())
        .then(data => {
          msgBox.innerHTML = '<div class="cm-msg ' + (data.success ? 'ok' : 'err') + '">' +
            data.message.replace(/</g, '&lt;') + '</div>';
          if (data.success) {
            form.reset();
            setTimeout(() => document.getElementById('contact-modal').close(), 2000);
          }
        })
        .catch(() => {
          msgBox.innerHTML = '<div class="cm-msg err">Une erreur est survenue, veuillez réessayer.</div>';
        })
        .finally(() => { btn.disabled = false; });
    });

    document.getElementById('contact-modal').addEventListener('close', function () {
      document.getElementById('contact-cm-msg').innerHTML = '';
    });
  </script>

</body>

</html>