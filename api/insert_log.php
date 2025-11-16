<?php
/*
main API for grabbing info from 
rfid_logger.py to put into SQL db
*/

$servername = "localhost";
$username = "FBT Attendance";
$password = "admin1234";
$dbname = "rfid_attendance";
$charset = "utf8mb4";

$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

$student_id = $data['student_id'] ?? null;
$student_name = $data['student_name'] ?? null;

if (!$student_id || !$student_name) {
    http_response_code(400);
    echo json_encode(["error" => "Missing student_id or student_name"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO rfid_logs (student_id, student_name) VALUES (?, ?)");
    $stmt->execute([$student_id, $student_name]);
    echo json_encode(["success" => "New record created successfully"]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>