SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 last_login_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS countries (
 id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code CHAR(3) NOT NULL UNIQUE,
 name VARCHAR(120) NOT NULL UNIQUE,
 region VARCHAR(60) NOT NULL,
 common_law_index TINYINT(1) NOT NULL DEFAULT 0,
 profile_summary TEXT NULL,
 icsid_status VARCHAR(255) NULL,
 icc_status VARCHAR(255) NULL,
 new_york_status VARCHAR(255) NULL,
 uncitral_status VARCHAR(255) NULL,
 source_url VARCHAR(1000) NULL,
 last_verified_at DATE NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 country_id SMALLINT UNSIGNED NULL,
 regime_key VARCHAR(40) NULL,
 section_key VARCHAR(80) NOT NULL,
 document_type VARCHAR(80) NOT NULL,
 language_code CHAR(2) NOT NULL DEFAULT 'en',
 document_number VARCHAR(120) NULL,
 title VARCHAR(300) NOT NULL,
 summary TEXT NULL,
 year SMALLINT UNSIGNED NULL,
 version_label VARCHAR(100) NULL,
 legal_status ENUM('current','amended','repealed','superseded','draft') NOT NULL DEFAULT 'current',
 effective_date DATE NULL,
 repeal_date DATE NULL,
 last_verified_at DATE NULL,
 verification_source VARCHAR(1000) NULL,
 verification_notes TEXT NULL,
 supersedes_document_id BIGINT UNSIGNED NULL,
 repealed_by_document_id BIGINT UNSIGNED NULL,
 source_url VARCHAR(1000) NULL,
 keywords TEXT NULL,
 file_path VARCHAR(500) NULL,
 original_filename VARCHAR(255) NULL,
 mime_type VARCHAR(150) NULL,
 file_size BIGINT UNSIGNED NULL,
 is_published TINYINT(1) NOT NULL DEFAULT 0,
 published_at DATETIME NULL,
 created_by INT UNSIGNED NULL,
 updated_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(country_id),INDEX(section_key),INDEX(regime_key),INDEX(year),INDEX idx_documents_legal_status(legal_status),INDEX idx_documents_effective_date(effective_date),INDEX(last_verified_at),
 FULLTEXT KEY ft_documents(title,summary,keywords),
 FOREIGN KEY(country_id) REFERENCES countries(id) ON DELETE SET NULL,
 FOREIGN KEY(supersedes_document_id) REFERENCES documents(id) ON DELETE SET NULL,
 FOREIGN KEY(repealed_by_document_id) REFERENCES documents(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS document_versions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 document_id BIGINT UNSIGNED NOT NULL,
 revision_no INT UNSIGNED NOT NULL,
 country_id SMALLINT UNSIGNED NULL,
 regime_key VARCHAR(40) NULL,
 section_key VARCHAR(80) NOT NULL,
 document_type VARCHAR(80) NOT NULL,
 language_code CHAR(2) NOT NULL DEFAULT 'en',
 document_number VARCHAR(120) NULL,
 title VARCHAR(300) NOT NULL,
 summary TEXT NULL,
 year SMALLINT UNSIGNED NULL,
 version_label VARCHAR(100) NULL,
 legal_status VARCHAR(30) NOT NULL,
 effective_date DATE NULL,
 repeal_date DATE NULL,
 last_verified_at DATE NULL,
 verification_source VARCHAR(1000) NULL,
 verification_notes TEXT NULL,
 supersedes_document_id BIGINT UNSIGNED NULL,
 repealed_by_document_id BIGINT UNSIGNED NULL,
 source_url VARCHAR(1000) NULL,
 keywords TEXT NULL,
 file_path VARCHAR(500) NULL,
 original_filename VARCHAR(255) NULL,
 mime_type VARCHAR(150) NULL,
 file_size BIGINT UNSIGNED NULL,
 is_published TINYINT(1) NOT NULL DEFAULT 0,
 published_at DATETIME NULL,
 change_note VARCHAR(500) NULL,
 changed_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_document_revision(document_id,revision_no),
 INDEX(document_id,created_at),INDEX idx_version_country(country_id),INDEX idx_version_legal_status(legal_status),
 FOREIGN KEY(document_id) REFERENCES documents(id) ON DELETE CASCADE,
 FOREIGN KEY(country_id) REFERENCES countries(id) ON DELETE SET NULL,
 FOREIGN KEY(supersedes_document_id) REFERENCES documents(id) ON DELETE SET NULL,
 FOREIGN KEY(repealed_by_document_id) REFERENCES documents(id) ON DELETE SET NULL,
 FOREIGN KEY(changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cases (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 country_id SMALLINT UNSIGNED NOT NULL,
 language_code CHAR(2) NOT NULL DEFAULT 'en',
 case_name VARCHAR(350) NOT NULL,
 citation VARCHAR(255) NULL,
 court VARCHAR(255) NOT NULL,
 decision_date DATE NULL,
 year SMALLINT UNSIGNED NOT NULL,
 primary_subject VARCHAR(160) NOT NULL,
 secondary_subject VARCHAR(180) NULL,
 summary MEDIUMTEXT NULL,
 key_holding MEDIUMTEXT NULL,
 keywords TEXT NULL,
 source_url VARCHAR(1000) NULL,
 last_verified_at DATE NULL,
 file_path VARCHAR(500) NULL,
 original_filename VARCHAR(255) NULL,
 mime_type VARCHAR(150) NULL,
 file_size BIGINT UNSIGNED NULL,
 is_published TINYINT(1) NOT NULL DEFAULT 0,
 created_by INT UNSIGNED NULL,
 updated_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(country_id,year),INDEX(primary_subject),INDEX(secondary_subject),INDEX(last_verified_at),
 FULLTEXT KEY ft_cases(case_name,citation,summary,keywords),
 FOREIGN KEY(country_id) REFERENCES countries(id),
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institutions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 country_id SMALLINT UNSIGNED NOT NULL,
 language_code CHAR(2) NOT NULL DEFAULT 'en',
 name VARCHAR(255) NOT NULL,
 description TEXT NULL,
 address TEXT NULL,
 phone VARCHAR(100) NULL,
 email VARCHAR(190) NULL,
 website VARCHAR(1000) NULL,
 rules_url VARCHAR(1000) NULL,
 last_verified_at DATE NULL,
 is_published TINYINT(1) NOT NULL DEFAULT 1,
 created_by INT UNSIGNED NULL,
 updated_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(country_id),INDEX(last_verified_at),
 FOREIGN KEY(country_id) REFERENCES countries(id),
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(190) NOT NULL UNIQUE,
 name VARCHAR(120) NULL,
 status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
 subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 unsubscribed_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS research_users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 organization VARCHAR(190) NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 last_login_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS research_folders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 research_user_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(160) NOT NULL,
 description VARCHAR(500) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(research_user_id),
 FOREIGN KEY(research_user_id) REFERENCES research_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS research_folder_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 folder_id BIGINT UNSIGNED NOT NULL,
 entity_type ENUM('document','case','institution') NOT NULL,
 entity_id BIGINT UNSIGNED NOT NULL,
 note VARCHAR(1000) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_folder_item(folder_id,entity_type,entity_id),
 INDEX(folder_id,created_at),
 FOREIGN KEY(folder_id) REFERENCES research_folders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_plans (
 id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(40) NOT NULL UNIQUE,
 name VARCHAR(120) NOT NULL,
 description VARCHAR(500) NULL,
 annual_price DECIMAL(12,2) NULL,
 currency CHAR(3) NOT NULL DEFAULT 'USD',
 included_seats SMALLINT UNSIGNED NOT NULL DEFAULT 5,
 features TEXT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institutional_subscriptions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 plan_id SMALLINT UNSIGNED NULL,
 institution_name VARCHAR(255) NOT NULL,
 institution_type VARCHAR(100) NULL,
 country_id SMALLINT UNSIGNED NULL,
 contact_name VARCHAR(160) NOT NULL,
 contact_email VARCHAR(190) NOT NULL,
 contact_phone VARCHAR(100) NULL,
 website VARCHAR(1000) NULL,
 seats_requested SMALLINT UNSIGNED NOT NULL DEFAULT 5,
 billing_cycle ENUM('annual','custom') NOT NULL DEFAULT 'annual',
 status ENUM('inquiry','trial','active','past_due','cancelled','expired') NOT NULL DEFAULT 'inquiry',
 start_date DATE NULL,
 end_date DATE NULL,
 renewal_date DATE NULL,
 notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(status),INDEX(country_id),INDEX(renewal_date),
 FOREIGN KEY(plan_id) REFERENCES subscription_plans(id) ON DELETE SET NULL,
 FOREIGN KEY(country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS institutional_members (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 subscription_id BIGINT UNSIGNED NOT NULL,
 research_user_id BIGINT UNSIGNED NULL,
 name VARCHAR(160) NOT NULL,
 email VARCHAR(190) NOT NULL,
 member_role ENUM('account_admin','researcher') NOT NULL DEFAULT 'researcher',
 status ENUM('invited','active','suspended') NOT NULL DEFAULT 'invited',
 invited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 joined_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_subscription_member(subscription_id,email),
 INDEX(research_user_id),INDEX(subscription_id,status),
 FOREIGN KEY(subscription_id) REFERENCES institutional_subscriptions(id) ON DELETE CASCADE,
 FOREIGN KEY(research_user_id) REFERENCES research_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(100) NOT NULL,
 details VARCHAR(500) NULL,
 ip_address VARCHAR(45) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
