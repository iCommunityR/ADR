# UI README

This branch (feature/ui) adds a lightweight PHP frontend to the ADR repository. It uses the existing `config/config.php` and expects the database schema/seed already imported.

Quick start (development):

1. Ensure the database is created and schema/seed imported:
   - mysql -u root -p < database/schema.sql
   - mysql -u root -p < database/seed.sql

2. Ensure storage/uploads exists and is writable by the webserver (the config points to `storage/uploads`).

3. Serve the `public/` folder. With PHP built-in server (development):
   - cd public
   - php -S 0.0.0.0:8080
   - Open http://localhost:8080 in your browser

Notes:
- The UI is intentionally minimal and focuses on visual balance, responsive cards and safe downloads.
- I did not modify existing config or database files. All new files are on branch `feature/ui`.
- If you want a different color palette or brand assets, provide hex codes or images and I will update the styles and header.
