<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Attendance Dashboard</title>
    <style>
        :root {
            --bg-primary: #f4f4f4;
            --bg-secondary: white;
            --text-primary: #333;
            --text-secondary: #666;
            --border-color: #ddd;
            --hover-bg: #f5f5f5;
            --accent-color: #4CAF50;
            --table-header-bg: #4CAF50;
            --table-header-text: white;
            --success-bg: #d4edda;
            --success-text: #155724;
            --warning-bg: #fff3cd;
            --warning-text: #856404;
            --danger-bg: #f8d7da;
            --danger-text: #721c24;
            --neutral-bg: #d1ecf1;
            --neutral-text: #0c5460;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --border-color: #404040;
            --hover-bg: #3a3a3a;
            --accent-color: #66BB6A;
            --table-header-bg: #388E3C;
            --table-header-text: #e0e0e0;
            --success-bg: #1e4620;
            --success-text: #a3d9a5;
            --warning-bg: #664d03;
            --warning-text: #ffecb5;
            --danger-bg: #58151c;
            --danger-text: #f8b4bc;
            --neutral-bg: #0a3d62;
            --neutral-text: #9ad3de;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        h1 {
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h2 {
            color: var(--text-primary);
        }

        .theme-toggle {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            color: var(--text-primary);
        }

        .theme-toggle:hover {
            background: var(--hover-bg);
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-secondary);
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        th {
            background-color: var(--table-header-bg);
            color: var(--table-header-text);
        }

        tr:hover {
            background-color: var(--hover-bg);
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 8px;
            flex: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .stat-box h3 {
            margin: 0;
            color: var(--text-secondary);
        }

        .stat-box p {
            font-size: 32px;
            margin: 10px 0 0 0;
            color: var(--accent-color);
            font-weight: bold;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }

        .status-tap-in {
            background-color: var(--success-bg);
            color: var(--success-text);
        }

        .status-tap-out {
            background-color: var(--neutral-bg);
            color: var(--neutral-text);
        }

        .status-tardy-in {
            background-color: var(--danger-bg);
            color: var(--danger-text);
        }

        /* Legend */
        .legend {
            background: var(--bg-secondary);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .legend-title {
            font-weight: bold;
            margin-right: 10px;
            color: var(--text-primary);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <h1>
        <span>📋 RFID Attendance System</span>
        <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
            <span id="themeIcon">🌙</span> Dark Mode
        </button>
    </h1>
    
    <div class="stats">
        <div class="stat-box">
            <h3>Today's Scans</h3>
            <p id="todayCount">-</p>
        </div>
        <div class="stat-box">
            <h3>Unique Students</h3>
            <p id="uniqueCount">-</p>
        </div>
        <div class="stat-box">
            <h3>On Time</h3>
            <p id="onTimeCount" style="color: #4CAF50;">-</p>
        </div>
        <div class="stat-box">
            <h3>Tardy</h3>
            <p id="tardyCount" style="color: #f44336;">-</p>
        </div>
    </div>

    <div class="legend">
        <span class="legend-title">Status Legend:</span>
        <div class="legend-item">
            <span class="status-badge status-tap-in">Tap In</span>
            <span>On time arrival (before 9:30 AM)</span>
        </div>
        <div class="legend-item">
            <span class="status-badge status-tardy-in">Tardy In</span>
            <span>Late arrival (after 9:30 AM)</span>
        </div>
        <div class="legend-item">
            <span class="status-badge status-tap-out">Tap Out</span>
            <span>Departure</span>
        </div>
    </div>
    
    <h2>Today's Attendance Logs</h2>
    <table id="logsTable">
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Status</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody id="logsBody">
            <tr><td colspan="5">Loading...</td></tr>
        </tbody>
    </table>

    <script>
        // Tardy cutoff time (9:30 AM)
        const TARDY_CUTOFF_HOUR = 9;
        const TARDY_CUTOFF_MINUTE = 30;

        // Theme management
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeButton(newTheme);
        }

        function updateThemeButton(theme) {
            const button = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            
            if (theme === 'dark') {
                icon.textContent = '☀️';
                button.innerHTML = '<span id="themeIcon">☀️</span> Light Mode';
            } else {
                icon.textContent = '🌙';
                button.innerHTML = '<span id="themeIcon">🌙</span> Dark Mode';
            }
        }

        // Load saved theme preference
        function loadTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeButton(savedTheme);
        }

        // Check if a tap time is tardy (after 9:30 AM)
        function isTardy(tapTime) {
            const date = new Date(tapTime);
            const hours = date.getHours();
            const minutes = date.getMinutes();
            
            // Convert to total minutes for easy comparison
            const tapMinutes = hours * 60 + minutes;
            const cutoffMinutes = TARDY_CUTOFF_HOUR * 60 + TARDY_CUTOFF_MINUTE;
            
            return tapMinutes > cutoffMinutes;
        }

        // Determine actual status based on database status and time
        function determineActualStatus(dbStatus, tapTime) {
            // If it's a Tap Out, always return Tap Out
            if (dbStatus === 'Tap Out') {
                return 'Tap Out';
            }
            
            // For Tap In or Tardy In, check the actual time
            if (dbStatus === 'Tap In' || dbStatus === 'Tardy In') {
                // Check if the tap time is actually after 9:30 AM
                if (isTardy(tapTime)) {
                    return 'Tardy In';
                } else {
                    return 'Tap In';
                }
            }
            
            // Fallback to database status
            return dbStatus;
        }

        // Get status badge class
        function getStatusBadgeClass(status) {
            if (status === 'Tap In') return 'status-tap-in';
            if (status === 'Tardy In') return 'status-tardy-in';
            if (status === 'Tap Out') return 'status-tap-out';
            return 'status-tap-in';
        }

        // Load logs function
        async function loadLogs() {
            try {
                const response = await fetch('../api/get_logs.php');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('logsBody');
                    tbody.innerHTML = '';
                    
                    // Update total count
                    document.getElementById('todayCount').textContent = result.count;
                    
                    // Track unique students and their first tap status
                    const uniqueStudents = new Set();
                    const onTimeStudents = new Set();
                    const tardyStudents = new Set();
                    
                    // Create a map to track first entry for each student today
                    const firstTapMap = new Map();
                    
                    // Sort logs by time (oldest first) to find first tap
                    const sortedLogs = [...result.data].reverse();
                    
                    sortedLogs.forEach(log => {
                        uniqueStudents.add(log.student_id);
                        
                        // Only check first tap (Tap In or Tardy In)
                        if (!firstTapMap.has(log.student_id)) {
                            const actualStatus = determineActualStatus(log.status, log.tap_time);
                            
                            // Only count if it's a Tap In or Tardy In (not Tap Out)
                            if (actualStatus === 'Tap In' || actualStatus === 'Tardy In') {
                                firstTapMap.set(log.student_id, actualStatus);
                                
                                if (actualStatus === 'Tap In') {
                                    onTimeStudents.add(log.student_id);
                                } else if (actualStatus === 'Tardy In') {
                                    tardyStudents.add(log.student_id);
                                }
                            }
                        }
                    });
                    
                    document.getElementById('uniqueCount').textContent = uniqueStudents.size;
                    document.getElementById('onTimeCount').textContent = onTimeStudents.size;
                    document.getElementById('tardyCount').textContent = tardyStudents.size;
                    
                    // Display logs (most recent first - original order)
                    result.data.forEach(log => {
                        // Determine the actual status based on time
                        const actualStatus = determineActualStatus(log.status, log.tap_time);
                        const statusClass = getStatusBadgeClass(actualStatus);
                        
                        const row = tbody.insertRow();
                        row.innerHTML = `
                            <td>${log.log_id}</td>
                            <td>${log.student_id}</td>
                            <td>${log.student_name}</td>
                            <td><span class="status-badge ${statusClass}">${actualStatus}</span></td>
                            <td>${new Date(log.tap_time).toLocaleString()}</td>
                        `;
                    });
                    
                    // If no logs, show message
                    if (result.count === 0) {
                        const row = tbody.insertRow();
                        row.innerHTML = '<td colspan="5" style="text-align: center;">No attendance logs for today</td>';
                    }
                }
            } catch (error) {
                console.error('Error loading logs:', error);
                document.getElementById('logsBody').innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error loading logs</td></tr>';
            }
        }
        
        // Initialize theme and load data
        loadTheme();
        loadLogs();
        
        // Auto-refresh every 5 seconds
        setInterval(loadLogs, 5000);
    </script>
</body>
</html>