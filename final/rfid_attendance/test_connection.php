<?php
$host = 'localhost';
$dbname = 'rfid_attendance';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Database connection successful!";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>