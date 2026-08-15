<?php
/**
 * API: LDAP Connection Tester Endpoint.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/ldap_tester.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$host = $input['host'] ?? '127.0.0.1';
$port = (int)($input['port'] ?? 389);
$bindDn = trim($input['bind_dn'] ?? '');
$password = $input['password'] ?? '';
$searchBase = trim($input['search_base'] ?? '');
$searchFilter = trim($input['search_filter'] ?? '');

$result = LdapTester::testConnection($host, $port, $bindDn, $password, $searchBase, $searchFilter);
echo json_encode($result);
