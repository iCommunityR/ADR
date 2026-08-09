# African Disputes Resolution Platform

A responsive PHP/MySQL research platform for African alternative dispute resolution law, based in Uganda and structured for continent-wide coverage.

## Implemented modules

### Multilingual public interface

The public interface supports:

- English
- French
- Arabic, with right-to-left layout
- Portuguese
- Swahili

The selected language is retained in the visitor's session and a one-year preference cookie. Interface labels are translated, while uploaded legal records retain an explicit **content language**. This prevents the platform from presenting an interface translation as an official translation of legislation or a judgment.

### Legal-document lifecycle

Each document can record:

- Official document number
- Version or consolidation label
- Current, amended, repealed, superseded or draft legal status
- Effective date
- Repeal or supersession date
- Last-verified date
- Verification source and editorial notes
- The earlier document it supersedes
- The later document that repealed or superseded it
- Original source URL
- Country, regional regime, section, type and content language

Editing an existing document automatically stores its complete previous scope, classification, lifecycle metadata, publication state and attached file in `document_versions`. Archived revisions are numbered, downloadable and restorable from the admin interface. Only revisions that were published are exposed in the public version history.

### Saved research folders

Readers can create personal research accounts and:

- Create private folders
- Save legislation, cases and institutions
- Add a note when saving an item
- Remove saved items
- Export complete folder citations as CSV, RIS or BibTeX

Research folders and notes are isolated by reader account. Administrators can see aggregate account adoption but not private folder contents through the supplied admin screens.

### Institutional subscriptions

The platform includes:

- Configurable Professional, Academic and Enterprise plans
- Public institutional access request form
- Enquiry, trial, active, past-due, expired and cancelled lifecycle states
- Start, end and renewal dates
- Seat allocation and seat-usage tracking
- Account administrator and researcher roles
- Invited, active and suspended member states
- Automatic linking when an invited member creates a research account using the same email address
- Public plan pricing and features editable from admin

The included plan prices are demonstration values. Update or remove them before launch. Payment collection and automated email delivery are not included because merchant, tax and email-service details are deployment-specific.

## Fresh installation with XAMPP

1. Extract `africa-adr-platform` into:

   `C:\xampp\htdocs\africa-adr-platform`

2. Start Apache and MySQL.
3. Create an empty MySQL database named `africa_adr`.
4. Review `config/config.php` or set environment variables.
5. Open:

   `http://localhost/africa-adr-platform/install.php`

6. Create the first administrator using a password of at least 12 characters.
7. Sign in at:

   `http://localhost/africa-adr-platform/admin.php`

8. Delete or rename `install.php` after installation.

## Upgrade an existing Africa ADR installation

Before upgrading, back up:

- The MySQL database
- `storage/uploads`
- `config/config.php`

Then:

1. Replace the existing application files with this version, preserving your configuration and uploads.
2. Sign in to the existing admin account.
3. The admin will redirect to `upgrade.php` when it detects the earlier database structure.
4. Select **Run upgrade**.
5. Return to the admin dashboard and review the new document, research and institutional modules.
6. Delete or rename `upgrade.php` after successful migration.

The migration adds columns and tables without deleting existing records. Existing legal documents begin building version history the first time they are edited after the upgrade.

## Environment variables

```text
APP_URL=http://localhost/africa-adr-platform
APP_TIMEZONE=Africa/Kampala
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=africa_adr
DB_USER=root
DB_PASS=
```

## Main files

- `index.php` — multilingual public platform, research accounts and institutional request form
- `admin.php` — country, document, case, institution, researcher and subscription administration
- `install.php` — fresh database installer
- `upgrade.php` — one-time migration for the earlier platform version
- `download.php` — current published files
- `version-download.php` — archived published document revisions
- `export.php` — CSV, RIS and BibTeX research-folder exports
- `includes/i18n.php` — interface language dictionaries and RTL selection
- `database/schema.sql` — complete fresh-install schema
- `database/seed.sql` — 54 countries and configurable institutional plans

## Security controls included

- PHP `password_hash()` and `password_verify()` for administrator and reader passwords
- CSRF tokens on state-changing forms
- Session ID rotation after login
- HTTP-only, SameSite session cookies
- Prepared SQL statements
- HTML output escaping
- File extension, MIME and signature verification
- Randomized uploaded filenames
- Protected upload directory
- Uploaded-file path containment checks
- Public downloads limited to published records
- Ownership checks for research folders and citation exports
- Seat-limit enforcement for institutional members
- Source URL validation restricted to HTTP and HTTPS

## Production work still required

Before a public launch, configure:

- TLS/HTTPS and secure production environment variables
- Transactional email for invitations, password resets and subscription notices
- Email verification and account recovery
- Malware scanning for uploaded files
- Off-site backups and disaster recovery
- Payment gateway and invoicing, where paid online subscriptions are required
- Ugandan and other applicable African privacy-compliance review
- Formal legal review of Terms and Privacy notices
- Manual accessibility and assistive-technology testing
- Professional review of translations and legal terminology
- Authoritative source-verification and editorial governance procedures

## Minimum recommended server

- PHP 8.1 or later
- PDO MySQL extension
- MySQL 8.0+ or a current MariaDB release
- Fileinfo extension
- Apache with `mod_rewrite`, or equivalent Nginx rules
