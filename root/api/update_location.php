<?php
require_once "../config.php"; // pastikan pdo $pdo sudah ada

$device = $_POST["device_id"] ?? "";
$lat    = $_POST["lat"] ?? null;
$lng    = $_POST["lng"] ?? null;
$acc    = $_POST["acc"] ?? null;
$ts     = $_POST["ts"] ?? time();
$status = $_POST["status"] ?? "online";

if (!$device) { 
    http_response_code(400); 
    echo "Missing device_id";
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO locations(device_id, lat, lng, accuracy, ts, status)
    VALUES (:device, :lat, :lng, :acc, :ts, :status)
");
$ok = $stmt->execute([
    ":device"=>$device,
    ":lat"=>$lat,
    ":lng"=>$lng,
    ":acc"=>$acc,
    ":ts"=>$ts,
    ":status"=>$status
]);

echo $ok ? "OK" : "DB ERROR";
