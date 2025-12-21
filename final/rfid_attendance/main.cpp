#include <WiFi.h>
#include <HTTPClient.h> // for connecting to PHP server
#include <ArduinoJson.h> // for sending packets
#include <SPI.h>
#include <MFRC522.h> // scanner library

// 
// PROJECT IN INTRODUCTION TO PROGRAMMING (Semester 1)
// Cybersecurity - Group 1
// Clement, Princess, Jessica, Diego, Augustus, Nouf
// 

// WiFi info (ie. wifi name and passwd)
const char* SSID = "FBT_Students_5G";
const char* PASSWORD = "fbtksa786";

// API endpoint - change IP with current IP for running server
const char* API_URL = "http://172.22.233.8/rfid_attendance/api/insert_log.php";

// RFID-RC522 pins
#define RST_PIN 27
#define SS_PIN 5

MFRC522 mfrc522(SS_PIN, RST_PIN);

// store student data
struct Student {
  String uid;
  String id;
  String name;
};

// student database, update as needed - ID tag (scan card for ID first), student ID, student name
Student students[] = {
  // {"NFC ID", "FBT2500xx", "lastname, firstname"}, 
  {"B3 1A 14 0E", "FBT250051", "Catal, Diego"},
  {"04 5C 49 AD 72 26 81", "FBT250027", "Ibrahim, John Clement"},
  {"04 65 EF AD 72 26 81", "FBT250079", "Alobaid, Nouf"},
  {"04 4D FF AE 72 26 81", "FBT250020", "Cabato, Ma. Jessica Paula"},
  {"04 FA 3E AE 72 26 81", "FBT250062", "Calisaan, Princess Gefren"},
  {"04 8D B4 AD 72 26 81", "FBT250015", "Sales, Augustus Vidal"},
  {"04 65 81 AF 72 26 81", "FBT250063", "Villanueva, Yadha"},
  {"04 BF 9B AF 72 26 81", "FBT250044", "Bulanadi, Azel"},
  {"04 7F 12 AF 72 26 81", "FBT250033", "Tanael, Salah Eldeen"},
  {"04 3C 86 AF 72 26 81", "FBT250030", "Muhammad Umar Ayubb"},
  {"xx", "FBT250019", "Talens, Nathaniel Josemari"},
  {"xx", "FBT250002", "Tejada, Abdulaziz Sara"},
  {"xx", "FBT250068", "Mohammad Abrar Khan"},
  {"xx", "FBT250065", "Muhammad Basil Shahid"},
  {"xx", "FBT250004", "Dicuangco, Justine Noel"},
  {"xx", "FBT250003", "Dicuangco, Emmanuel"},
  {"xx", "FBT2500--", "Hassen Abduljalil Wabe"},
  {"xx", "FBT2500--", "Wossan Kadi Tuna"},
  {"xx", "FBT250053", "Khalid Waleed Suliman"},
  {"xx", "FBT250086", "Druja, Mark Rowel"},
};
int numStudents = sizeof(students) / sizeof(students[0]);

void setup() {
  Serial.begin(115200); // Baud rate, always keep at 115200
  delay(1000);
  
  // ALL BELOW IS MADE TO BE SHOWN ON ARDUINO IDE'S SERIAL MONITOR, NOT ON A SCREEN MODULE

  Serial.println("\n=================================");
  Serial.println("ESP32 RFID Attendance System");
  Serial.println("=================================");
  
  // initialize SPI and MFRC522
  SPI.begin();
  mfrc522.PCD_Init();
  delay(4);
  
  Serial.println("\n--- RFID Reader Info ---");
  mfrc522.PCD_DumpVersionToSerial();
  Serial.println("RFID Reader initialized");
  
  // connect to WiFi
  Serial.println("\n--- WiFi Connection ---");
  connectToWiFi();

  Serial.println("\n=================================");
  Serial.println("System Ready! Waiting for cards...");
  Serial.println("=================================\n");
}

// RFID card scan loop
void loop() {
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    delay(50);
    return;
  }
  
  // get card UID
  String cardUID = getCardUID();
  Serial.println("\n--- Card Detected ---");
  Serial.println("UID: " + cardUID);
  
  // find student
  Student* student = findStudentByUID(cardUID);
  
  if (student != nullptr) {
    Serial.println("Student ID: " + student->id);
    Serial.println("Name: " + student->name);
    Serial.println("Status: Registered ✓");
    
    // send to database API
    Serial.println("\nSending to database...");
    bool success = sendLog(student->id, student->name);
    
    if (success) {
      Serial.println("✓ Attendance recorded!");
    } else {
      Serial.println("✗ Failed to record");
    }
  } else {
    Serial.println("Status: NOT REGISTERED ✗");
    Serial.println("Add: {\"" + cardUID + "\", \"STUDENT_ID\", \"STUDENT_NAME\"},");
  }
  
  Serial.println("---------------------\n");
  
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
  delay(2000);
  
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi lost! Reconnecting...");
    connectToWiFi();
  }
}

void connectToWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(SSID, PASSWORD);
  
  Serial.print("Connecting");
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✓ WiFi Connected!");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    Serial.print("Signal: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    Serial.println("\n✗ WiFi Failed!");
  }
}

String getCardUID() {
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uid += String(mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " ");
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  uid.trim();
  return uid;
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
    Serial.println("✗ WiFi not connected");
    return false;
  }
  
  HTTPClient http;
  http.setTimeout(10000);
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
  
  // Shows response and packet details
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
  }
  
  http.end();
  return success;
}