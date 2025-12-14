<?php
// Database configuration - FOR XAMPP DEFAULT SETUP
define('DB_HOST', 'localhost');
define('DB_NAME', 'rfid_attendance');
define('DB_USER', 'root');          
define('DB_PASS', '');               
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection with detailed error reporting
function getDBConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log detailed error for debugging
        error_log("Database connection error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode([
            "error" => "Database connection failed",
            "details" => $e->getMessage()  // Shows actual error for debugging
        ]));
    }
}

// Enable CORS (allow ESP32 to connect)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
?>
