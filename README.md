# Home Kitchen Club

A recipe site I coded in pure PHP to improve my web dev skills (no framework, I wanted to understand what's happening under the hood). There's a public area to browse recipes, an account system, and an admin dashboard to manage everything.

**Live demo:** [homekitchenclub.alwaysdata.net](https://homekitchenclub.alwaysdata.net/)

**Stack:** Native PHP, MySQL/PDO, vanilla HTML/CSS/JS. No framework, no build tool. Images go through GD for AVIF conversion.

## Features

* Recipe list filterable by category, with a detailed page per recipe (ingredients, steps, time, difficulty)
* Live serving adjustment on the recipe page (quantities recalculated in JS)
* User accounts: sign up / log in, with a "stay logged in" option (remember-me secured by token)
* Admin dashboard to create, edit, and delete recipes
* Image upload, automatically converted to AVIF + thumbnail generation
* Contact form in a popup (AJAX, protected by a CSRF token)
* Bilingual FR/EN site, auto-detected based on browser language

## Requirements

* PHP 8.1 or newer, with extensions `pdo_mysql` and `gd` (compiled with **AVIF support**)
* MySQL 5.7+ or MariaDB 10.3+
* Apache with `mod_rewrite` and `AllowOverride All` (the `.htaccess` handles URL rewriting, security and caching)

Check AVIF support:

```bash
php -r "var_dump(gd_info()['AVIF Support']);"
```

## Installation

```bash
git clone https://github.com/UnsoberDriver/HomeKitchenClub
cd HomeKitchenClub
cp .env.example .env      # Windows: copy .env.example .env
```

1. **Configure** `.env` (see [Configuration](#configuration)).
2. **Create the database** and import the schema:
   ```bash
   mysql -u root -p -e "CREATE DATABASE homekitchenclub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p homekitchenclub < database/schema.sql
   ```
3. **Make the uploads folder writable** by the web server:
   ```bash
   mkdir -p www/uploads
   chmod 775 www/uploads
   ```
4. **Point the web server's document root to `www/public/`**. Everything else (`includes/`, `lang/`, `uploads/`, `.env`) must stay outside of it.
5. **Quick local test** with PHP's built-in server (note: `.htaccess` rules are ignored, so clean URLs won't work):
   ```bash
   php -S localhost:8000 -t www/public
   ```

## Configuration

The `.env` file lives at the repository root, outside of the web root.

| Variable | Description |
|---|---|
| `DB_HOST` | Database host (e.g. `localhost`) |
| `DB_NAME` | Database name |
| `DB_USER` | Database user |
| `DB_PASSWORD` | Database password |

Never commit `.env`: make sure it is listed in `.gitignore`.

## Creating an admin account

`auth_check.php` accepts two kinds of admin. Pick one:

**Option A: promote a regular account**

1. Register at `/user/register`.
2. Promote it in the database:
   ```sql
   UPDATE utilisateurs SET est_admin = 1 WHERE email = 'you@example.com';
   ```

**Option B: dedicated admin (`admins` table)**, the only kind that supports the "stay logged in" cookie:

1. Generate a password hash:
   ```bash
   php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
   ```
2. Insert the admin:
   ```sql
   INSERT INTO admins (email, mot_de_passe_hash, nom) VALUES ('you@example.com', '<hash>', 'Your name');
   ```

Then log in at `/user/login` and open `/admin/dashboard`.

## Usage

1. Browse recipes on the home page, filter by category and adjust the servings.
2. Create an account or log in via `/user/login`.
3. Admins manage recipes from `/admin/dashboard`.

## Project structure

```
/
├── .env                          # Environment variables (DB credentials, secrets) — never committed
├── .env.example                  # Configuration template
├── database/
│   └── schema.sql                # Database schema
└── www/
    │
    ├── uploads/                  # All pictures (outside of the document root)
    │   └── pictures.avif
    │
    ├── lang/                     # Translation files
    │   ├── en.php                # English translations
    │   └── fr.php                # French translations
    │
    ├── includes/                 # Shared PHP files (DB connection, language handling, etc.)
    │   ├── db.php                # Database connection (PDO)
    │   ├── lang.php              # Internationalization handling (FR/EN)
    │   ├── auth_check.php        # Verifies if an admin is connected
    │   └── image-utils.php       # Resizes pictures if necessary
    │
    └── public/                   # Web root (server document root)
        │
        ├── assets/               # Main stylesheet
        │   └── style.css
        │
        ├── recipes/              # Recipe detail page
        │   └── recette.php
        │
        ├── picture/
        │   └── image.php         # Secure proxy serving images from the uploads/ folder
        │
        ├── legal-notices/        # Legal notices
        │   └── mentions-legales
        │
        ├── contact/
        │   ├── contact.php
        │   └── contact_envoyer.php
        │
        ├── user/                 # User account management
        │   ├── login.php
        │   ├── logout.php
        │   └── register.php
        │
        ├── admin/                # Admin back-office
        │   ├── ajouter.php       # Add a new recipe
        │   ├── dashboard.php     # Admin dashboard
        │   └── modifier.php      # Edit an existing recipe
        │
        ├── .htaccess             # URL rewriting, security, browser caching
        └── index.php             # Home page (recipe listing)
```

## Security

A few things I put in place while learning about the topic:

* Passwords hashed with `password_hash` / `password_verify`
* Prepared SQL statements (PDO) everywhere, no query concatenation
* CSRF protection on sensitive forms (contact, recipe creation/editing)
* "Remember me" cookie based on a hashed selector/validator pair (no plaintext token stored server-side), rotated on every use
* Uploads and secrets kept outside of the document root; images served through a proxy script

## Internationalization

The site detects the browser language on first load and displays content in French or English accordingly. The logic lives in `lang.php`, static text is in `fr.php` / `en.php`, and recipes have `_en` columns in the database with automatic fallback to French if the translation hasn't been filled in yet.

To add a language: create `lang/xx.php` with the same keys as `en.php`, then register it in `lang.php`.

## Deployment notes

The live site runs on [alwaysdata](https://www.alwaysdata.com). Set the site's document root to `www/public`, create the MySQL database from the admin panel, and upload the project (keeping `.env` outside the public folder).

## Troubleshooting

* **Images are not converted / upload fails:** GD has no AVIF support. Check with the command in [Requirements](#requirements) and upgrade PHP or its GD build.
* **404 on every page except the home page:** `mod_rewrite` is disabled or `AllowOverride` is not set to `All`.
* **Database connection error:** check the `DB_*` values in `.env` and that the file is at the repository root.
* **"Stay logged in" does nothing locally:** the remember-me cookie is marked `Secure`, so it needs HTTPS (or a browser that treats `localhost` as secure).
* **Upload permission denied:** the web server user needs write access to `www/uploads/`.

## Legal notices

* [Legal notice](https://homekitchenclub.alwaysdata.net/mentions-legales)

## Author

Nicolas Boulloud — [LinkedIn](https://www.linkedin.com/in/nicolas-boulloud/)

## License

This project is proprietary — all rights reserved. See the LICENSE file for details.

See also NOTICE.md for additional usage restrictions (including AI training).

© 2026 Nicolas Boulloud.
