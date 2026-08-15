<?php
/**
 * Application Settings and Constants.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'LDAP Engine Console');
define('APP_VERSION', '1.0.0');

// Dynamic BASE_URL detection (works for both XAMPP subfolder /ldap and Docker root /)
$envBaseUrl = getenv('BASE_URL');
if ($envBaseUrl !== false) {
    define('BASE_URL', rtrim($envBaseUrl, '/'));
} else {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base = ($scriptDir === '/' || $scriptDir === '\\') ? '' : str_replace('\\', '/', $scriptDir);
    define('BASE_URL', $base);
}

// Helper to get dynamic settings from database
function get_setting(string $key, string $default = ''): string {
    static $settingsCache = null;
    if ($settingsCache === null) {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM ldap_settings");
            $rows = $stmt->fetchAll();
            $settingsCache = [];
            foreach ($rows as $row) {
                $settingsCache[$row['setting_key']] = $row['setting_value'] ?? '';
            }
        } catch (Exception $e) {
            $settingsCache = [];
        }
    }
    return $settingsCache[$key] ?? $default;
}

function get_base_dn(): string {
    return get_setting('base_dn', 'dc=internal,dc=net');
}

function get_admin_bind_dn(): string {
    return get_setting('admin_bind_dn', 'cn=admin,dc=internal,dc=net');
}

function get_ldap_port(): int {
    return (int) get_setting('ldap_port', '389');
}
