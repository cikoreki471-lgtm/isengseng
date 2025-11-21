<?php
// api/get_locations.php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$since = isset($_GET['since']) ? (int) $_GET['since'] : 0;

try {
    if ($since > 0) {
        $stmt = $pdo->prepare("SELECT device_id,label,lat,lng,accuracy,ts,last_seen FROM devices WHERE ts > ? ORDER BY last_seen DESC");
        $stmt->execute([$since]);
    } else {
        $stmt = $pdo->query("SELECT device_id,label,lat,lng,accuracy,ts,last_seen FROM devices ORDER BY last_seen DESC");
    }
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','msg'=>$e->getMessage()]);
}
