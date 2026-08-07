# Plan de renommage [equiploc → Rentova]

## Plan (option 2 : comptes e-mail + base uniquement)
- [x] 1. `app/Views/auth/login.php` : remplacer `@equiploc.com` par `@Rentova.com` (3 boutons de démo)
- [x] 2. `sql/database.sql` : remplacer `@equiploc.com` par `@Rentova.com` (3 comptes)
- [x] 3. `config/config.php` : `equiploc_db` → `rentova_db` (SESSION_NAME et CSRF_KEY conservés en `equiploc`)
- [x] 4. `app/Core/Router.php` : commentaire conservé en `equiploc/public` (inchangé)
- [x] 5. Vérification finale (recherche de résidus `@equiploc.com`)
