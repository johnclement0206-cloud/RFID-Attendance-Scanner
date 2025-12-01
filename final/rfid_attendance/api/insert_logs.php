<?php
require_once 'config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

$student_id = $data['student_id'] ?? null;
$student_name = $data['student_name'] ?? null;

// Validate input
if (!$student_id || !$student_name) {
    http_response_code(400);
    echo json_encode(["error" => "Missing student_id or student_name"]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("INSERT INTO rfid_logs (student_id, student_name) VALUES (?, ?)");
    $stmt->execute([$student_id, $student_name]);
    
    echo json_encode([
        "success" => true,
        "message" => "Attendance recorded successfully",
        "log_id" => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>