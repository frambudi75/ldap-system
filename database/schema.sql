-- ==========================================================
-- Custom LDAP Server & Management Database Schema
-- Database: ldap_server_db
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `ldap_server_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `ldap_server_db`;

-- 1. Table: ldap_settings
CREATE TABLE IF NOT EXISTS `ldap_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(64) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `description` VARCHAR(255) NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: ldap_users
CREATE TABLE IF NOT EXISTS `ldap_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(64) NOT NULL UNIQUE,          -- maps to uid, sAMAccountName
    `full_name` VARCHAR(128) NOT NULL,               -- maps to cn, displayName
    `first_name` VARCHAR(64) NULL,                   -- maps to givenName
    `last_name` VARCHAR(64) NULL,                    -- maps to sn
    `email` VARCHAR(128) NULL UNIQUE,                -- maps to mail
    `password_hash` VARCHAR(255) NOT NULL,           -- maps to userPassword
    `telephone` VARCHAR(32) NULL,                    -- maps to telephoneNumber
    `department` VARCHAR(64) NULL,                   -- maps to departmentNumber
    `ou` VARCHAR(64) NOT NULL DEFAULT 'users',       -- Organizational Unit
    `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_username` (`username`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: ldap_groups
CREATE TABLE IF NOT EXISTS `ldap_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(64) NOT NULL UNIQUE,              -- maps to cn
    `description` VARCHAR(255) NULL,
    `gid_number` INT UNSIGNED NULL UNIQUE,           -- maps to gidNumber (posixGroup)
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table: ldap_group_members
CREATE TABLE IF NOT EXISTS `ldap_group_members` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_group_user` (`group_id`, `user_id`),
    CONSTRAINT `fk_group_members_group` FOREIGN KEY (`group_id`) REFERENCES `ldap_groups`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_group_members_user` FOREIGN KEY (`user_id`) REFERENCES `ldap_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table: ldap_logs
CREATE TABLE IF NOT EXISTS `ldap_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_ip` VARCHAR(45) NULL,
    `operation` VARCHAR(32) NOT NULL,               -- BIND, SEARCH, UNBIND, etc.
    `bind_dn` VARCHAR(255) NULL,
    `search_filter` TEXT NULL,
    `status` ENUM('SUCCESS', 'FAILED') NOT NULL,
    `error_message` VARCHAR(255) NULL,
    `response_time_ms` INT UNSIGNED NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_logs_operation` (`operation`),
    INDEX `idx_logs_status` (`status`),
    INDEX `idx_logs_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA (Default Settings & Sample Users)
-- ==========================================================

-- Insert Settings
INSERT INTO `ldap_settings` (`setting_key`, `setting_value`, `description`) VALUES
('base_dn', 'dc=internal,dc=net', 'Base Distinguished Name for LDAP directory'),
('admin_bind_dn', 'cn=admin,dc=internal,dc=net', 'Root / Service account Bind DN'),
('admin_password_hash', '$2y$10$Tf.S.TMRUJNDphQ2Casdme7X1Ophe44VEw2klE4PBjO6/y1w181q2', 'Password hash for admin (default: admin123)'),
('allow_anonymous_bind', '1', '1 to allow anonymous bind, 0 to require authentication'),
('ldap_port', '389', 'TCP listening port for LDAP daemon'),
('user_ou', 'ou=users', 'Default Organizational Unit for users'),
('group_ou', 'ou=groups', 'Default Organizational Unit for groups'),
('org_name', 'Internal Directory Service', 'Organization display name')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Insert Groups
INSERT INTO `ldap_groups` (`name`, `description`, `gid_number`) VALUES
('Administrators', 'System administrators with full privileges', 500),
('Staff', 'General company staff accounts', 501),
('Developers', 'Software development and technical engineering team', 502)
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- Insert Initial Users (Passwords: adminpassword, password123, password123)
-- Password hashes generated with password_hash('...', PASSWORD_BCRYPT)
INSERT INTO `ldap_users` (`username`, `full_name`, `first_name`, `last_name`, `email`, `password_hash`, `telephone`, `department`, `ou`, `status`) VALUES
('admin', 'System Administrator', 'System', 'Administrator', 'admin@internal.net', '$2y$10$XzD1LBTGMxoYk6cFlz3EQOmwahz015kMzKRIr61YVOp0.1Xxz6oRi', '+62811000001', 'IT & Infrastructure', 'users', 'active'),
('budi', 'Budi Santoso', 'Budi', 'Santoso', 'budi@internal.net', '$2y$10$FzWZutht6IsodDVKBss84eL4GHI7YophTWeKwxD1N2G0woGTZW/8y', '+628123456789', 'Software Engineering', 'users', 'active'),
('siti', 'Siti Rahma', 'Siti', 'Rahma', 'siti@internal.net', '$2y$10$FzWZutht6IsodDVKBss84eL4GHI7YophTWeKwxD1N2G0woGTZW/8y', '+628129876543', 'Human Resources', 'users', 'active')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- Assign Users to Groups
-- Note: admin -> Administrators, budi -> Developers & Staff, siti -> Staff
INSERT IGNORE INTO `ldap_group_members` (`group_id`, `user_id`)
SELECT g.id, u.id FROM `ldap_groups` g, `ldap_users` u WHERE g.name = 'Administrators' AND u.username = 'admin';

INSERT IGNORE INTO `ldap_group_members` (`group_id`, `user_id`)
SELECT g.id, u.id FROM `ldap_groups` g, `ldap_users` u WHERE g.name = 'Developers' AND u.username = 'budi';

INSERT IGNORE INTO `ldap_group_members` (`group_id`, `user_id`)
SELECT g.id, u.id FROM `ldap_groups` g, `ldap_users` u WHERE g.name = 'Staff' AND u.username = 'budi';

INSERT IGNORE INTO `ldap_group_members` (`group_id`, `user_id`)
SELECT g.id, u.id FROM `ldap_groups` g, `ldap_users` u WHERE g.name = 'Staff' AND u.username = 'siti';

-- Initial Sample Log Entry
INSERT INTO `ldap_logs` (`client_ip`, `operation`, `bind_dn`, `search_filter`, `status`, `error_message`, `response_time_ms`)
VALUES ('127.0.0.1', 'SYSTEM_INIT', 'system', NULL, 'SUCCESS', 'Database schema initialized successfully', 5);
