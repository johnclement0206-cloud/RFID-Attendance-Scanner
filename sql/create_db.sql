CREATE DATABASE rfid_attendance;
USE rfid_attendance;

CREATE TABLE rfid_logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(20) NOT NULL,
  student_name VARCHAR(100) NOT NULL,
  tap_time DATETIME DEFAULT CURRENT_TIMESTAMP
);