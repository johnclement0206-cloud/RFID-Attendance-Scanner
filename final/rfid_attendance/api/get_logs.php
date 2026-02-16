<?php
// config.php is in parent folder
require_once '../api/config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only handle GET requests for this endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed. Use GET."]);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');
$student_id = $_GET['student_id'] ?? null;

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    
    if ($student_id) {
        $stmt = $pdo->prepare("SELECT log_id, student_id, student_name, status, tap_time FROM rfid_logs WHERE student_id = ? ORDER BY tap_time DESC LIMIT 100");
        $stmt->execute([$student_id]);
    } else {
        $stmt = $pdo->prepare("SELECT log_id, student_id, student_name, status, tap_time FROM rfid_logs WHERE DATE(tap_time) = ? ORDER BY tap_time DESC");
        $stmt->execute([$date]);
    }
    
    $logs = $stmt->fetchAll();
    
    echo json_encode([
        "success" => true,
        "count" => count($logs),
        "data" => $logs
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
}
?>