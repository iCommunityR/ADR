# XAMPP / Local setup

Place the repository folder inside your web server document root (for XAMPP: C:\xampp\htdocs\ADR).

Steps:
1. Ensure PHP & MySQL (Apache, MySQL) are running (start XAMPP control panel).
2. Create a MySQL database named `africa_adr` or change the name in `config/config.php`.
3. Edit `config/config.php` if needed to set DB credentials and `app.base_url` (use '/ADR' if served at http://localhost/ADR).
4. Visit http://localhost/ADR/ — the root index.php front controller will serve the site.

Notes:
- Static assets are referenced relative to the application root (public/css, public/js).
- Uploaded files are expected under `storage/uploads` as referenced by `public/download.php`.
- If you host this at a subpath, set `app.base_url` accordingly in `config/config.php`.
