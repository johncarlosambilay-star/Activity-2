CREATE DATABASE IF NOT EXISTS user_accounts_system
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE user_accounts_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Demo administrator:
-- Email: admin@example.com
-- Password: admin123
-- The password below is a PHP password_hash() value.
INSERT INTO users (full_name, email, password, role)
VALUES (
    'System Administrator',
    'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
)
ON DUPLICATE KEY UPDATE email = email;
