# Soirées / Vote Project

## Server structure

The web root (document root) is **`www/public/`**. Anything that needs to be
reachable from the browser must live inside this folder.

```
www/
├── admin/
│   ├── admin_auth.php
│   ├── admin_dashboard.php
│   └── admin_login.php
├── includes/
│   ├── db.php           # database connection (reads the .env)
│   ├── login.php
│   └── login_auth.php
├── public/               # ← web root (document root)
│   ├── assets/
│   │   └── css/
│   │       └── styles.css
│   ├── img/
│   ├── vote/
│   │   ├── films_vote.php
│   │   ├── lieu_vote.php
│   │   ├── lieux_candidats.php
│   │   ├── lieux_soiree.php
│   │   └── soirees_vote.php
│   ├── formulaire.php
│   ├── index.php
│   ├── inscription.php
│   ├── lieu.php
│   ├── logout.php
│   ├── profil.php
│   ├── quitter_soiree.php
│   ├── session_user.php
│   ├── soiree_card.php
│   ├── traitement.php
│   └── vote_results.php
└── .env                  # DB connection variables (server root)
```

## General path rule

- All links/AJAX calls in `index.php` are **relative to `public/`**
  (e.g. `assets/css/styles.css`, `vote/films_vote.php`).
- Any PHP file that needs the database must include `includes/db.php` with a
  path **relative to its own location**, using `__DIR__`, e.g.:
  - from `public/lieu.php` → `__DIR__ . '/../includes/db.php'`
  - from `public/vote/films_vote.php` → `__DIR__ . '/../../includes/db.php'`

## Fixes applied

1. **CSS not loading (404)** — the `<link rel="stylesheet">` in `index.php`
   pointed to the wrong path / had a corrupted `rel` attribute. Fixed to
   point to `assets/css/styles.css`.
2. **Bootstrap CSS not loading** — the `rel` attribute of the Bootstrap
   `<link>` tag was `rel="/assets/css/stylesheet"` instead of
   `rel="stylesheet"`, which prevented modals from staying hidden by
   default (they were rendering full-page).
3. **`films_vote.php` returning 404** — the `vote/` folder lived at
   `www/vote/`, outside the web root (`www/public/`). Moved it to
   `www/public/vote/` and updated all AJAX calls in `index.php` with the
   `vote/` prefix.
4. **"Voter films" button doing nothing** — the `data-bs-dismiss="modal"`
   attribute on `#btn-modal-voter` and `#btn-modal-voter-lieu` closed the
   `modalParticiper` modal at the same time the JS tried to open the next
   one (`modalVote` / `modalVoteLieu`), causing a Bootstrap backdrop
   conflict. Removed the attribute and added a manual `hide()` of the
   previous modal before opening the next one.
5. **`connexion.php` not found** — `films_vote.php` used
   `require_once 'connexion.php'` (an obsolete file, replaced by an `.env` +
   `includes/db.php` setup). Fixed to
   `require_once __DIR__ . '/../../includes/db.php';`.

## To check / remaining work

- [ ] Check whether `lieu_vote.php`, `lieux_candidats.php`,
      `lieux_soiree.php` and `soirees_vote.php` (in `public/vote/`) still
      use `require_once 'connexion.php'` and fix them the same way if
      needed.
- [ ] Confirm that `includes/db.php` correctly reads the `.env` at the
      server root for the database credentials.
- [ ] Clear browser cache / force reload after each upload to avoid
      testing a stale version of the files.

## Deployment

1. Edit files locally.
2. Re-upload via FTP to the correct folder under `www/public/` (respect the
   structure above).
3. Test with the browser console (F12 → Console / Network) to spot any
   404s or PHP errors.
