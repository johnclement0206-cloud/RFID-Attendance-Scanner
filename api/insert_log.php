<?php

/*
main API for grabbing info from 
rfid_logger.py to put into SQL db
*/

$servername = "localhost";
$username = "attendance_admin";
$password = "secure_password";
$dbname = "rfid_attendance";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$student_id = $_POST['student_id'];
$student_name = $_POST['student_name'];

$sql = "INSERT INTO rfid_logs (student_id, student_name) VALUES ('$student_id', '$student_name')";

if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . " - " . $conn->error;
}

$conn->close();
?>