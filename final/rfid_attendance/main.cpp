#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>

// WiFi credentials
const char* SSID = "FBT_Students_5G";
const char* PASSWORD = "fbtksa786";

// API endpoint - update with your server IP
const char* API_URL = "http://192.168.56.1/rfid_attendance/api/insert_log.php";

// RFID-RC522 pins
#define RST_PIN 22   // Reset pin
#define SS_PIN 21    // Slave Select (SDA) pin

// SPI pins (ESP32 default):
// MOSI = GPIO 23
// MISO = GPIO 19
// SCK = GPIO 18

// Create MFRC522 instance
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Store student data
struct Student {
  String uid;
  String id;
  String name;
};

// Example student database - ADD YOUR STUDENTS HERE
Student students[] = {
  {"AB 12 34 56", "12345", "John Doe"},
  {"CD 78 90 EF", "67890", "Jane Smith"},
  {"11 22 33 44", "11111", "Alice Johnson"}
};
int numStudents = sizeof(students) / sizeof(students[0]);

void setup() {
  // Initialize serial monitor
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n=================================");
  Serial.println("ESP32 RFID Attendance System");
  Serial.println("=================================");
  
  // Initialize SPI bus
  SPI.begin();
  
  // Initialize MFRC522
  mfrc522.PCD_Init();
  delay(4);
  
  // Show RFID reader details
  Serial.println("\n--- RFID Reader Info ---");
  mfrc522.PCD_DumpVersionToSerial();
  Serial.println("RFID Reader initialized successfully");
  
  // Connect to WiFi
  Serial.println("\n--- WiFi Connection ---");
  connectToWiFi();
  
  Serial.println("\n=================================");
  Serial.println("System Ready!");
  Serial.println("Waiting for RFID cards...");
  Serial.println("=================================\n");
}

void loop() {
  // Look for new cards
  if (!mfrc522.PICC_IsNewCardPresent()) {
    delay(50);
    return;
  }
  
  // Select one of the cards
  if (!mfrc522.PICC_ReadCardSerial()) {
    delay(50);
    return;
  }
  
  // Read card UID
  String cardUID = getCardUID();
  Serial.println("\n--- Card Detected ---");
  Serial.println("UID: " + cardUID);
  
  // Find student by UID
  Student* student = findStudentByUID(cardUID);
  
  if (student != nullptr) {
    Serial.println("Student ID: " + student->id);
    Serial.println("Name: " + student->name);
    Serial.println("Status: Registered ✓");
    
    // Send to database
    Serial.println("\nSending to database...");
    bool success = sendLog(student->id, student->name);
    
    if (success) {
      Serial.println("✓ Attendance recorded successfully!");
    } else {
      Serial.println("✗ Failed to record attendance");
    }
    
  } else {
    Serial.println("Status: NOT REGISTERED ✗");
    Serial.println("⚠ Unknown card - Please add this UID to the system:");
    Serial.println("   {\"" + cardUID + "\", \"STUDENT_ID\", \"STUDENT_NAME\"},");
  }
  
  Serial.println("---------------------\n");
  
  // Halt PICC
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
  
  // Prevent multiple reads
  delay(2000);
  
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n⚠ WiFi disconnected! Reconnecting...");
    connectToWiFi();
  }
}

void connectToWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(SSID, PASSWORD);
  
  Serial.print("Connecting to WiFi");
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✓ WiFi Connected!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    Serial.println("\n✗ Failed to connect to WiFi");
    Serial.println("Please check:");
    Serial.println("  1. WiFi credentials are correct");
    Serial.println("  2. WiFi network is available");
    Serial.println("  3. ESP32 is in range");
  }
}

String getCardUID() {
  String content = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    content += String(mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " ");
    content += String(mfrc522.uid.uidByte[i], HEX);
  }
  content.toUpperCase();
  content.trim();
  return content;
}

Student* findStudentByUID(String uid) {
  for (int i = 0; i < numStudents; i++) {
    if (students[i].uid == uid) {
      return &students[i];
    }
  }
  return nullptr;
}

bool sendLog(String studentId, String studentName) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("✗ Error: WiFi not connected");
    return false;
  }
  
  HTTPClient http;
  
  // Set timeout
  http.setTimeout(10000); // 10 seconds
  
  http.begin(API_URL);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  StaticJsonDocument<200> doc;
  doc["student_id"] = studentId;
  doc["student_name"] = studentName;
  
  String jsonPayload;
  serializeJson(doc, jsonPayload);
  
  Serial.println("Payload: " + jsonPayload);
  Serial.print("Sending to: ");
  Serial.println(API_URL);
  
  // Send POST request
  int httpResponseCode = http.POST(jsonPayload);
  
  bool success = false;
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Response Code: ");
    Serial.println(httpResponseCode);
    Serial.print("Response: ");
    Serial.println(response);
    
    if (httpResponseCode == 200) {
      success = true;
    }
  } else {
    Serial.print("✗ HTTP Error: ");
    Serial.println(http.errorToString(httpResponseCode));
    Serial.println("Possible issues:");
    Serial.println("  1. Server is not running (check XAMPP)");
    Serial.println("  2. Wrong IP address");
    Serial.println("  3. Firewall blocking connection");
  }
  
  http.end();
  return success;
}