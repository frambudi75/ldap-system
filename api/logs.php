<?php
/**
 * API: Real-Time Audit Logs Stream.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/helpers.php';

$limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM ldap_logs ORDER BY id DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();

    foreach ($logs as &$log) {
        $log['time_ago'] = format_time_ago($log['created_at']);
    }

    echo json_encode(['success' => true, 'count' => count($logs), 'logs' => $logs]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
