<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/image-utils.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
  header('Location: dashboard');
  exit;
}

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$erreurs = [];
$succes = false;

function slugify($texte)
{
  $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
  $texte = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $texte));
  return trim($texte, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $erreurs[] = "Jeton de sécurité invalide, veuillez réessayer.";
  }

  $titre = trim($_POST['titre'] ?? '');
  $categorie_id = (int) ($_POST['categorie_id'] ?? 0);
  $intro = trim($_POST['intro'] ?? '');
  $temps = trim($_POST['temps_total'] ?? '');
  $portions = trim($_POST['portions'] ?? '');
  $diff = $_POST['difficulte'] ?? 'Facile';
  $nouveau = isset($_POST['nouveau']) ? 1 : 0;
  $publie = 1;

  $ing_noms = $_POST['ing_nom'] ?? [];
  $ing_qtes = $_POST['ing_qte'] ?? [];
  $etape_titres = $_POST['etape_titre'] ?? [];
  $etape_textes = $_POST['etape_texte'] ?? [];

  $image_nom = $_POST['image_actuelle'] ?? null;
  if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $extensions_ok = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $extensions_ok, true)) {
      $erreurs[] = "Format d'image non supporté (jpg, png ou webp uniquement).";
    } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
      $erreurs[] = "L'image dépasse la taille maximale de 5 Mo.";
    } else {
      $dimensions = @getimagesize($_FILES['image']['tmp_name']);
      if (!$dimensions) {
        $erreurs[] = "Fichier image invalide.";
      } elseif ($dimensions[0] <= $dimensions[1]) {
        $erreurs[] = "L'image doit être au format horizontal (paysage).";
      } else {
        $dossier_upload = __DIR__ . '/uploads/';
        if (!is_dir($dossier_upload))
          mkdir($dossier_upload, 0755, true);
        $nom_base = uniqid('recette_');
        if (generer_variantes_image($_FILES['image']['tmp_name'], $ext, $dossier_upload, $nom_base)) {
          $ancienne_image = $image_nom;
          $image_nom = $nom_base . '.avif';
          // Supprime l'ancienne image (grande + thumb) devenue orpheline
          if ($ancienne_image) {
            supprimer_variantes_image($dossier_upload, $ancienne_image);
          }
        } else {
          $erreurs[] = "Erreur lors du téléversement de l'image.";
        }
      }
    }
  }

  if ($titre === '')
    $erreurs[] = "Le titre est obligatoire.";
  if ($categorie_id <= 0)
    $erreurs[] = "Merci de choisir une catégorie.";
  if (!in_array($diff, ['Facile', 'Moyen', 'Difficile'], true))
    $erreurs[] = "Difficulté invalide.";

  if (!$erreurs) {
    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare("
                UPDATE recettes
                SET titre = ?, categorie_id = ?, intro = ?, temps_total = ?, portions = ?, difficulte = ?, nouveau = ?, publie = ?, image_url = ?
                WHERE id = ?
            ");
      $stmt->execute([$titre, $categorie_id, $intro, $temps, $portions, $diff, $nouveau, $publie, $image_nom, $id]);

      $pdo->prepare("DELETE FROM ingredients WHERE recette_id = ?")->execute([$id]);
      $stmt_ing = $pdo->prepare("INSERT INTO ingredients (recette_id, nom, quantite, ordre) VALUES (?, ?, ?, ?)");
      foreach ($ing_noms as $idx => $nom) {
        $nom = trim($nom);
        if ($nom === '')
          continue;
        $stmt_ing->execute([$id, $nom, trim($ing_qtes[$idx] ?? ''), $idx + 1]);
      }

      $pdo->prepare("DELETE FROM etapes WHERE recette_id = ?")->execute([$id]);
      $stmt_etape = $pdo->prepare("INSERT INTO etapes (recette_id, numero, titre, texte) VALUES (?, ?, ?, ?)");
      $num = 1;
      foreach ($etape_titres as $idx => $et_titre) {
        $et_titre = trim($et_titre);
        $et_texte = trim($etape_textes[$idx] ?? '');
        if ($et_titre === '' && $et_texte === '')
          continue;
        $stmt_etape->execute([$id, $num++, $et_titre, $et_texte]);
      }

      $pdo->commit();
      header('Location: dashboard');
      exit;
    } catch (Exception $e) {
      $pdo->rollBack();
      $erreurs[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
  }
}

// Charge la recette (état actuel en base, ou dernière saisie si erreurs de validation)
$stmt = $pdo->prepare("SELECT * FROM recettes WHERE id = ?");
$stmt->execute([$id]);
$recette = $stmt->fetch();
if (!$recette) {
  header('Location: dashboard');
  exit;
}

$categories = $pdo->query("SELECT id, nom FROM categories ORDER BY ordre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $erreurs) {
  // Réaffiche ce que l'admin vient de saisir plutôt que la version en base
  $recette['titre'] = $_POST['titre'] ?? $recette['titre'];
  $recette['categorie_id'] = $_POST['categorie_id'] ?? $recette['categorie_id'];
  $recette['intro'] = $_POST['intro'] ?? $recette['intro'];
  $recette['temps_total'] = $_POST['temps_total'] ?? $recette['temps_total'];
  $recette['portions'] = $_POST['portions'] ?? $recette['portions'];
  $recette['difficulte'] = $_POST['difficulte'] ?? $recette['difficulte'];
  $recette['nouveau'] = isset($_POST['nouveau']) ? 1 : 0;
  $ingredients = [];
  foreach (($_POST['ing_nom'] ?? []) as $idx => $nom) {
    $ingredients[] = ['nom' => $nom, 'quantite' => $_POST['ing_qte'][$idx] ?? ''];
  }
  $etapes = [];
  foreach (($_POST['etape_titre'] ?? []) as $idx => $t) {
    $etapes[] = ['titre' => $t, 'texte' => $_POST['etape_texte'][$idx] ?? ''];
  }
} else {
  $stmt = $pdo->prepare("SELECT nom, quantite FROM ingredients WHERE recette_id = ? ORDER BY ordre");
  $stmt->execute([$id]);
  $ingredients = $stmt->fetchAll();

  $stmt = $pdo->prepare("SELECT titre, texte FROM etapes WHERE recette_id = ? ORDER BY numero");
  $stmt->execute([$id]);
  $etapes = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier — <?= htmlspecialchars($recette['titre']) ?> — Home Kitchen Club</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    /* Champs "invisibles" qui se fondent dans le design de la vue publique */
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
      -moz-appearance: none;
      appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231E2A1F' stroke-width='2'><path d='M6 9l6 6 6-6'/></svg>");
      background-repeat: no-repeat;
      background-position: right 4px center;
      background-size: 16px;
      padding-right: 24px;
    }

    textarea.edit-field {
      resize: vertical;
      line-height: inherit
    }

    .edit-title {
      font-family: var(--font-display);
      font-size: 2.6rem;
      letter-spacing: -0.01em;
      margin: 10px 0 14px
    }

    .edit-cat {
      font-family: var(--font-mono);
      text-transform: uppercase;
      font-size: .78rem;
      letter-spacing: .08em;
      color: var(--tomato);
      font-weight: 600;
      border: none;
      background: transparent
    }

    .edit-lede {
      font-size: 1.08rem;
      max-width: 48ch;
      color: #3a3a35
    }

    .meta-input {
      font-family: var(--font-display);
      font-size: 1.3rem;
      width: 100%
    }

    .ing-row {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      padding: 8px 0;
      border-bottom: 1px dotted var(--line);
      align-items: center
    }

    .ing-row input {
      font-size: .92rem
    }

    .ing-row input.qty {
      max-width: 110px;
      text-align: right;
      font-family: var(--font-mono);
      color: var(--sage)
    }

    .step-block {
      position: relative;
      padding: 0 0 28px 52px;
      margin-bottom: 6px
    }

    .step-title-input {
      font-family: var(--font-display);
      font-size: 1.4rem;
      margin-bottom: 6px
    }

    .step-text-input {
      color: #4c4c46;
      min-height: 70px
    }

    .step-num {
      position: absolute;
      left: 0;
      top: 0;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 2px solid var(--ink);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-mono);
      font-weight: 700;
      background: var(--paper)
    }

    .remove-row {
      background: none;
      border: 1px solid var(--tomato);
      color: var(--tomato);
      padding: 4px 10px;
      border-radius: 999px;
      font-family: var(--font-mono);
      font-size: .7rem;
      cursor: pointer;
      white-space: nowrap
    }

    .remove-row:hover {
      background: var(--tomato);
      color: #fff
    }

    .add-mini {
      background: none;
      border: 1px solid var(--ink);
      border-radius: 999px;
      padding: 8px 16px;
      font-family: var(--font-mono);
      font-size: .75rem;
      cursor: pointer;
      margin-top: 10px
    }

    .add-mini:hover {
      background: var(--ink);
      color: var(--paper)
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

    .checks-inline {
      display: flex;
      gap: 20px;
      align-items: center
    }

    .checks-inline label {
      display: flex;
      align-items: center;
      gap: 6px
    }

    .save-bar {
      position: sticky;
      bottom: 0;
      background: var(--paper);
      border-top: 2px solid var(--ink);
      padding: 16px 0;
      margin-top: 20px
    }

    .save-bar .wrap {
      display: flex;
      justify-content: space-between;
      align-items: center
    }

    .btn-save {
      background: var(--ink);
      color: var(--paper);
      padding: 13px 28px;
      border-radius: 999px;
      font-weight: 600;
      border: 2px solid var(--ink);
      cursor: pointer
    }

    .btn-save:hover {
      background: var(--tomato);
      border-color: var(--tomato)
    }

    .thumb-upload {
      cursor: pointer;
      flex-direction: column;
      gap: 8px;
      color: rgba(30, 42, 31, .55);
      transition: background .15s;
    }

    .thumb-upload:hover {
      background: rgba(30, 42, 31, .08)
    }

    .thumb-upload img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .erreurs {
      background: #f8d7da;
      color: #8a1c25;
      padding: 14px 18px;
      border-radius: 4px;
      margin: 20px 0
    }

    .erreurs ul {
      margin: 0;
      padding-left: 18px
    }
  </style>
</head>

<body>

  <header class="site-header">
    <div class="wrap">
      <a href="dashboard" class="logo"><img src="/image?f=logo-navbar.svg" alt="Home Kitchen Club"></a>
      <div style="display:flex;gap:10px;margin-left:auto">
        <a href="/user/logout" class="nav-cta">Déconnexion</a>
      </div>
    </div>
  </header>

  <div class="wrap" style="padding-top:20px">
    <?php if ($erreurs): ?>
      <div class="erreurs">
        <ul><?php foreach ($erreurs as $e): ?>
            <li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>

  <form method="post" id="form-modifier" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <section class="recipe-hero">
      <div class="wrap" style="display:block">
        <select name="categorie_id" class="edit-field edit-cat">
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($recette['categorie_id'] == $c['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <input type="text" name="titre" class="edit-field edit-title" value="<?= htmlspecialchars($recette['titre']) ?>"
          placeholder="Titre de la recette">

        <label for="image-input" class="thumb-big thumb-upload" id="thumb-upload" style="width:100%;margin-top:16px">
          <span id="thumb-placeholder" style="<?= !empty($recette['image_url']) ? 'display:none' : '' ?>">
            <span style="font-size:2rem;display:block">📷</span>
            <span
              style="font-family:var(--font-mono);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em">Ajouter
              une image</span>
          </span>
          <img id="thumb-preview"
            src="<?= !empty($recette['image_url']) ? '/image?f=' . urlencode($recette['image_url']) : '' ?>" alt=""
            style="<?= !empty($recette['image_url']) ? 'display:block;object-fit:contain' : 'display:none' ?>">
        </label>
        <input type="file" id="image-input" name="image" accept="image/png,image/jpeg,image/webp" style="display:none">
        <input type="hidden" name="image_actuelle" value="<?= htmlspecialchars($recette['image_url'] ?? '') ?>">

        <textarea name="intro" class="edit-field edit-lede" rows="2" placeholder="Introduction / description"
          style="margin-top:24px"><?= htmlspecialchars($recette['intro']) ?></textarea>

        <div class="recipe-meta">
          <div>
            <input type="text" name="temps_total" class="edit-field meta-input"
              value="<?= htmlspecialchars($recette['temps_total']) ?>" placeholder="ex : 45 min">
            Temps total
          </div>
          <div>
            <input type="text" name="portions" class="edit-field meta-input"
              value="<?= htmlspecialchars($recette['portions']) ?>" placeholder="ex : 4">
            Portions
          </div>
          <div>
            <select name="difficulte" class="edit-field meta-input">
              <?php foreach (['Facile', 'Moyen', 'Difficile'] as $d): ?>
                <option value="<?= $d ?>" <?= ($recette['difficulte'] === $d) ? 'selected' : '' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
            Difficulté
          </div>
        </div>

        <div class="checks-inline" style="margin-top:16px;font-family:var(--font-mono);font-size:.8rem">
          <label><input type="checkbox" name="nouveau" <?= !empty($recette['nouveau']) ? 'checked' : '' ?>> Marquer «
            Nouveau »</label>
        </div>
      </div>
    </section>

    <div class="wrap">
      <div class="recipe-body">
        <aside class="ingredients">
          <h3>Ingrédients</h3>
          <div id="ingredients-list">
            <?php foreach ($ingredients as $ing): ?>
              <div class="ing-row">
                <input type="text" name="ing_nom[]" class="edit-field" value="<?= htmlspecialchars($ing['nom']) ?>"
                  placeholder="Nom de l'ingrédient">
                <input type="text" name="ing_qte[]" class="edit-field qty"
                  value="<?= htmlspecialchars($ing['quantite']) ?>" placeholder="Quantité">
                <button type="button" class="remove-row" onclick="this.parentElement.remove()">✕</button>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-mini" onclick="ajouterIngredient()">+ Ajouter un ingrédient</button>
        </aside>

        <div class="steps">
          <h3>Préparation</h3>
          <div id="etapes-list">
            <?php $n = 1;
            foreach ($etapes as $etape): ?>
              <div class="step-block">
                <span class="step-num"><?= $n++ ?></span>
                <input type="text" name="etape_titre[]" class="edit-field step-title-input"
                  value="<?= htmlspecialchars($etape['titre']) ?>" placeholder="Titre de l'étape">
                <textarea name="etape_texte[]" class="edit-field step-text-input"
                  placeholder="Détail de l'étape..."><?= htmlspecialchars($etape['texte']) ?></textarea>
                <div style="text-align:right;margin-top:6px">
                  <button type="button" class="remove-row"
                    onclick="this.closest('.step-block').remove(); renumeroterEtapes()">✕ Retirer l'étape</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-mini" onclick="ajouterEtape()">+ Ajouter une étape</button>
        </div>
      </div>
    </div>

    <div class="save-bar">
      <div class="wrap">
        <a href="dashboard">← Annuler</a>
        <button type="submit" class="btn-save">Enregistrer les modifications</button>
      </div>
    </div>

  </form>

  <footer>
    <div class="wrap">
      <span>© <?= date('Y') ?> Home Kitchen Club</span>
      <a href="dashboard">← Retour au tableau de bord</a>
    </div>
  </footer>

  <script>
    document.getElementById('image-input').addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (ev) {
        const testImg = new Image();
        testImg.onload = function () {
          if (testImg.width <= testImg.height) {
            alert("L'image doit être au format horizontal (paysage).");
            e.target.value = '';
            return;
          }
          document.getElementById('thumb-placeholder').style.display = 'none';
          const img = document.getElementById('thumb-preview');
          img.src = ev.target.result;
          img.style.display = 'block';
        };
        testImg.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    });

    function ajouterIngredient() {
      const div = document.createElement('div');
      div.className = 'ing-row';
      div.innerHTML = `
    <input type="text" name="ing_nom[]" class="edit-field" placeholder="Nom de l'ingrédient">
    <input type="text" name="ing_qte[]" class="edit-field qty" placeholder="Quantité">
    <button type="button" class="remove-row" onclick="this.parentElement.remove()">✕</button>
  `;
      document.getElementById('ingredients-list').appendChild(div);
    }

    function renumeroterEtapes() {
      document.querySelectorAll('#etapes-list .step-num').forEach((el, i) => el.textContent = i + 1);
    }

    function ajouterEtape() {
      const div = document.createElement('div');
      div.className = 'step-block';
      const num = document.querySelectorAll('#etapes-list .step-block').length + 1;
      div.innerHTML = `
    <span class="step-num">${num}</span>
    <input type="text" name="etape_titre[]" class="edit-field step-title-input" placeholder="Titre de l'étape">
    <textarea name="etape_texte[]" class="edit-field step-text-input" placeholder="Détail de l'étape..."></textarea>
    <div style="text-align:right;margin-top:6px">
      <button type="button" class="remove-row" onclick="this.closest('.step-block').remove(); renumeroterEtapes()">✕ Retirer l'étape</button>
    </div>
  `;
      document.getElementById('etapes-list').appendChild(div);
    }
  </script>

</body>

</html>