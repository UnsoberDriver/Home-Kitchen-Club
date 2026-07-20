<?php
/**
 * Redimensionne une image (en conservant le ratio) si elle dépasse $max_largeur,
 * puis l'enregistre en AVIF à $destination.
 * Retourne true en cas de succès, false sinon.
 */
function redimensionner_et_convertir_avif(string $tmp_path, string $ext_source, string $destination, int $max_largeur, int $qualite = 65): bool
{
    if (!function_exists('imageavif')) {
        // GD sans support AVIF : on ne peut pas convertir (nécessite PHP >= 8.1
        // avec GD compilé --with-avif)
        return false;
    }

    switch ($ext_source) {
        case 'jpg':
        case 'jpeg':
            $source = @imagecreatefromjpeg($tmp_path);
            break;
        case 'png':
            $source = @imagecreatefrompng($tmp_path);
            break;
        case 'webp':
            $source = @imagecreatefromwebp($tmp_path);
            break;
        default:
            return false;
    }

    if (!$source) {
        return false;
    }

    $largeur_src = imagesx($source);
    $hauteur_src = imagesy($source);

    // Ne redimensionne que si l'image dépasse la largeur max (pas d'agrandissement)
    if ($largeur_src > $max_largeur) {
        $largeur_dest = $max_largeur;
        $hauteur_dest = (int) round($hauteur_src * ($max_largeur / $largeur_src));

        $image = imagecreatetruecolor($largeur_dest, $hauteur_dest);

        // Préserve la transparence pour les PNG
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, $largeur_dest, $hauteur_dest, $transparent);

        imagecopyresampled(
            $image, $source,
            0, 0, 0, 0,
            $largeur_dest, $hauteur_dest,
            $largeur_src, $hauteur_src
        );
        imagedestroy($source);
    } else {
        // Image déjà assez petite : on la garde telle quelle
        $image = $source;
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }

    $ok = imageavif($image, $destination, $qualite);
    imagedestroy($image);

    return $ok;
}

/**
 * Génère les deux variantes (grande + thumb) d'une image uploadée à partir
 * du même fichier temporaire source, et les enregistre dans $dossier_upload.
 * Retourne true si les deux conversions ont réussi.
 */
function generer_variantes_image(string $tmp_path, string $ext_source, string $dossier_upload, string $nom_base): bool
{
    $ok_large = redimensionner_et_convertir_avif($tmp_path, $ext_source, $dossier_upload . $nom_base . '.avif', 1600);
    $ok_thumb = redimensionner_et_convertir_avif($tmp_path, $ext_source, $dossier_upload . $nom_base . '_thumb.avif', 800);
    return $ok_large && $ok_thumb;
}

/**
 * Supprime les deux variantes (grande + thumb) associées à un nom de fichier
 * image_url stocké en base (ex: "recette_abc123.avif").
 */
function supprimer_variantes_image(string $dossier_upload, string $image_url): void
{
    $base = preg_replace('/\.avif$/', '', $image_url);
    $large = $dossier_upload . $base . '.avif';
    $thumb = $dossier_upload . $base . '_thumb.avif';
    if (is_file($large)) @unlink($large);
    if (is_file($thumb)) @unlink($thumb);
}