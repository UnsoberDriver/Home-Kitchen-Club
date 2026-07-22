<?php
function charger_env(string $chemin): void
{
    if (!is_readable($chemin)) {
        error_log("Fichier .env introuvable ou illisible : $chemin");
        die('Erreur de configuration du serveur.');
    }

    foreach (file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne === '' || str_starts_with($ligne, '#')) {
            continue;
        }
        [$cle, $valeur] = array_pad(explode('=', $ligne, 2), 2, '');
        $cle = trim($cle);
        $valeur = trim($valeur);
        if ($cle !== '' && getenv($cle) === false) {
            putenv("$cle=$valeur");
        }
    }
}

// Adapte ce chemin à l'emplacement réel de ton .env, HORS de la racine web.
// Exemple alwaysdata : /home/homekitchenclub/.env
charger_env(__DIR__ . '/../../.env');

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    error_log('Erreur de connexion BDD : ' . $e->getMessage());
    die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
}