main.cpp is to be placed in an ESP32 chip using Arduino IDE.
You must install the libraries for ESP32 (by Espressif) and ones in the <include> headings. (ie. MFRC522)
main.cpp does not need the XAMPP server or audio_server.py to run completely. Instead it will provide an error message for either lacking component, but still runs and reads the NFC tags used.

audio_server.py must be run in the background for the correct haptic feedback.

All .php files are used in an XAMPP server.
File structure in the XAMPP server should be in the following format:
C:\
└── xampp\
    └── htdocs\
        └── rfid_attendance\
            └── api\
                ├── insert_log.php
                └── get_logs.php
            └── dashboard\
                └── index.php
