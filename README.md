# Home Kitchen Club

Site de recettes que j'ai codé en PHP pur pour progresser en dev web (pas de framework, je voulais comprendre ce qui se passe sous le capot). Il y a un espace public pour consulter les recettes, un système de compte, et un dashboard admin pour tout gérer.

## Ce que ça fait

- Liste de recettes filtrable par catégorie, avec fiche détaillée par recette (ingrédients, étapes, temps, difficulté)
- Ajustement des portions en direct sur la fiche recette (recalcul des quantités en JS)
- Comptes utilisateurs : inscription / connexion, avec option "rester connecté" (remember-me sécurisé par token)
- Dashboard admin pour créer, modifier et supprimer des recettes
- Upload d'images, converties automatiquement en AVIF + génération de miniatures
- Formulaire de contact en popup (AJAX, protégé par jeton CSRF)
- Site bilingue FR/EN, détecté automatiquement selon la langue du navigateur

## Stack

PHP natif, MySQL/PDO, HTML/CSS/JS vanilla. Pas de framework, pas de build tool.
Les images passent par GD pour la conversion AVIF (nécessite PHP 8.1+).

## Structure du projet

```
homekitchenclub/
├── public/                  # Racine web (document root)
│   ├── index.php            # Page d'accueil (liste des recettes)
│   ├── recette.php          # Détail d'une recette
│   ├── contact.php          # Formulaire de contact
│   ├── contact_envoyer.php  # Traitement du formulaire de contact
│   ├── mentions-legales.php
│   ├── .htaccess
│   ├── logo.png
│   └── logo-navbar.svg
│
├── admin/                   # Gestion des recettes (espace connecté)
│   ├── dashboard.php
│   ├── ajouter.php
│   └── modifier.php
│
├── utilisateur/             # Authentification
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── includes/                # Logique métier / technique (non accessible en URL directe)
│   ├── db.php                # Connexion PDO à la base de données
│   ├── lang.php              # Gestion de la langue (fr/en)
│   ├── auth_check.php        # Vérification de session utilisateur
│   └── image-utils.php       # Traitement / redimensionnement des images
│
├── lang/                    # Fichiers de traduction
│   ├── fr.php
│   └── en.php
│
├── assets/
│   └── css/
│       └── style.css
│
└── uploads/                 # Images uploadées par les utilisateurs
    └── recettes/
```

## Sécurité

Quelques trucs que j'ai mis en place en apprenant le sujet :

- Mots de passe hashés avec `password_hash` / `password_verify`
- Requêtes SQL préparées (PDO) partout, pas de concaténation de requêtes
- Protection CSRF sur les formulaires sensibles (contact, création/édition de recette)
- Cookie "remember me" basé sur un couple sélecteur/validateur hashé (pas de token en clair côté serveur), avec rotation à chaque utilisation

## Internationalisation

Le site détecte la langue du navigateur au premier chargement et affiche le contenu en français ou en anglais en conséquence. La logique est dans `lang.php`, les textes fixes dans `fr.php` / `en.php`, et les recettes ont des colonnes `_en` en base avec repli automatique sur le français si la traduction n'est pas encore renseignée.

## En ligne

- Site : [homekitchenclub.alwaysdata.net](https://homekitchenclub.alwaysdata.net/)
- [Mentions légales](https://homekitchenclub.alwaysdata.net/mentions-legales)

## Auteur

Nicolas Boulloud — [LinkedIn](https://www.linkedin.com/in/nicolas-boulloud/)

## Licence

© 2026 Nicolas Boulloud. Tous droits réservés.
