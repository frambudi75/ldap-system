<?php
/**
 * Utility functions and helpers.
 */

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function check_ldap_daemon_status(?string $host = null, ?int $port = null, float $timeout = 0.5): array {
    $displayPort = function_exists('get_ldap_port') ? get_ldap_port() : 389;
    $internalHost = getenv('LDAP_HOST') ?: 'ldap-daemon';
    $internalPort = (int)(getenv('LDAP_PORT') ?: 389);

    // Candidates to test
    $targets = [];
    if ($host !== null && $port !== null) {
        $targets[] = [$host, $port];
    }
    // Docker internal network
    $targets[] = [$internalHost, $internalPort];
    $targets[] = ['ldap-daemon', 389];
    $targets[] = ['127.0.0.1', $internalPort];
    if ($displayPort !== $internalPort) {
        $targets[] = ['127.0.0.1', $displayPort];
    }

    $socket = false;
    $connectedHost = $host ?: $internalHost;
    $connectedPort = $port ?: $internalPort;

    foreach ($targets as [$tHost, $tPort]) {
        $sock = @fsockopen($tHost, $tPort, $errno, $errstr, $timeout);
        if (is_resource($sock)) {
            $socket = $sock;
            $connectedHost = $tHost;
            $connectedPort = $tPort;
            break;
        }
    }

    $status_file = __DIR__ . '/../daemon/server_status.json';
    $meta = [];
    if (file_exists($status_file)) {
        $json_data = @json_decode(file_get_contents($status_file), true);
        if (is_array($json_data)) {
            $meta = $json_data;
        }
    }

    if (is_resource($socket)) {
        fclose($socket);
        return [
            'online' => true,
            'host' => $connectedHost,
            'port' => $displayPort,
            'meta' => $meta,
            'message' => "LDAP Server is active on {$connectedHost}:{$displayPort}"
        ];
    }

    return [
        'online' => false,
        'host' => $host ?: $internalHost,
        'port' => $displayPort,
        'meta' => $meta,
        'message' => "LDAP Server is stopped or port unreachable"
    ];
}

function format_time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return "Baru saja";
    if ($diff < 3600) return floor($diff / 60) . " menit lalu";
    if ($diff < 86400) return floor($diff / 3600) . " jam lalu";
    return date("d M Y H:i", $time);
}
