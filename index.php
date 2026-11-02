<?php
/**
 * Master Entry Point and Controller Router.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/helpers.php';

$action = $_GET['action'] ?? null;
$page   = $_GET['page'] ?? 'dashboard';

$pdo = Database::getConnection();

// ========================================================
// 1. ACTION HANDLERS
// ========================================================

// Handle Login Action
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Token CSRF tidak valid. Silakan coba lagi.');
        header("Location: " . BASE_URL . "/index.php?page=login");
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (Auth::login($username, $password)) {
        set_flash('success', 'Selamat datang kembali, ' . htmlspecialchars($username) . '!');
        header("Location: " . BASE_URL . "/index.php?page=dashboard");
        exit;
    } else {
        $loginError = 'Username atau password salah.';
        require __DIR__ . '/views/login.php';
        exit;
    }
}

// Handle Logout Action
if ($action === 'logout') {
    Auth::logout();
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

// Require login for all other mutating actions and admin pages
if ($page !== 'login') {
    Auth::requireLogin();
}

// Action: Create User
if ($action === 'create_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $dept      = trim($_POST['department'] ?? '');
    $phone     = trim($_POST['telephone'] ?? '');
    $groupIds  = $_POST['group_ids'] ?? [];

    if (empty($username) || empty($password) || empty($fullName)) {
        set_flash('danger', 'Username, Password, dan Nama Lengkap wajib diisi.');
        header("Location: " . BASE_URL . "/index.php?page=users");
        exit;
    }

    try {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO ldap_users (username, full_name, first_name, last_name, email, password_hash, department, telephone, ou, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'users', 'active')
        ");
        $stmt->execute([$username, $fullName, $firstName, $lastName, $email ?: null, $hash, $dept ?: null, $phone ?: null]);
        $newUserId = $pdo->lastInsertId();

        // Assign groups
        if (!empty($groupIds)) {
            $gStmt = $pdo->prepare("INSERT IGNORE INTO ldap_group_members (group_id, user_id) VALUES (?, ?)");
            foreach ($groupIds as $gid) {
                $gStmt->execute([(int)$gid, $newUserId]);
            }
        }

        set_flash('success', "Pengguna '{$username}' berhasil ditambahkan ke direktori LDAP.");
    } catch (PDOException $e) {
        set_flash('danger', "Gagal menambah pengguna: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "/index.php?page=users");
    exit;
}

// Action: Update User
if ($action === 'update_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $userId    = (int)($_POST['user_id'] ?? 0);
    $fullName  = trim($_POST['full_name'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $dept      = trim($_POST['department'] ?? '');
    $phone     = trim($_POST['telephone'] ?? '');
    $groupIds  = $_POST['group_ids'] ?? [];

    try {
        $stmt = $pdo->prepare("
            UPDATE ldap_users 
            SET full_name = ?, first_name = ?, last_name = ?, email = ?, department = ?, telephone = ?
            WHERE id = ?
        ");
        $stmt->execute([$fullName, $firstName, $lastName, $email ?: null, $dept ?: null, $phone ?: null, $userId]);

        // Sync groups
        $pdo->prepare("DELETE FROM ldap_group_members WHERE user_id = ?")->execute([$userId]);
        if (!empty($groupIds)) {
            $gStmt = $pdo->prepare("INSERT IGNORE INTO ldap_group_members (group_id, user_id) VALUES (?, ?)");
            foreach ($groupIds as $gid) {
                $gStmt->execute([(int)$gid, $userId]);
            }
        }

        set_flash('success', "Data pengguna berhasil diperbarui.");
    } catch (PDOException $e) {
        set_flash('danger', "Gagal update pengguna: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "/index.php?page=users");
    exit;
}

// Action: Reset User Password
if ($action === 'reset_user_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $userId = (int)($_POST['user_id'] ?? 0);
    $newPwd = $_POST['new_password'] ?? '';

    if (!empty($newPwd) && $userId > 0) {
        $hash = password_hash($newPwd, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE ldap_users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);

        $chkStmt = $pdo->prepare("SELECT username FROM ldap_users WHERE id = ?");
        $chkStmt->execute([$userId]);
        if ($chkStmt->fetchColumn() === 'admin') {
            $pdo->prepare("INSERT INTO ldap_settings (setting_key, setting_value) VALUES ('admin_password_hash', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$hash]);
        }
        set_flash('success', "Password pengguna berhasil diubah.");
    } else {
        set_flash('danger', "Password baru tidak boleh kosong.");
    }

    header("Location: " . BASE_URL . "/index.php?page=users");
    exit;
}

// Action: Toggle User Status
if ($action === 'toggle_user_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $userId = (int)($_POST['user_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT status FROM ldap_users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentStatus = $stmt->fetchColumn();

    if ($currentStatus) {
        $newStatus = ($currentStatus === 'active') ? 'disabled' : 'active';
        $pdo->prepare("UPDATE ldap_users SET status = ? WHERE id = ?")->execute([$newStatus, $userId]);
        set_flash('info', "Status pengguna diubah menjadi " . strtoupper($newStatus) . ".");
    }

    header("Location: " . BASE_URL . "/index.php?page=users");
    exit;
}

// Action: Delete User
if ($action === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $userId = (int)($_POST['user_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT username FROM ldap_users WHERE id = ?");
    $stmt->execute([$userId]);
    $uName = $stmt->fetchColumn();

    if ($uName && $uName !== 'admin') {
        $pdo->prepare("DELETE FROM ldap_users WHERE id = ?")->execute([$userId]);
        set_flash('success', "Pengguna '{$uName}' berhasil dihapus.");
    }

    header("Location: " . BASE_URL . "/index.php?page=users");
    exit;
}

// Action: Create Group
if ($action === 'create_group' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $name = trim($_POST['name'] ?? '');
    $gid = !empty($_POST['gid_number']) ? (int)$_POST['gid_number'] : null;
    $desc = trim($_POST['description'] ?? '');

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ldap_groups (name, gid_number, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $gid, $desc ?: null]);
            set_flash('success', "Kelompok '{$name}' berhasil dibuat.");
        } catch (PDOException $e) {
            set_flash('danger', "Gagal membuat kelompok: " . $e->getMessage());
        }
    }

    header("Location: " . BASE_URL . "/index.php?page=groups");
    exit;
}

// Action: Update Group
if ($action === 'update_group' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $groupId = (int)($_POST['group_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $gid = !empty($_POST['gid_number']) ? (int)$_POST['gid_number'] : null;
    $desc = trim($_POST['description'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE ldap_groups SET name = ?, gid_number = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $gid, $desc ?: null, $groupId]);
        set_flash('success', "Kelompok berhasil diperbarui.");
    } catch (PDOException $e) {
        set_flash('danger', "Gagal update kelompok: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "/index.php?page=groups");
    exit;
}

// Action: Delete Group
if ($action === 'delete_group' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $groupId = (int)($_POST['group_id'] ?? 0);
    $pdo->prepare("DELETE FROM ldap_groups WHERE id = ?")->execute([$groupId]);
    set_flash('success', "Kelompok berhasil dihapus.");

    header("Location: " . BASE_URL . "/index.php?page=groups");
    exit;
}

// Action: Save Settings
if ($action === 'save_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $baseDn   = trim($_POST['base_dn'] ?? 'dc=internal,dc=net');
    $orgName  = trim($_POST['org_name'] ?? 'Internal Directory Service');
    $adminDn  = trim($_POST['admin_bind_dn'] ?? 'cn=admin,dc=internal,dc=net');
    $adminPwd = $_POST['admin_password'] ?? '';
    $userOu   = trim($_POST['user_ou'] ?? 'ou=users');
    $groupOu  = trim($_POST['group_ou'] ?? 'ou=groups');
    $port     = (int)($_POST['ldap_port'] ?? 389);
    $allowAnon = isset($_POST['allow_anonymous_bind']) ? '1' : '0';

    $stmt = $pdo->prepare("INSERT INTO ldap_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    $stmt->execute(['base_dn', $baseDn]);
    $stmt->execute(['org_name', $orgName]);
    $stmt->execute(['admin_bind_dn', $adminDn]);
    $stmt->execute(['user_ou', $userOu]);
    $stmt->execute(['group_ou', $groupOu]);
    $stmt->execute(['ldap_port', (string)$port]);
    $stmt->execute(['allow_anonymous_bind', $allowAnon]);

    if (!empty($adminPwd)) {
        $hash = password_hash($adminPwd, PASSWORD_BCRYPT);
        $stmt->execute(['admin_password_hash', $hash]);
        $pdo->prepare("UPDATE ldap_users SET password_hash = ? WHERE username = 'admin'")->execute([$hash]);
    }

    set_flash('success', "Pengaturan direktori LDAP berhasil disimpan.");
    header("Location: " . BASE_URL . "/index.php?page=settings");
    exit;
}

// Action: Clear Logs
if ($action === 'clear_logs' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF error");

    $pdo->query("TRUNCATE TABLE ldap_logs");
    set_flash('success', "Riwayat audit logs berhasil dibersihkan.");
    header("Location: " . BASE_URL . "/index.php?page=logs");
    exit;
}

// ========================================================
// 2. PAGE ROUTING & VIEW RENDERING
// ========================================================

if ($page === 'login') {
    if (Auth::check()) {
        header("Location: " . BASE_URL . "/index.php?page=dashboard");
        exit;
    }
    require __DIR__ . '/views/login.php';
    exit;
}

$pageMap = [
    'dashboard' => ['file' => 'dashboard.php', 'title' => 'Dashboard Overview'],
    'clients'   => ['file' => 'clients.php', 'title' => 'Connected Clients & Devices'],
    'users'     => ['file' => 'users.php', 'title' => 'Manajemen Pengguna'],
    'groups'    => ['file' => 'groups.php', 'title' => 'Manajemen Kelompok'],
    'settings'  => ['file' => 'settings.php', 'title' => 'Pengaturan Direktori'],
    'tester'    => ['file' => 'tester.php', 'title' => 'LDAP Connection Tester'],
    'logs'      => ['file' => 'logs.php', 'title' => 'Audit Logs'],
    'guide'     => ['file' => 'guide.php', 'title' => 'Panduan Integrasi'],
];

if (!isset($pageMap[$page])) {
    $page = 'dashboard';
}

$pageInfo = $pageMap[$page];
$pageTitle = $pageInfo['title'];

// Render Layout
require __DIR__ . '/views/layout/header.php';
require __DIR__ . '/views/layout/sidebar.php';
require __DIR__ . '/views/' . $pageInfo['file'];
require __DIR__ . '/views/layout/footer.php';
