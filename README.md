# Home Kitchen Club

A recipe site I coded in pure PHP to improve my web dev skills (no framework, I wanted to understand what's happening under the hood). There's a public area to browse recipes, an account system, and an admin dashboard to manage everything.

## What it does

* Recipe list filterable by category, with a detailed page per recipe (ingredients, steps, time, difficulty)
* Live serving adjustment on the recipe page (quantities recalculated in JS)
* User accounts: sign up / log in, with a "stay logged in" option (remember-me secured by token)
* Admin dashboard to create, edit, and delete recipes
* Image upload, automatically converted to AVIF + thumbnail generation
* Contact form in a popup (AJAX, protected by a CSRF token)
* Bilingual FR/EN site, auto-detected based on browser language

## Stack

Native PHP, MySQL/PDO, vanilla HTML/CSS/JS. No framework, no build tool. Images go through GD for AVIF conversion (requires PHP 8.1+).

## Project structure

```
homekitchenclub/
├── public/                  # Web root (document root)
│   ├── index.php            # Home page (recipe list)
│   ├── recette.php          # Recipe detail page
│   ├── contact.php          # Contact form
│   ├── contact_envoyer.php  # Contact form handler
│   ├── mentions-legales.php
│   ├── .htaccess
│   ├── logo.png
│   └── logo-navbar.svg
│
├── admin/                   # Recipe management (logged-in area)
│   ├── dashboard.php
│   ├── ajouter.php
│   └── modifier.php
│
├── utilisateur/             # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── includes/                # Business/technical logic (not directly URL-accessible)
│   ├── db.php                # PDO database connection
│   ├── lang.php              # Language handling (fr/en)
│   ├── auth_check.php        # User session check
│   └── image-utils.php       # Image processing / resizing
│
├── lang/                    # Translation files
│   ├── fr.php
│   └── en.php
│
├── assets/
│   └── css/
│       └── style.css
│
└── uploads/                 # User-uploaded images
    └── recettes/

```

## Security

A few things I put in place while learning about the topic:

* Passwords hashed with `password_hash` / `password_verify`
* Prepared SQL statements (PDO) everywhere, no query concatenation
* CSRF protection on sensitive forms (contact, recipe creation/editing)
* "Remember me" cookie based on a hashed selector/validator pair (no plaintext token stored server-side), rotated on every use

## Internationalization

The site detects the browser language on first load and displays content in French or English accordingly. The logic lives in `lang.php`, static text is in `fr.php` / `en.php`, and recipes have `_en` columns in the database with automatic fallback to French if the translation hasn't been filled in yet.

## Live

* Site: [homekitchenclub.alwaysdata.net](https://homekitchenclub.alwaysdata.net/)
* [Legal notice](https://homekitchenclub.alwaysdata.net/mentions-legales)

## Author

Nicolas Boulloud — [LinkedIn](https://www.linkedin.com/in/nicolas-boulloud/)

## License

© 2026 Nicolas Boulloud. All rights reserved.
