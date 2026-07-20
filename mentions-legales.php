<?php
/**
 * mentions-legales.php — page légale publique
 * Pensez à remplacer les champs [entre crochets] par vos informations réelles.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mentions légales — Home Kitchen Club</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
  html,body{height:100%}
  body{display:flex;flex-direction:column;min-height:100vh}
  footer{margin-top:auto}

  @media (max-width:600px){
    footer .wrap{flex-direction:column-reverse;gap:8px;text-align:center}
  }

  .legal{max-width:760px;margin:0 auto;padding:60px 28px 80px}
  .legal h1{font-family:var(--font-display);font-size:2.1rem;margin-bottom:8px}
  .legal .updated{font-family:var(--font-mono);font-size:.78rem;color:#888;margin-bottom:40px}
  .legal h2{font-family:var(--font-display);font-size:1.3rem;margin:36px 0 12px;border-bottom:1px solid var(--line);padding-bottom:8px}
  .legal p{line-height:1.7;margin:0 0 14px}
  .legal ul{line-height:1.7;margin:0 0 14px;padding-left:20px}
  .legal a{color:var(--tomato);text-decoration:underline}
  .legal .placeholder{background:#fff3cd;padding:0 4px;border-radius:3px}

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
    <a href="index" class="logo"><span class="dot"></span> Home Kitchen Club</a>
  </div>
</header>

<div class="legal">
  <h1>Mentions légales</h1>
  <p class="updated">Dernière mise à jour : <?= date('d/m/Y') ?></p>

  <h2>1. Éditeur du site</h2>
  <p>
    Le présent site, accessible à l'adresse <strong>[nom de domaine, ex : homekitchenclub.fr]</strong>,
    est édité par :
  </p>
  <ul>
    <li>Nom / Raison sociale : <span class="placeholder">[Nom ou raison sociale]</span></li>
    <li>Statut : <span class="placeholder">[particulier / auto-entrepreneur / société — préciser forme juridique]</span></li>
    <li>Adresse : <span class="placeholder">[Adresse postale complète]</span></li>
    <li>SIRET / SIREN : <span class="placeholder">[si applicable]</span></li>
    <li>Email de contact : <span class="placeholder">[email de contact]</span></li>
    <li>Directeur de la publication : <span class="placeholder">[Nom du responsable de publication]</span></li>
  </ul>

  <h2>2. Hébergement</h2>
  <p>
    Le site est hébergé par :
  </p>
  <ul>
    <li>Hébergeur : Alwaysdata SAS</li>
    <li>Adresse : 91 rue du Faubourg Saint-Honoré, 75008 Paris, France</li>
    <li>Site web : <a href="https://www.alwaysdata.com" target="_blank" rel="noopener">www.alwaysdata.com</a></li>
  </ul>
  <p style="font-size:.85rem;color:#888">
    (Vérifiez ces coordonnées auprès de votre hébergeur réel avant publication.)
  </p>

  <h2>3. Propriété intellectuelle</h2>
  <p>
    L'ensemble des contenus présents sur ce site (textes, recettes, photographies, logos, mise en page)
    est protégé par le droit d'auteur, sauf mention contraire. Toute reproduction, représentation,
    modification ou diffusion, totale ou partielle, sans autorisation préalable de l'éditeur, est
    interdite et pourrait constituer une contrefaçon.
  </p>

  <h2>4. Données personnelles</h2>
  <p>
    Le site collecte certaines données personnelles dans le cadre de la création d'un compte
    utilisateur (nom, email, mot de passe) et de l'inscription à la newsletter. Ces données sont
    utilisées uniquement pour le fonctionnement du site (connexion, gestion du compte, envoi de
    la newsletter) et ne sont ni vendues ni transmises à des tiers à des fins commerciales.
  </p>
  <p>
    Conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi
    « Informatique et Libertés », vous disposez d'un droit d'accès, de rectification, de
    suppression et de portabilité de vos données. Pour exercer ce droit, contactez-nous à
    l'adresse : <span class="placeholder">[email de contact RGPD]</span>.
  </p>

  <h2>5. Cookies</h2>
  <p>
    Le site peut utiliser des cookies techniques nécessaires à son fonctionnement (maintien de
    la session de connexion notamment). Aucun cookie publicitaire ou de traçage tiers n'est déposé
    sans votre consentement préalable, le cas échéant.
  </p>

  <h2>6. Limitation de responsabilité</h2>
  <p>
    L'éditeur s'efforce d'assurer l'exactitude des informations publiées sur le site (notamment
    les recettes et temps de préparation) mais ne saurait être tenu responsable des erreurs,
    omissions ou de l'indisponibilité temporaire du site.
  </p>

  <h2>7. Droit applicable</h2>
  <p>
    Les présentes mentions légales sont soumises au droit français. En cas de litige, et à défaut
    de résolution amiable, les tribunaux français seront seuls compétents.
  </p>

  <h2>8. Contact</h2>
  <p>
    Pour toute question relative au site ou aux présentes mentions légales, vous pouvez nous
    contacter à l'adresse : <span class="placeholder">[email de contact]</span>.
  </p>
</div>

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

<button id="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Remonter en haut de la page" title="Remonter en haut">
  <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 19V5"></path>
    <path d="M6 11l6-6 6 6"></path>
  </svg>
</button>
<style>
  #back-to-top{
    position:fixed;bottom:28px;right:28px;width:46px;height:46px;border-radius:50%;
    background:var(--ink);color:var(--paper);border:1px solid var(--ink);
    display:flex;align-items:center;justify-content:center;cursor:pointer;
    box-shadow:0 4px 14px rgba(0,0,0,.25);opacity:0;visibility:hidden;
    transform:translateY(10px);transition:opacity .25s,transform .25s,visibility .25s,background .15s;
    z-index:1000;
  }
  #back-to-top.visible{opacity:1;visibility:visible;transform:translateY(0)}
  #back-to-top:hover{background:var(--tomato);border-color:var(--tomato)}
</style>
<script>
  (function(){
    var btn = document.getElementById('back-to-top');
    window.addEventListener('scroll', function(){
      if (window.scrollY > 400) {
        btn.classList.add('visible');
      } else {
        btn.classList.remove('visible');
      }
    });
  })();
</script>

</body>
</html>