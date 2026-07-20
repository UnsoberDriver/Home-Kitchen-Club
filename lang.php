<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function detecter_langue(): string
{
    // Langue déjà déterminée pour cette session
    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], ['fr', 'en'], true)) {
        return $_SESSION['lang'];
    }

    $langue = 'en'; // par défaut : anglais pour tout ce qui n'est pas explicitement français

    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // Ex d'en-tête : "fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7"
        $langues = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (!empty($langues[0])) {
            $premiere = strtolower(trim(explode(';', $langues[0])[0]));
            if (str_starts_with($premiere, 'fr')) {
                $langue = 'fr';
            }
        }
    }

    $_SESSION['lang'] = $langue;
    return $langue;
}

$GLOBALS['lang'] = detecter_langue();

$GLOBALS['i18n'] = require __DIR__ . '/' . $GLOBALS['lang'] . '.php';

/**
 * Retourne la chaîne traduite pour la clé donnée.
 * Si la clé n'existe pas, retourne la clé elle-même (pour repérer les oublis).
 */
function __(string $cle): string
{
    return $GLOBALS['i18n'][$cle] ?? $cle;
}

/**
 * Retourne la valeur dans la langue courante pour un champ de la base
 * qui a une variante _en (ex: champ_fr($recette, 'titre') lit 'titre' ou 'titre_en').
 * Si la traduction anglaise est vide, on retourne le français en repli.
 */
function champ_langue(array $ligne, string $champ): string
{
    if ($GLOBALS['lang'] === 'en' && !empty($ligne[$champ . '_en'])) {
        return $ligne[$champ . '_en'];
    }
    return $ligne[$champ] ?? '';
}