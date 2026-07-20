# Home Kitchen Club

Site web de recettes de cuisine développé en PHP natif, avec espace membre et back-office d'administration pour la gestion du contenu.  
Projet personnel réalisé pour apprendre/pratiquer le PHP « from scratch », MySQL/PDO, et un peu de front léger (HTML/CSS/JS vanilla).

## Fonctionnalités

- **Catalogue de recettes** filtrable par catégorie, avec fiches détaillées (ingrédients, étapes, temps, difficulté)
- **Portions ajustables** en direct sur la fiche recette (recalcul des quantités en JS)
- **Comptes utilisateurs** : inscription / connexion, avec option « rester connecté » (remember-me sécurisé par token)
- **Espace administrateur** : tableau de bord pour créer, modifier et supprimer des recettes
- **Upload d'images** avec redimensionnement et conversion automatique en AVIF (+ génération de miniatures)
- **Formulaire de contact** (page dédiée + popup modale en AJAX), protégé par jeton CSRF
- **Page mentions légales**

## Stack technique

- PHP natif (pas de framework)
- MySQL via PDO (requêtes préparées)
- HTML / CSS / JavaScript vanilla (pas de dépendance front)
- Images converties en AVIF via GD (`imageavif`, PHP ≥ 8.1)

## Structure du projet

```
.
├── index.php                # Page d'accueil (liste des recettes, filtres par catégorie)
├── recette.php               # Fiche détail d'une recette
├── login.php / register.php  # Connexion / inscription
├── logout.php                # Déconnexion
├── auth_check.php            # Middleware d'authentification pour les pages admin
├── dashboard.php              # Back-office : liste/édition rapide des recettes
├── ajouter.php                # Back-office : création d'une recette
├── modifier.php               # Back-office : édition complète d'une recette
├── contact.php                # Page de contact
├── contact_envoyer.php        # Endpoint AJAX du formulaire de contact
├── mentions-legales.php       # Page légale
├── db.php                     # Connexion PDO + chargement du .env
├── image-utils.php            # Redimensionnement / conversion AVIF des images
├── migrer_thumbs.php          # Script ponctuel de migration des miniatures
├── creer_admin.php             # Script ponctuel de création du premier compte admin
├── register_admin.php         # Variante de création de compte admin (non liée publiquement)
└── style.css                  # Styles globaux du site
```

## Sécurité

- Mots de passe hashés avec `password_hash` / `password_verify`
- Requêtes SQL préparées (PDO) partout
- Protection CSRF sur les formulaires sensibles (contact, création/édition de recette)
- Cookie « remember me » basé sur un couple sélecteur/validateur hashé (pas de token en clair côté serveur), avec rotation à chaque utilisation
- Scripts d'initialisation (`creer_admin.php`, `register_admin.php`, `migrer_thumbs.php`) à supprimer après usage

## Internationalisation
Le site détecte automatiquement la langue du navigateur :

- 🇫🇷 Français si le navigateur est en français
- 🇬🇧 Anglais dans tous les autres cas

Les traductions sont gérées via lib/translations.ts et le hook useLanguage().

## En ligne
- URL du site : [https://Homekitchhenclub.net](https://homekitchenclub.alwaysdata.net/)

## Pages légales
- [Mentions légales](https://homekitchenclub.alwaysdata.net/mentions-legales)

## Auteur
- Nicolas Boulloud
- [LinkedIn](https://www.linkedin.com/in/nicolas-boulloud/)

## Licence

© 2026 Nicolas boulloudl. Tous droits réservés.


