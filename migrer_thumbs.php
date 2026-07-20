<?php
/**
 * Script de migration ponctuel : génère les fichiers _thumb.avif manquants
 * pour toutes les recettes existantes, à partir de leur image "grande" déjà en ligne.
 *
 * À exécuter UNE SEULE FOIS en visitant migrer_thumbs.php dans le navigateur,
 * puis à SUPPRIMER du serveur.
 */
require_once __DIR__ . '/auth_check.php'; // sécurité : réservé à l'admin connecté
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/image-utils.php';

$dossier_upload = __DIR__ . '/uploads/';

$recettes = $pdo->query("SELECT id, titre, image_url FROM recettes WHERE image_url IS NOT NULL AND image_url != ''")->fetchAll();

$resultats = [];

foreach ($recettes as $r) {
    $image_url = $r['image_url'];
    $chemin_large = $dossier_upload . $image_url;
    $base = preg_replace('/\.avif$/', '', $image_url);
    $chemin_thumb = $dossier_upload . $base . '_thumb.avif';

    if (is_file($chemin_thumb)) {
        $resultats[] = ['titre' => $r['titre'], 'statut' => 'déjà présent'];
        continue;
    }

    if (!is_file($chemin_large)) {
        $resultats[] = ['titre' => $r['titre'], 'statut' => 'ERREUR : image source introuvable (' . $image_url . ')'];
        continue;
    }

    // La source est déjà en AVIF : on la décode avec imagecreatefromavif si dispo
    if (function_exists('imagecreatefromavif')) {
        $source = @imagecreatefromavif($chemin_large);
    } else {
        $source = false;
    }

    if (!$source) {
        $resultats[] = ['titre' => $r['titre'], 'statut' => 'ERREUR : impossible de lire l\'AVIF source (GD sans support avif ?)'];
        continue;
    }

    $largeur_src = imagesx($source);
    $hauteur_src = imagesy($source);
    $max_largeur = 800;

    if ($largeur_src > $max_largeur) {
        $largeur_dest = $max_largeur;
        $hauteur_dest = (int) round($hauteur_src * ($max_largeur / $largeur_src));
        $thumb = imagecreatetruecolor($largeur_dest, $hauteur_dest);
        imagepalettetotruecolor($thumb);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefilledrectangle($thumb, 0, 0, $largeur_dest, $hauteur_dest, $transparent);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $largeur_dest, $hauteur_dest, $largeur_src, $hauteur_src);
        imagedestroy($source);
    } else {
        $thumb = $source;
        imagepalettetotruecolor($thumb);
        imagealphablending($thumb, true);
        imagesavealpha($thumb, true);
    }

    $ok = imageavif($thumb, $chemin_thumb, 65);
    imagedestroy($thumb);

    $resultats[] = ['titre' => $r['titre'], 'statut' => $ok ? 'thumb généré ✅' : 'ERREUR lors de la génération'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Migration thumbs</title></head>
<body style="font-family:sans-serif;padding:24px">
<h1>Migration des thumbs</h1>
<p><strong>⚠️ Supprimez ce fichier du serveur une fois la migration terminée.</strong></p>
<table border="1" cellpadding="8" cellspacing="0">
<tr><th>Recette</th><th>Statut</th></tr>
<?php foreach ($resultats as $r): ?>
<tr><td><?= htmlspecialchars($r['titre']) ?></td><td><?= htmlspecialchars($r['statut']) ?></td></tr>
<?php endforeach; ?>
</table>
<p><?= count($resultats) ?> recette(s) traitée(s).</p>
</body>
</html>
