<?php
/**
 * LDAP Connection & Authentication Tester.
 * Tests Bind and Search directly via TCP socket or PHP LDAP extension.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

class LdapTester {
    public static function testConnection(string $host, int $port, string $bindDn, string $password, string $searchBase, string $searchFilter): array {
        $startTime = microtime(true);

        // Sanitize host: strip ldap://, ldaps://, http:// and any embedded :port
        $host = trim($host);
        $host = preg_replace('#^(ldap|ldaps|http|https)://#i', '', $host);
        if (strpos($host, ':') !== false) {
            $parts = explode(':', $host);
            $host = $parts[0];
            if (!empty($parts[1]) && is_numeric($parts[1])) {
                $port = (int)$parts[1];
            }
        }
        $host = trim($host, '/');

        // Auto-resolve localhost/127.0.0.1 to internal container host if inside Docker network
        $envLdapHost = getenv('LDAP_HOST');
        if (($host === '127.0.0.1' || $host === 'localhost') && $envLdapHost && $envLdapHost !== '0.0.0.0' && $envLdapHost !== '127.0.0.1') {
            $host = $envLdapHost;
        } elseif (($host === '127.0.0.1' || $host === 'localhost')) {
            // Check if ldap-daemon hostname is reachable in docker network
            $testSock = @fsockopen('ldap-daemon', $port, $e, $es, 0.3);
            if (is_resource($testSock)) {
                fclose($testSock);
                $host = 'ldap-daemon';
            }
        }

        // 1. Check if PHP LDAP extension is installed
        if (function_exists('ldap_connect')) {
            return self::testViaPhpLdap($host, $port, $bindDn, $password, $searchBase, $searchFilter, $startTime);
        }

        // 2. Direct Socket Fallback & Verification against MySQL backend
        return self::testViaDirectVerification($host, $port, $bindDn, $password, $searchBase, $searchFilter, $startTime);
    }

    private static function testViaPhpLdap(string $host, int $port, string $bindDn, string $password, string $searchBase, string $searchFilter, float $startTime): array {
        $ldapUrl = "ldap://{$host}:{$port}";
        $conn = @ldap_connect($ldapUrl);
        if (!$conn) {
            return [
                'success' => false,
                'stage' => 'CONNECT',
                'message' => "Gagal terhubung ke {$ldapUrl}",
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 3);

        // Try Bind
        $bind = @ldap_bind($conn, $bindDn, $password);
        if (!$bind) {
            $err = ldap_error($conn);
            $errno = ldap_errno($conn);
            ldap_close($conn);
            return [
                'success' => false,
                'stage' => 'BIND',
                'message' => "Autentikasi Bind Gagal: {$err} (Code: {$errno})",
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }

        // Try Search if searchBase provided
        $entries = [];
        if (!empty($searchBase)) {
            $filter = !empty($searchFilter) ? $searchFilter : '(objectClass=*)';
            $search = @ldap_search($conn, $searchBase, $filter);
            if ($search) {
                $rawEntries = ldap_get_entries($conn, $search);
                for ($i = 0; $i < $rawEntries['count']; $i++) {
                    $item = ['dn' => $rawEntries[$i]['dn']];
                    for ($j = 0; $j < $rawEntries[$i]['count']; $j++) {
                        $attrName = $rawEntries[$i][$j];
                        $item[$attrName] = $rawEntries[$i][$attrName][0] ?? '';
                    }
                    $entries[] = $item;
                }
            }
        }

        ldap_unbind($conn);

        return [
            'success' => true,
            'stage' => 'COMPLETED',
            'message' => "Autentikasi Bind berhasil! Server merespons dengan kode 0 (SUCCESS).",
            'entries_found' => count($entries),
            'entries' => $entries,
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
        ];
    }

    private static function testViaDirectVerification(string $host, int $port, string $bindDn, string $password, string $searchBase, string $searchFilter, float $startTime): array {
        // Test TCP Port connection first
        $sock = @fsockopen($host, $port, $errno, $errstr, 2);
        if (!$sock) {
            return [
                'success' => false,
                'stage' => 'CONNECT',
                'message' => "Port {$port} tidak dapat dijangkau ({$errstr}). Pastikan daemon server sedang berjalan.",
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }
        fclose($sock);

        // Verify credentials in MySQL database
        $pdo = Database::getConnection();
        $baseDn = get_base_dn();
        $adminDn = get_admin_bind_dn();

        $bindSuccess = false;
        $bindType = '';

        if (empty($bindDn) && empty($password)) {
            $allowAnon = get_setting('allow_anonymous_bind', '1');
            if ($allowAnon === '1') {
                $bindSuccess = true;
                $bindType = 'Anonymous Bind';
            }
        } elseif (strcasecmp($bindDn, $adminDn) === 0 || in_array(strtolower($bindDn), ['cn=admin', 'admin'])) {
            $stmt = $pdo->query("SELECT setting_value FROM ldap_settings WHERE setting_key = 'admin_password_hash' LIMIT 1");
            $hash = $stmt->fetchColumn();
            if ($hash && password_verify($password, $hash)) {
                $bindSuccess = true;
                $bindType = 'Admin Root Bind';
            }
        } else {
            // Extract username from DN
            $username = $bindDn;
            if (preg_match('/uid=([^,]+)/i', $bindDn, $m)) {
                $username = $m[1];
            }
            $stmt = $pdo->prepare("SELECT * FROM ldap_users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
            $stmt->execute([$username, $username]);
            $u = $stmt->fetch();
            if ($u && password_verify($password, $u['password_hash'])) {
                $bindSuccess = true;
                $bindType = "User Bind ({$u['username']})";
            }
        }

        if (!$bindSuccess) {
            return [
                'success' => false,
                'stage' => 'BIND',
                'message' => "Autentikasi Bind Gagal: Invalid credentials atau akun dinonaktifkan.",
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ];
        }

        // Fetch sample search entries from DB
        $stmt = $pdo->query("SELECT username, full_name, email, ou, telephone, department FROM ldap_users WHERE status = 'active'");
        $users = $stmt->fetchAll();
        $entries = [];
        foreach ($users as $u) {
            $entries[] = [
                'dn' => "uid={$u['username']},ou={$u['ou']},{$baseDn}",
                'uid' => $u['username'],
                'cn' => $u['full_name'],
                'mail' => $u['email'],
                'ou' => $u['ou']
            ];
        }

        return [
            'success' => true,
            'stage' => 'COMPLETED',
            'bind_type' => $bindType,
            'message' => "Koneksi ke port {$port} OK dan autentikasi {$bindType} valid!",
            'entries_found' => count($entries),
            'entries' => $entries,
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
        ];
    }
}
