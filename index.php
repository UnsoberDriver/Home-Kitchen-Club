<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$categories = $pdo->query("SELECT id, nom FROM categories ORDER BY ordre")->fetchAll();

$categorie_active = isset($_GET['categorie']) ? (int) $_GET['categorie'] : 0;

if ($categorie_active > 0) {
    $stmtR = $pdo->prepare("
        SELECT r.id, r.titre, r.intro, r.temps_total, r.difficulte, r.nouveau, r.image_url, c.nom AS cat
        FROM recettes r
        JOIN categories c ON c.id = r.categorie_id
        WHERE r.publie = 1 AND r.categorie_id = ?
        ORDER BY r.titre ASC
    ");
    $stmtR->execute([$categorie_active]);
    $recettes = $stmtR->fetchAll();
} else {
    $recettes = $pdo->query("
        SELECT r.id, r.titre, r.intro, r.temps_total, r.difficulte, r.nouveau, r.image_url, c.nom AS cat
        FROM recettes r
        JOIN categories c ON c.id = r.categorie_id
        WHERE r.publie = 1
        ORDER BY r.titre ASC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home Kitchen Club — Recettes de saison</title>
<link rel="icon" href="logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap"></noscript>

<!-- Preload style.css to shorten the critical chain, load it non-blocking -->
<link rel="preload" href="style.css" as="style">
<link rel="stylesheet" href="style.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="style.css"></noscript>

<style>
  /* ---- Critical CSS (above-the-fold): header + category strip ---- */
  :root{
    --ink:#1E2A1F;--paper:#F1EADA;--paper-2:#E8DEC8;--saffron:#E3A008;
    --tomato:#C1440E;--sage:#7C8B6F;--line:rgba(30,42,31,0.15);
    --font-display:'Fraunces', Georgia, serif;
    --font-body:'Work Sans', Arial, sans-serif;
    --font-mono:'IBM Plex Mono', monospace;
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{margin:0;display:flex;flex-direction:column;min-height:100vh;background:var(--paper);color:var(--ink);font-family:var(--font-body);line-height:1.5}
  a{color:inherit;text-decoration:none}
  .wrap{max-width:1120px;margin:0 auto;padding:0 28px}
  footer{margin-top:auto}

  .site-header{border-bottom:2px solid var(--ink);position:sticky;top:0;background:var(--ink);color:var(--paper);z-index:50}
  .site-header .wrap{display:flex;align-items:center;justify-content:flex-start;padding-top:18px;padding-bottom:18px}
  .logo{font-family:var(--font-display);font-size:1.6rem;font-weight:600;letter-spacing:-0.02em;display:flex;align-items:center;gap:8px}
  .logo .dot{width:10px;height:10px;border-radius:50%;background:var(--tomato);display:inline-block}
  .burger-btn{display:none;background:none;border:none;width:38px;height:38px;align-items:center;justify-content:center;color:var(--paper);cursor:pointer;flex-shrink:0;margin-left:-10px}
  .burger-btn svg{width:22px;height:22px;stroke:currentColor;fill:none}
  nav.main-nav{display:flex;gap:28px;font-size:.92rem;text-transform:uppercase;letter-spacing:.06em}
  .profile-menu{position:relative;list-style:none;margin-left:auto}
  .profile-menu summary{list-style:none;cursor:pointer;width:38px;height:38px;border-radius:50%;border:2px solid var(--paper);display:flex;align-items:center;justify-content:center;color:var(--paper)}
  .profile-menu summary::-webkit-details-marker{display:none}
  .profile-menu summary svg{width:20px;height:20px;stroke:currentColor;fill:none}

  .cat-strip{border-bottom:2px solid var(--ink);background:var(--ink)}
  .cat-strip .wrap{display:flex;gap:8px;overflow-x:auto;justify-content:center}
  .cat-strip a{color:var(--paper);padding:14px 22px;font-size:.85rem;text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;border-right:1px solid rgba(241,234,218,.2);font-family:var(--font-mono)}
  .cat-strip a.active{background:var(--paper);color:var(--ink)}

  @media (max-width:860px){
    nav.main-nav{display:none}
    .logo .dot{display:none}
    .burger-btn{display:flex;margin-left:-18px}
    .cat-strip{display:none}
    body.nav-open .cat-strip{display:block}
    body.nav-open .cat-strip .wrap{flex-direction:column;overflow-x:visible}
    body.nav-open .cat-strip a{border-right:none;border-bottom:1px solid rgba(241,234,218,.2);text-align:center}
  }
  /* ---- End critical CSS ---- */

  @media (max-width:600px){
    footer .wrap{flex-direction:column-reverse;gap:16px;text-align:center}
    footer .wrap > span{padding-top:16px;border-top:1px solid rgba(241,234,218,.2);width:100%}
    footer .wrap > div{justify-content:center;width:100%}
  }

  .cat-strip a.active{background:var(--ink);color:var(--paper)}

  .contact-modal{border:2px solid var(--ink);border-radius:8px;padding:0;max-width:440px;width:90%}
  .contact-modal::backdrop{background:rgba(30,42,31,.55)}
  .contact-modal .cm-inner{padding:28px}
  .contact-modal h2{font-family:var(--font-display);font-size:1.4rem;margin:0 0 6px}
  .contact-modal p.intro{color:#666;font-size:.9rem;margin:0 0 18px}
  .contact-modal label{display:block;font-size:.82rem;font-family:var(--font-mono);margin:12px 0 6px}
  .contact-modal input,.contact-modal textarea{width:100%;padding:9px 11px;border:2px solid var(--ink);border-radius:4px;font-family:var(--font-body);font-size:.92rem;box-sizing:border-box}
  .contact-modal textarea{resize:vertical;min-height:110px}
  .contact-modal .cm-actions{display:flex;gap:10px;margin-top:18px}
  .contact-modal button{padding:11px 18px;border-radius:999px;font-weight:600;cursor:pointer;font-family:inherit}
  .contact-modal .btn-envoyer{flex:1;background:var(--ink);color:var(--paper);border:1px solid var(--ink)}
  .contact-modal .btn-envoyer:hover{background:var(--tomato);border-color:var(--tomato)}
  .contact-modal .btn-annuler{background:transparent;border:1px solid var(--line);color:var(--ink)}
  .contact-modal .cm-msg{padding:10px 12px;border-radius:4px;font-size:.86rem;margin-bottom:10px}
  .contact-modal .cm-msg.ok{background:#dff0d8;color:#2c5c2c}
  .contact-modal .cm-msg.err{background:#f8d7da;color:#8a1c25}
  .contact-modal .cm-close{position:absolute;top:10px;right:14px;background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--ink)}
</style>
</head>
<body>

<header class="site-header">
  <div class="wrap">
    <button type="button" class="burger-btn" aria-label="Menu" aria-expanded="false" onclick="document.body.classList.toggle('nav-open'); this.setAttribute('aria-expanded', document.body.classList.contains('nav-open'))">
      <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>
    <a href="index" class="logo"><img src="logo-navbar.svg" alt="Home Kitchen Club"></a>
    <nav class="main-nav">
    </nav>
    <details class="profile-menu">
      <summary aria-label="Compte">
        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="8" r="4"></circle>
          <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"></path>
        </svg>
      </summary>
      <div class="profile-dropdown">
        <a href="login">Connexion</a>
        <a href="register">Inscription</a>
      </div>
    </details>
  </div>
</header>

<div class="cat-strip">
  <div class="wrap">
    <a href="index" class="<?= $categorie_active === 0 ? 'active' : '' ?>">Toutes</a>
    <?php foreach ($categories as $c): ?>
      <a href="index?categorie=<?= $c['id'] ?>" class="<?= $categorie_active === (int) $c['id'] ? 'active' : '' ?>">
        <?= htmlspecialchars($c['nom']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<section class="section" id="recettes">
  <div class="wrap">
    <div class="section-head">
      <h2><?= $categorie_active > 0 ? htmlspecialchars(array_values(array_filter($categories, fn($c) => (int) $c['id'] === $categorie_active))[0]['nom'] ?? 'Recettes') : 'Dernières recettes' ?></h2>
      <span class="num"><?= count($recettes) ?> résultats</span>
    </div>
    <div class="grid">
      <?php foreach ($recettes as $i => $r): ?>
        <a class="card" href="recette?id=<?= $r['id'] ?>">
          <?php if (!empty($r['nouveau'])): ?><span class="badge">Nouveau</span><?php endif; ?>
          <div class="thumb">
            <?php if (!empty($r['image_url'])):
              $thumb_url = preg_replace('/\.avif$/', '_thumb.avif', $r['image_url']);
              $is_first = $i === 0;
            ?>
              <img src="uploads/<?= htmlspecialchars($thumb_url) ?>" alt="<?= htmlspecialchars($r['titre']) ?>"
                <?= $is_first ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>
                decoding="async" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              🍽
            <?php endif; ?>
          </div>
          <div class="body">
            <span class="tag"><?= htmlspecialchars($r['cat']) ?></span>
            <h3><?= htmlspecialchars($r['titre']) ?></h3>
          </div>
        </a>
      <?php endforeach; ?>
      <?php if (!$recettes): ?>
        <p style="color:#888">Aucune recette pour le moment.</p>
      <?php endif; ?>
    </div>
    <?php if ($recettes): ?>
      <div style="text-align:center;margin-top:36px">
        <button type="button" onclick="window.scrollTo({top:0,behavior:'smooth'})" style="background:none;border:1px solid var(--ink);color:var(--ink);padding:10px 22px;border-radius:999px;font-family:var(--font-mono);font-size:.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px">
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.6" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 19V5"></path>
            <path d="M6 11l6-6 6 6"></path>
          </svg>
          Remonter en haut
        </button>
      </div>
    <?php endif; ?>
  </div>
</section>

<footer>
  <div class="wrap">
    <span>© <?= date('Y') ?> Home Kitchen Club</span>
    <div style="display:flex;gap:20px;align-items:center">
      <button type="button" onclick="document.getElementById('contact-modal').showModal()" style="background:none;border:none;color:inherit;text-decoration:none;cursor:pointer;font:inherit;padding:0">Nous contacter</button>
      <span style="opacity:.5">·</span>
      <a href="mentions-legales" style="text-decoration:none">Mentions légales</a>
    </div>
  </div>
</footer>

<dialog id="contact-modal" class="contact-modal">
  <button type="button" class="cm-close" onclick="document.getElementById('contact-modal').close()" aria-label="Fermer">&times;</button>
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
        <button type="button" class="btn-annuler" onclick="document.getElementById('contact-modal').close()">Annuler</button>
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

    fetch('contact_envoyer', {
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