<?php
require_once '../api/config.php';

echo "<h1>Test API Endpoints</h1>";

// Test GET endpoint
echo "<h2>Testing GET /get_logs.php</h2>";
$url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/get_logs.php";
$response = file_get_contents($url);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test POST endpoint
echo "<h2>Testing POST /insert_logs.php</h2>";
$postData = json_encode([
    'student_id' => 'TEST' . rand(100, 999),
    'student_name' => 'Test User ' . date('H:i:s')
]);

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => $postData,
    ],
];

$context = stream_context_create($options);
$url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/insert_logs.php";
$response = file_get_contents($url, false, $context);
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>