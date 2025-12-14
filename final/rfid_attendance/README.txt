main.cpp is to be placed in an ESP32 chip using Arduino IDE.

You must install the libraries for ESP32 (by Espressif) and ones in the <include> headings.
All .php files are used in an XAMPP server.

Your file structure should be in the following format

C:\
└── xampp\
    └── htdocs\
        └── rfid_attendance\
            └── api\
                ├── insert_log.php
                └── get_logs.php
            └── dashboard\
                └── index.php