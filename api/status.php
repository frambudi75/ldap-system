<?php
/**
 * API: LDAP Server Daemon Status.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/helpers.php';

$port = get_ldap_port();
$status = check_ldap_daemon_status('127.0.0.1', $port);

echo json_encode($status, JSON_PRETTY_PRINT);
