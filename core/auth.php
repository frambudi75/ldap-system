<?php
/**
 * Authentication and Session Management for Web Admin.
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    public static function check(): bool {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public static function user(): ?array {
        return $_SESSION['admin_user'] ?? null;
    }

    public static function login(string $username, string $password): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM ldap_users WHERE username = :username AND status = 'active' LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
            ];
            return true;
        }

        // Also check if admin bind credentials match
        $stmt = $pdo->prepare("SELECT setting_value FROM ldap_settings WHERE setting_key = 'admin_password_hash' LIMIT 1");
        $stmt->execute();
        $adminSetting = $stmt->fetch();
        if ($username === 'admin' && $adminSetting && password_verify($password, $adminSetting['setting_value'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = [
                'id' => 1,
                'username' => 'admin',
                'full_name' => 'System Administrator',
                'email' => 'admin@internal.net',
            ];
            return true;
        }

        return false;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            header("Location: " . BASE_URL . "/index.php?page=login");
            exit;
        }
    }
}
