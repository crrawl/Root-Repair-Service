CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'technician', 'owner') NOT NULL DEFAULT 'customer',
    note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS repairs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(32) NOT NULL UNIQUE,
    customer_id INT UNSIGNED NULL,
    customer_name VARCHAR(120) NOT NULL,
    contact_email VARCHAR(190) NOT NULL,
    device_type VARCHAR(80) NOT NULL,
    device VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    status ENUM(
        'submitted',
        'received',
        'diagnostics',
        'repairing',
        'ready',
        'collected'
    ) NOT NULL DEFAULT 'submitted',
    summary TEXT NOT NULL,
    internal_note TEXT NULL,
    assigned_to INT UNSIGNED NULL,
    photo_path VARCHAR(255) NULL,
    photo_original VARCHAR(255) NULL,
    photos_json JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users (id),
    FOREIGN KEY (assigned_to) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS lab_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    content TEXT NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS app_settings (name VARCHAR(80) PRIMARY KEY, value TEXT NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS diagnostics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    repair_id INT UNSIGNED NOT NULL,
    imported_by INT UNSIGNED NULL,
    model VARCHAR(160) NOT NULL,
    serial VARCHAR(160) NOT NULL DEFAULT '',
    result TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repair_id) REFERENCES repairs (id) ON DELETE CASCADE,
    FOREIGN KEY (imported_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id INT UNSIGNED NULL,
    action VARCHAR(40) NOT NULL,
    repair_id INT UNSIGNED NULL,
    reference VARCHAR(32) NOT NULL,
    details TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

