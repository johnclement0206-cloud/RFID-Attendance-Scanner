import urequests #type: ignore
import network #type: ignore
import time
import machine #type: ignore
import ubinascii #type: ignore

# wifi credentials - school wifi
SSID = "FBT_Students_5G"
PASSWORD = "fbtksa786"

# API endpoint - update this with server IP
API_URL = "http://<your-server-ip>/api/insert_log.php"

# RFID Reader pins (edit with actual pin used)
RFID_RX_PIN = 16
RFID_TX_PIN = 17
RFID_BAUD_RATE = 9600

# connect to wifi
wifi = network.WLAN(network.STA_IF)
wifi.active(True)
wifi.connect(SSID, PASSWORD) #type: ignore
while not wifi.isconnected():
    print("Connecting to Wi-Fi...")
    time.sleep(1)
print("Connected:", wifi.ifconfig())

# Initialize UART for RFID reader
uart = machine.UART(2, RFID_BAUD_RATE, tx=RFID_TX_PIN, rx=RFID_RX_PIN)

def send_log(student_id, student_name):
    try:
        data = {"student_id": student_id, "student_name": student_name}
        response = urequests.post(API_URL, json=data) #type: ignore
        print("Response:", response.text)
        response.close()
    except Exception as e:
        print("Error sending log:", e)

def parse_rfid_data(rfid_raw):
    """
    Parse RFID tag data format: ID,NAME
    Assumes tag stores data as: "12345,John Doe\r\n"
    """
    try:
        rfid_input = rfid_raw.decode('utf-8').strip()
        parts = rfid_input.split(",")
        if len(parts) == 2:
            return parts[0].strip(), parts[1].strip()
    except Exception as e:
        print("Error parsing RFID data:", e)
    return None, None

# Main loop - listen for RFID scanner input
print("RFID Scanner active. Waiting for tags...")
while True:
    try:
        if uart.any():
            rfid_raw = uart.read()
            student_id, student_name = parse_rfid_data(rfid_raw)
            
            if student_id and student_name:
                print(f"Tag read: ID={student_id}, Name={student_name}")
                send_log(student_id, student_name)
            else:
                print("Invalid tag format")
        
        time.sleep(0.1)  # Small delay to prevent CPU overuse
        
    except KeyboardInterrupt:
        print("Exiting...")
        break
    except Exception as e:
        print("Error:", e)
        time.sleep(1)