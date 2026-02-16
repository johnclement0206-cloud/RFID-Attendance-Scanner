#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>

// WiFi credentials
const char* SSID = "name";
const char* PASSWORD = "password";

// API endpoint - UPDATE THIS with your computer's IP
const char* API_URL = "http://ipaddress/rfid_attendance/api/insert_logs.php";

// Audio feedback server - UPDATE if needed
const char* AUDIO_SERVER = "http://ipaddress:5000";

// RFID-RC522 pins
#define RST_PIN 27
#define SS_PIN 5

MFRC522 mfrc522(SS_PIN, RST_PIN);

// Store student data
struct Student {
  String uid;
  String id;
  String name;
};

Student students[] = {
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

// Sound queue variables
bool playSoundOnScan = true;
bool playSoundOnSuccess = true;
bool playSoundOnError = true;

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n=================================");
  Serial.println("ESP32 RFID Attendance System");
  Serial.println("=================================");
  
  // Initialize SPI and MFRC522
  SPI.begin();
  mfrc522.PCD_Init();
  delay(4);
  
  Serial.println("\n--- RFID Reader Info ---");
  mfrc522.PCD_DumpVersionToSerial();
  Serial.println("RFID Reader initialized");
  
  // Connect to WiFi
  Serial.println("\n--- WiFi Connection ---");
  connectToWiFi();
  
  Serial.println("\n--- Audio Server ---");
  Serial.print("Audio Server: ");
  Serial.println(AUDIO_SERVER);
  
  Serial.println("\n=================================");
  Serial.println("System Ready! Waiting for cards...");
  Serial.println("=================================\n");
}

void loop() {
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    delay(50);
    return;
  }
  
  // Get card UID
  String cardUID = getCardUID();
  Serial.println("\n--- Card Detected ---");
  Serial.println("UID: " + cardUID);
  
  // Play scan sound immediately
  if (playSoundOnScan) {
    playSound("scan");
    delay(100); // Small delay for sound to start
  }
  
  // Find student
  Student* student = findStudentByUID(cardUID);
  
  if (student != nullptr) {
    Serial.println("Student ID: " + student->id);
    Serial.println("Name: " + student->name);
    Serial.println("Status: Registered ✓");
    
    // Send to database
    Serial.println("\nSending to database...");
    bool success = sendLog(student->id, student->name);
    
    if (success) {
      Serial.println("✓ Attendance recorded!");
      if (playSoundOnSuccess) {
        playSound("success");
      }
    } else {
      Serial.println("✗ Failed to record");
      if (playSoundOnError) {
        playSound("error");
      }
    }
  } else {
    Serial.println("Status: NOT REGISTERED ✗");
    Serial.println("Add: {\"" + cardUID + "\", \"STUDENT_ID\", \"STUDENT_NAME\"},");
    if (playSoundOnError) {
      playSound("error");
    }
  }
  
  Serial.println("---------------------\n");
  
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
  delay(2000); // Wait 2 seconds before next scan
  
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
    Serial.print("Gateway: ");
    Serial.println(WiFi.gatewayIP());
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

void playSound(String soundType) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("✗ WiFi not connected, cannot play sound");
    return;
  }
  
  Serial.print("Playing sound: ");
  Serial.println(soundType);
  
  HTTPClient http;
  String url = String(AUDIO_SERVER) + "/sound/" + soundType;
  
  http.begin(url);
  http.setTimeout(2000); // 2 second timeout for audio server
  
  int httpCode = http.POST("");
  if (httpCode > 0) {
    if (httpCode == HTTP_CODE_OK) {
      Serial.println("✓ Sound played successfully");
    } else {
      Serial.print("✗ Audio server error: ");
      Serial.println(httpCode);
    }
  } else {
    Serial.print("✗ Failed to connect to audio server: ");
    Serial.println(http.errorToString(httpCode));
  }
  
  http.end();
  delay(50); // Small delay to prevent overwhelming the server
}

bool sendLog(String studentId, String studentName) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("✗ WiFi not connected");
    return false;
  }
  
  Serial.println("\n--- DEBUG: Sending Data ---");
  Serial.print("WiFi Status: Connected (");
  Serial.print(WiFi.localIP());
  Serial.println(")");
  
  HTTPClient http;
  http.setTimeout(10000);
  
  Serial.print("Connecting to: ");
  Serial.println(API_URL);
  
  if (!http.begin(API_URL)) {
    Serial.println("✗ Failed to begin HTTP connection");
    return false;
  }
  
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  StaticJsonDocument<200> doc;
  doc["student_id"] = studentId;
  doc["student_name"] = studentName;
  
  String jsonPayload;
  serializeJson(doc, jsonPayload);
  
  Serial.print("Payload: ");
  Serial.println(jsonPayload);
  Serial.print("Payload Length: ");
  Serial.println(jsonPayload.length());
  
  // Send POST request
  Serial.println("Sending POST request...");
  int httpResponseCode = http.POST(jsonPayload);
  
  Serial.print("Response Code: ");
  Serial.println(httpResponseCode);
  
  bool success = false;
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Response Body: ");
    Serial.println(response);
    Serial.print("Response Length: ");
    Serial.println(response.length());
    
    // Parse JSON response
    StaticJsonDocument<200> responseDoc;
    DeserializationError error = deserializeJson(responseDoc, response);
    
    if (!error) {
      bool serverSuccess = responseDoc["success"];
      if (serverSuccess) {
        success = true;
        Serial.print("Status: ");
        Serial.println(responseDoc["status"].as<String>());
        Serial.print("Log ID: ");
        Serial.println(responseDoc["log_id"].as<String>());
      } else {
        Serial.print("Server error: ");
        Serial.println(responseDoc["error"].as<String>());
      }
    } else {
      Serial.print("JSON parse error: ");
      Serial.println(error.c_str());
    }
  } else {
    Serial.print("✗ HTTP Error Code: ");
    Serial.println(httpResponseCode);
    Serial.print("Error Description: ");
    Serial.println(http.errorToString(httpResponseCode));
  }
  
  http.end();
  Serial.println("--- DEBUG END ---\n");
  return success;
}

// Optional: Function to test audio server
void testAudioServer() {
  Serial.println("\nTesting audio server...");
  
  String sounds[] = {"scan", "success", "error"};
  for (int i = 0; i < 3; i++) {
    Serial.print("Testing sound: ");
    Serial.println(sounds[i]);
    playSound(sounds[i]);
    delay(2000);
  }
}