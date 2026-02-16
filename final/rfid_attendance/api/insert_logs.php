<?php
// config.php is in parent folder
require_once '../api/config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only handle POST requests for this endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed. Use POST."]);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// If no JSON, check for form data
if (empty($data)) {
    $student_id = $_POST['student_id'] ?? null;
    $student_name = $_POST['student_name'] ?? null;
} else {
    $student_id = $data['student_id'] ?? null;
    $student_name = $data['student_name'] ?? null;
}

// Validate input
if (!$student_id || !$student_name) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing student_id or student_name"]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    
    // Get current date and time
    $current_time = new DateTime();
    $current_date = $current_time->format('Y-m-d');
    $current_hour_min = $current_time->format('H:i:s');
    
    // Define tardy cutoff time (9:30 AM)
    $tardy_cutoff = '09:30:00';
    
    // Check the last entry for this student TODAY
    $stmt = $pdo->prepare("
        SELECT status 
        FROM rfid_logs 
        WHERE student_id = ? AND DATE(tap_time) = ? 
        ORDER BY tap_time DESC 
        LIMIT 1
    ");
    $stmt->execute([$student_id, $current_date]);
    $last_entry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determine the status for this tap
    $status = 'Tap In';
    
    if ($last_entry) {
        // If last entry exists for today, toggle between Tap In and Tap Out
        if ($last_entry['status'] === 'Tap In' || $last_entry['status'] === 'Tardy In') {
            // Last was Tap In, so this should be Tap Out
            $status = 'Tap Out';
        } else {
            // Last was Tap Out, so this should be Tap In
            // Check if tardy
            if ($current_hour_min > $tardy_cutoff) {
                $status = 'Tardy In';
            } else {
                $status = 'Tap In';
            }
        }
    } else {
        // No entry today, this is first tap - check if tardy
        if ($current_hour_min > $tardy_cutoff) {
            $status = 'Tardy In';
        } else {
            $status = 'Tap In';
        }
    }
    
    // Insert the new log entry with status
    $stmt = $pdo->prepare("INSERT INTO rfid_logs (student_id, student_name, status) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $student_name, $status]);
    
    echo json_encode([
        "success" => true,
        "message" => "Attendance recorded successfully",
        "log_id" => $pdo->lastInsertId(),
        "status" => $status,
        "student_id" => $student_id,
        "student_name" => $student_name
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>