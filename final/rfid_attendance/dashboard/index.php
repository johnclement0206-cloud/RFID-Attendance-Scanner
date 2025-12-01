<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Attendance Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-box { background: white; padding: 20px; border-radius: 8px; flex: 1; }
        .stat-box h3 { margin: 0; color: #666; }
        .stat-box p { font-size: 32px; margin: 10px 0 0 0; color: #4CAF50; }
    </style>
</head>
<body>
    <h1>📋 RFID Attendance System</h1>
    
    <div class="stats">
        <div class="stat-box">
            <h3>Today's Scans</h3>
            <p id="todayCount">-</p>
        </div>
        <div class="stat-box">
            <h3>Unique Students</h3>
            <p id="uniqueCount">-</p>
        </div>
    </div>
    
    <h2>Recent Attendance Logs</h2>
    <table id="logsTable">
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody id="logsBody">
            <tr><td colspan="4">Loading...</td></tr>
        </tbody>
    </table>

    <script>
        async function loadLogs() {
            try {
                const response = await fetch('../api/get_logs.php');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('logsBody');
                    tbody.innerHTML = '';
                    
                    document.getElementById('todayCount').textContent = result.count;
                    
                    // Count unique students
                    const uniqueStudents = new Set(result.data.map(log => log.student_id));
                    document.getElementById('uniqueCount').textContent = uniqueStudents.size;
                    
                    result.data.forEach(log => {
                        const row = tbody.insertRow();
                        row.innerHTML = `
                            <td>${log.log_id}</td>
                            <td>${log.student_id}</td>
                            <td>${log.student_name}</td>
                            <td>${new Date(log.tap_time).toLocaleString()}</td>
                        `;
                    });
                }
            } catch (error) {
                console.error('Error loading logs:', error);
            }
        }
        
        // Load logs on page load
        loadLogs();
        
        // Auto-refresh every 5 seconds
        setInterval(loadLogs, 5000);
    </script>
</body>
</html>