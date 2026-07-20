<?php
require_once __DIR__ . '/lang.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('mentions_titre') ?> — Home Kitchen Club</title>
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
  .legal a.email-link{color:inherit;text-decoration:none}
  .legal a.email-link:hover{text-decoration:underline}

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
    <a href="index" class="logo"><img src="logo-navbar.svg" alt="Home Kitchen Club"></a>
  </div>
</header>

<div class="legal">
  <h1><?= __('mentions_titre') ?></h1>
  <p class="updated"><?= __('mentions_maj') ?> : <?= date('d/m/Y') ?></p>

<?php if ($lang === 'fr'): ?>

  <h2>1. Éditeur du site</h2>
  <p>
    Le présent site, accessible à l'adresse <strong>https://homekitchenclub.alwaysdata.net</strong>,
    est édité par :
  </p>
  <ul>
    <li>Nom / Raison sociale : <span>Boulloud Nicolas</span></li>
    <li>Statut : <span>Particulier</span></li>
    <li>Adresse : <span>65000</span></li>
    <li>SIRET / SIREN : <span>non applicable (site personnel)</span></li>
    <li>Email de contact : <a class="email-link" href="mailto:boulloud.nicolas@gmail.com">boulloud.nicolas@gmail.com</a></li>
    <li>Directeur de la publication : <span>Boulloud Nicolas</span></li>
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
    l'adresse : <a class="email-link" href="mailto:boulloud.nicolas@gmail.com">boulloud.nicolas@gmail.com</a>.
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
    contacter à l'adresse : <a class="email-link" href="mailto:boulloud.nicolas@gmail.com">boulloud.nicolas@gmail.com</a>.
  </p>

  <?php else: ?>

  <h2>1. Site publisher</h2>
  <p>
    This site, available at <strong>https://homekitchenclub.alwaysdata.net</strong>,
    is published by:
  </p>
  <ul>
    <li>Name: <span>Boulloud Nicolas</span></li>
    <li>Status: <span>Individual</span></li>
    <li>Address: <span>65000 Tarbes, France</span></li>
    <li>Business registration number: <span>not applicable (personal site)</span></li>
    <li>Contact email: <a class="email-link" href="mailto:boulloud.nicolas@gmail.com">boulloud.nicolas@gmail.com</a></li>
    <li>Publication director: <span>Boulloud Nicolas</span></li>
  </ul>

  <h2>2. Hosting</h2>
  <p>
    This site is hosted by:
  </p>
  <ul>
    <li>Host: Alwaysdata SAS</li>
    <li>Address: 91 rue du Faubourg Saint-Honoré, 75008 Paris, France</li>
    <li>Website: <a href="https://www.alwaysdata.com" target="_blank" rel="noopener">www.alwaysdata.com</a></li>
  </ul>

  <h2>3. Intellectual property</h2>
  <p>
    All content on this site (text, recipes, photographs, logos, layout) is protected by
    copyright unless stated otherwise. Any reproduction, representation, modification, or
    distribution, in whole or in part, without the publisher's prior authorization is
    prohibited and may constitute infringement.
  </p>

  <h2>4. Personal data</h2>
  <p>
    This site collects certain personal data when creating a user account (name, email,
    password) and when subscribing to the newsletter. This data is used solely for the
    site's operation (login, account management, newsletter delivery) and is never sold
    or shared with third parties for commercial purposes.
  </p>
  <p>
    Under the General Data Protection Regulation (GDPR), you have the right to access,
    correct, delete, and port your data. To exercise this right, contact us at:
    <a class="email-link" href="mailto:boulloud.nicolas@gmail.com">boulloud.nicolas@gmail.com</a>.
  </p>

  <h2>5. Cookies</h2>
  <p>
    This site may use technical cookies necessary for its operation (in particular, to
    maintain your login session). No advertising or third-party tracking cookies are set
    without your prior consent, where applicable.
  </p>

  <h2>6. Limitation of liability</h2>
  <p>
    The publisher strives to ensure the accuracy of the information published on the site
    (in particular recipes and preparation times) but cannot be held liable for errors,
    omissions, or temporary unavailability of the site.
  </p>

  <h2>7. Governing law</h2>
  <p>
    This legal notice is governed by French law. In the event of a dispute, and failing
    an amicable resolution, French courts shall have sole jurisdiction.
  </p>

  <h2>8. Contact</h2>
  <p>
    For any questions regarding the site or this legal notice, you can contact us at:
    <a class="email-link" href="mailto:boulloud.nicolas@gmail.com">boulloud.nicolas@gmail.com</a>.
  </p>

  <?php endif; ?>
</div>

<footer>
  <div class="wrap">
    <span>© <?= date('Y') ?> Home Kitchen Club</span>
    <div style="display:flex;gap:20px;align-items:center">
      <button type="button" onclick="document.getElementById('contact-modal').showModal()" style="background:none;border:none;color:inherit;text-decoration:none;cursor:pointer;font:inherit;padding:0"><?= __('footer_contact') ?></button>
      <span style="opacity:.5">·</span>
      <a href="mentions-legales" style="text-decoration:none"><?= __('footer_mentions') ?></a>
    </div>
  </div>
</footer>

<dialog id="contact-modal" class="contact-modal">
  <button type="button" class="cm-close" onclick="document.getElementById('contact-modal').close()" aria-label="Fermer">&times;</button>
  <div class="cm-inner">
    <h2><?= __('contact_titre') ?></h2>
    <p class="intro"><?= __('contact_intro') ?></p>

    <div id="contact-cm-msg"></div>

    <form id="contact-form">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

      <label for="cm-nom"><?= __('contact_nom') ?></label>
      <input type="text" id="cm-nom" name="nom" placeholder="<?= __('contact_nom') ?>">

      <label for="cm-email"><?= __('contact_email') ?></label>
      <input type="email" id="cm-email" name="email" required>

      <label for="cm-sujet"><?= __('contact_sujet') ?></label>
      <input type="text" id="cm-sujet" name="sujet" placeholder="<?= __('contact_sujet') ?>">

      <label for="cm-message"><?= __('contact_message') ?></label>
      <textarea id="cm-message" name="message" required maxlength="5000"></textarea>

      <div class="cm-actions">
        <button type="button" class="btn-annuler" onclick="document.getElementById('contact-modal').close()"><?= __('contact_annuler') ?></button>
        <button type="submit" class="btn-envoyer"><?= __('contact_envoyer') ?></button>
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