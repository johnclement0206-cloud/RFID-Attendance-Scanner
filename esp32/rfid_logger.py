import urequests #type: ignore
import network #type: ignore
import time

# wifi credentials - school wifi
# SSID = (net name, str)
# PASSWORD = (wifi pass, keep as str)

# API endpoint
# API_URL = (need PHP web hook here) - currently in comment mode because no PHP
# PHP hook in the form of: http://(ip add of current device maintaining db)/api/insert_log.php

# connect to wifi
wifi = network.WLAN(network.STA_IF)
wifi.active(True)
wifi.connect(SSID, PASSWORD) #type: ignore
while not wifi.isconnected():
    print("Connecting to Wi-Fi...")
    time.sleep(1)
print("Connected:", wifi.ifconfig())

def send_log(student_id, student_name):
    data = {"student_id": student_id, "student_name": student_name}
    response = urequests.post(API_URL, data=data) #type: ignore
    print(response.text)
    response.close()