<?php
require_once 'config.php';

// Optional: Get logs for specific date or student
$date = $_GET['date'] ?? date('Y-m-d');
$student_id = $_GET['student_id'] ?? null;

try {
    $pdo = getDBConnection();
    
    if ($student_id) {
        // Get logs for specific student
        $stmt = $pdo->prepare("SELECT * FROM rfid_logs WHERE student_id = ? ORDER BY tap_time DESC LIMIT 100");
        $stmt->execute([$student_id]);
    } else {
        // Get all logs for today
        $stmt = $pdo->prepare("SELECT * FROM rfid_logs WHERE DATE(tap_time) = ? ORDER BY tap_time DESC");
        $stmt->execute([$date]);
    }
    
    $logs = $stmt->fetchAll();
    
    echo json_encode([
        "success" => true,
        "count" => count($logs),
        "data" => $logs
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>