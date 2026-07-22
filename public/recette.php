<?php
require_once __DIR__ . '/includes/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: index.php');
  exit;
}

$stmt = $pdo->prepare("
    SELECT r.*, c.nom AS cat
    FROM recettes r
    JOIN categories c ON c.id = r.categorie_id
    WHERE r.id = ? AND r.publie = 1
");
$stmt->execute([$id]);
$recette = $stmt->fetch();

if (!$recette) {
  header('Location: index.php');
  exit;
}

$stmt = $pdo->prepare("SELECT nom, quantite FROM ingredients WHERE recette_id = ? ORDER BY ordre");
$stmt->execute([$id]);
$ingredients = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT titre, texte FROM etapes WHERE recette_id = ? ORDER BY numero");
$stmt->execute([$id]);
$etapes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($recette['titre']) ?> — Home Kitchen Club</title>
  <link rel="icon" href="logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Work+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;600&display=swap"
    rel="stylesheet">
  <link rel="preload" href="assets/style.css" as="style">
  <link rel="stylesheet" href="assets/style.css" media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="assets/style.css">
  </noscript>
  <style>
    .ing-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px dashed var(--line);
      padding-bottom: 12px;
      margin-bottom: 16px
    }

    .ing-head h3 {
      margin: 0;
      border: none;
      padding: 0
    }

    .portion-stepper {
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: var(--font-mono);
      font-size: .82rem;
      border: 2px solid var(--ink);
      border-radius: 999px;
      padding: 4px 6px
    }

    .portion-stepper button {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      border: 1px solid var(--ink);
      background: none;
      font-size: 1rem;
      cursor: pointer;
      line-height: 1
    }

    .portion-stepper button:hover {
      background: var(--ink);
      color: var(--paper)
    }

    .ing-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px
    }

    .ing-card {
      text-align: center
    }

    .ing-icon {
      width: 56px;
      height: 56px;
      margin: 0 auto 8px;
      border-radius: 50%;
      background: var(--paper-2);
      border: 1px solid var(--line);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }

    .ing-nom {
      font-size: .85rem;
      margin-bottom: 2px
    }

    .ing-qte {
      font-family: var(--font-mono);
      font-weight: 700;
      font-size: .9rem
    }

    .ing-qte .qte-unite {
      font-weight: 400;
      text-transform: lowercase
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
    }

    @media (max-width:860px) {
      .logo .dot {
        display: inline-block
      }
    }
  </style>
</head>

<body>

  <header class="site-header">
    <div class="wrap">
      <a href="index" class="logo"><img src="/image?f=logo-navbar.svg" alt="Home Kitchen Club"></a>
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

  <section class="recipe-hero">
    <div class="wrap" style="display:block">
      <span class="tag"
        style="color:var(--tomato);font-family:var(--font-mono);text-transform:uppercase;font-size:.78rem;letter-spacing:.08em">
        <?= htmlspecialchars($recette['cat']) ?>
      </span>
      <h1><?= htmlspecialchars($recette['titre']) ?></h1>

      <?php if (!empty($recette['image_url'])): ?>
        <div class="thumb-big" style="width:100%;margin-top:16px">
          <img src="image?f=<?= urlencode($recette['image_url']) ?>" alt="<?= htmlspecialchars($recette['titre']) ?>"
            loading="eager" fetchpriority="high" decoding="async">
        </div>
      <?php else: ?>
        <div class="thumb-big" style="width:100%;margin-top:16px">🍽</div>
      <?php endif; ?>

      <p class="lede" style="max-width:48ch;color:#3a3a35;margin-top:24px"><?= htmlspecialchars($recette['intro']) ?>
      </p>
      <div class="recipe-meta">
        <div><b><?= htmlspecialchars($recette['temps_total']) ?></b>Temps total</div>
        <div><b><?= htmlspecialchars($recette['portions']) ?></b>Portions</div>
        <div><b><?= htmlspecialchars($recette['difficulte']) ?></b>Difficulté</div>
      </div>
    </div>
  </section>

  <div class="wrap">
    <div class="recipe-body">
      <aside class="ingredients">
        <div class="ing-head">
          <h3>Ingrédients</h3>
          <?php if (is_numeric($recette['portions'])): ?>
            <div class="portion-stepper">
              <button type="button" id="portion-moins" aria-label="Moins de portions">−</button>
              <span><b id="portion-count"><?= (int) $recette['portions'] ?></b> pers.</span>
              <button type="button" id="portion-plus" aria-label="Plus de portions">+</button>
            </div>
          <?php endif; ?>
        </div>
        <div class="ing-grid">
          <?php foreach ($ingredients as $ing): ?>
            <?php
            preg_match('/^([\d.,]+)\s*(.*)$/', trim($ing['quantite']), $m);
            $qte_nombre = isset($m[1]) ? str_replace(',', '.', $m[1]) : '';
            $qte_unite = isset($m[2]) ? $m[2] : '';
            ?>
            <div class="ing-card">
              <div class="ing-icon">🥄</div>
              <div class="ing-nom"><?= htmlspecialchars($ing['nom']) ?></div>
              <div class="ing-qte">
                <?php if ($qte_nombre !== '' && is_numeric($qte_nombre)): ?>
                  <span class="qte-nombre"
                    data-base="<?= htmlspecialchars($qte_nombre) ?>"><?= htmlspecialchars($qte_nombre) ?></span><span
                    class="qte-unite"> <?= htmlspecialchars($qte_unite) ?></span>
                <?php else: ?>
                  <span><?= htmlspecialchars($ing['quantite']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </aside>

      <div class="steps">
        <h3>Préparation</h3>
        <ol>
          <?php foreach ($etapes as $etape): ?>
            <li>
              <strong style="font-family:var(--font-display);font-size:1.1rem;display:block;margin-bottom:6px;">
                <?= htmlspecialchars($etape['titre']) ?>
              </strong>
              <span style="color:#4c4c46"><?= htmlspecialchars($etape['texte']) ?></span>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </div>

  <footer>
    <div class="wrap">
      <span>© <?= date('Y') ?> Home Kitchen Club</span>
      <a href="index">← Retour aux recettes</a>
    </div>
  </footer>

  <?php if (is_numeric($recette['portions'])): ?>
    <script>
      (function () {
        const base = <?= (int) $recette['portions'] ?>;
        let current = base;
        const countEl = document.getElementById('portion-count');
        const nombres = document.querySelectorAll('.qte-nombre');

        function maj() {
          countEl.textContent = current;
          const ratio = current / base;
          nombres.forEach(el => {
            const baseVal = parseFloat(el.dataset.base);
            let val = baseVal * ratio;
            val = Math.round(val * 100) / 100;
            el.textContent = (val % 1 === 0) ? val : val.toFixed(2).replace(/0$/, '').replace(/\.$/, '');
          });
        }

        document.getElementById('portion-moins').addEventListener('click', () => {
          if (current > 1) { current--; maj(); }
        });
        document.getElementById('portion-plus').addEventListener('click', () => {
          current++; maj();
        });
      })();
    </script>
  <?php endif; ?>

</body>

</html>