<?php
// ==========================================
// 1. ส่วนเชื่อมต่อฐานข้อมูล Localhost & บันทึกค่าจาก ESP8266
// ==========================================
$servername = "localhost";
$username   = "root";
$password   = ""; // XAMPP เริ่มต้นไม่มีรหัสผ่าน
$dbname     = "weather_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รองรับทั้ง GET และ POST จาก ESP8266
if (isset($_REQUEST['temp_c']) && isset($_REQUEST['humidity'])) {
    $temp_c     = floatval($_REQUEST['temp_c']);
    $humidity   = floatval($_REQUEST['humidity']);
    
    // คำนวณค่า °F และ Feels Like (กรณีไม่ได้ส่งมา)
    $temp_f     = isset($_REQUEST['temp_f']) ? floatval($_REQUEST['temp_f']) : ($temp_c * 9/5) + 32;
    $feels_like = isset($_REQUEST['feels_like']) ? floatval($_REQUEST['feels_like']) : $temp_c;

    $stmt = $conn->prepare("INSERT INTO dht_data (temp_c, temp_f, humidity, feels_like) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("dddd", $temp_c, $temp_f, $humidity, $feels_like);
    
    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
    exit(); // จบการทำงานสำหรับ Request ที่ส่งมาจาก ESP8266
}

// ==========================================
// 2. ส่วน API สำหรับส่งข้อมูล JSON ให้ JavaScript (ทุก 10 วินาที)
// ==========================================
if (isset($_GET['api']) && $_GET['api'] == 'get_data') {
    header('Content-Type: application/json');
    
    // ดึงค่าล่าสุด 1 รายการ
    $latest_sql = "SELECT * FROM dht_data ORDER BY id DESC LIMIT 1";
    $latest_res = $conn->query($latest_sql);
    $latest = ($latest_res && $latest_res->num_rows > 0) ? $latest_res->fetch_assoc() : null;

    // ดึงค่าสถิติ Min, Max, Avg
    $stat_sql = "SELECT MAX(temp_c) as max_temp, MIN(temp_c) as min_temp, AVG(humidity) as avg_hum FROM dht_data";
    $stat_res = $conn->query($stat_sql);
    $stat = ($stat_res) ? $stat_res->fetch_assoc() : null;

    // ดึงข้อมูลย้อนหลัง 5 รายการ
    $history_sql = "SELECT * FROM dht_data ORDER BY id DESC LIMIT 5";
    $history_res = $conn->query($history_sql);
    $history = [];
    if ($history_res) {
        while($row = $history_res->fetch_assoc()) {
            $history[] = $row;
        }
    }

    echo json_encode([
        'latest' => $latest,
        'stat'   => $stat,
        'history'=> $history
    ]);
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP Weather Hub - Local Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0e14;
            --card-bg: #151a23;
            --card-inner: #1c2330;
            --text-primary: #ffffff;
            --text-secondary: #8a94a6;
            --accent-blue: #007bff;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --border-color: #232d3f;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-primary); padding: 30px; display: flex; justify-content: center; min-height: 100vh; }
        .dashboard-container { width: 100%; max-width: 1200px; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }

        .card { background-color: var(--card-bg); border-radius: 20px; padding: 25px; border: 1px solid var(--border-color); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .title-group h1 { font-size: 1.5rem; font-weight: 700; }
        .title-group p { color: var(--text-secondary); font-size: 0.85rem; }

        .status-badge { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .status-dot { width: 8px; height: 8px; background-color: var(--accent-green); border-radius: 50%; }

        .main-metrics { background-color: var(--card-inner); border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .temp-display { display: flex; align-items: baseline; gap: 15px; margin: 15px 0; }
        .temp-main { font-size: 4rem; font-weight: 700; }
        .feels-like { color: var(--text-secondary); font-size: 1rem; }

        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; }
        .sub-card { background-color: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; }
        .sub-card-title { color: var(--text-secondary); font-size: 0.75rem; font-weight: 500; margin-bottom: 5px; }
        .sub-card-value { font-size: 1.1rem; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; font-size: 0.85rem; }
        th { color: var(--text-secondary); border-bottom: 1px solid var(--border-color); font-weight: 500; }
        td { border-bottom: 1px solid rgba(255,255,255,0.05); }

        .info-list { display: flex; flex-direction: column; gap: 12px; margin-top: 15px; }
        .info-item { display: flex; justify-content: space-between; font-size: 0.85rem; }
        .info-item span:first-child { color: var(--text-secondary); }

        @media (max-width: 900px) { .dashboard-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- ฝั่งซ้าย: แสดงข้อมูล Real-time -->
    <div>
        <div class="card">
            <div class="header">
                <div class="title-group">
                    <h1>ESP Weather Hub (Localhost)</h1>
                    <p>สถานีตรวจวัดอุณหภูมิและความชื้น Real-Time</p>
                </div>
                <div class="status-badge">
                    <div class="status-dot"></div> ONLINE
                </div>
            </div>

            <!-- ส่วนแสดงค่าอุณหภูมิหลัก -->
            <div class="main-metrics">
                <span style="color: var(--text-secondary); font-size: 0.8rem;">ปัจจุบัน (LIVE METRICS)</span>
                <div class="temp-display">
                    <div class="temp-main"><span id="temp-c">--</span>°C</div>
                    <div class="feels-like">Feels Like <span id="feels-like">--</span>°C</div>
                </div>

                <div class="grid-3">
                    <div class="sub-card">
                        <div class="sub-card-title">ความชื้นสัมพัทธ์</div>
                        <div class="sub-card-value" style="color: var(--accent-blue);"><span id="humidity">--</span> %</div>
                    </div>
                    <div class="sub-card">
                        <div class="sub-card-title">อุณหภูมิ (°F)</div>
                        <div class="sub-card-value"><span id="temp-f">--</span> °F</div>
                    </div>
                    <div class="sub-card">
                        <div class="sub-card-title">รอบการอัปเดต</div>
                        <div class="sub-card-value" style="color: var(--accent-orange);">ทุก 10 วิ</div>
                    </div>
                </div>
            </div>

            <!-- ตารางบันทึกข้อมูลย้อนหลัง -->
            <h3 style="font-size: 1rem; margin-top: 25px; margin-bottom: 10px;">📊 ตารางข้อมูลใน MySQL (dht_data)</h3>
            <table>
                <thead>
                    <tr>
                        <th>เวลา</th>
                        <th>อุณหภูมิ (°C)</th>
                        <th>อุณหภูมิ (°F)</th>
                        <th>ความชื้น (%)</th>
                        <th>Feels Like (°C)</th>
                    </tr>
                </thead>
                <tbody id="history-table">
                    <tr><td colspan="5" style="text-align: center; color: var(--text-secondary);">กำลังโหลดข้อมูล...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ฝั่งขวา: สรุปสถิติและข้อมูล Node -->
    <div>
        <div class="card" style="margin-bottom: 20px;">
            <h3 style="font-size: 1rem; margin-bottom: 15px;">📊 สรุปสถิติข้อมูล</h3>
            <div class="info-list">
                <div class="info-item">
                    <span>อุณหภูมิสูงสุด</span>
                    <span style="font-weight: 600; color: #ef4444;"><span id="max-temp">--</span> °C</span>
                </div>
                <div class="info-item">
                    <span>อุณหภูมิต่ำสุด</span>
                    <span style="font-weight: 600; color: var(--accent-blue);"><span id="min-temp">--</span> °C</span>
                </div>
                <div class="info-item">
                    <span>ความชื้นเฉลี่ย</span>
                    <span style="font-weight: 600; color: var(--accent-green);"><span id="avg-hum">--</span> %</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="font-size: 1rem; margin-bottom: 15px;">⚙️ ข้อมูลระบบ Node ESP8266</h3>
            <div class="info-list">
                <div class="info-item"><span>Microcontroller</span><span>ESP8266 (NodeMCU)</span></div>
                <div class="info-item"><span>Sensor Module</span><span>DHT11 (Pin D4)</span></div>
                <div class="info-item"><span>Web Server</span><span>XAMPP (Localhost)</span></div>
                <div class="info-item"><span>Database</span><span>MySQL / weather_db</span></div>
                <div class="info-item"><span>Last Update</span><span id="last-update" style="color: var(--accent-orange);">--</span></div>
            </div>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันดึงข้อมูลจาก API แบบ Real-Time ทุกๆ 10 วินาที
function fetchData() {
    fetch('index.php?api=get_data')
        .then(response => response.json())
        .then(data => {
            if (data.latest) {
                document.getElementById('temp-c').innerText = parseFloat(data.latest.temp_c).toFixed(1);
                document.getElementById('temp-f').innerText = parseFloat(data.latest.temp_f).toFixed(1);
                document.getElementById('humidity').innerText = parseFloat(data.latest.humidity).toFixed(0);
                document.getElementById('feels-like').innerText = parseFloat(data.latest.feels_like).toFixed(1);
                document.getElementById('last-update').innerText = data.latest.created_at;
            }

            if (data.stat) {
                document.getElementById('max-temp').innerText = data.stat.max_temp ? parseFloat(data.stat.max_temp).toFixed(1) : '--';
                document.getElementById('min-temp').innerText = data.stat.min_temp ? parseFloat(data.stat.min_temp).toFixed(1) : '--';
                document.getElementById('avg-hum').innerText = data.stat.avg_hum ? parseFloat(data.stat.avg_hum).toFixed(0) : '--';
            }

            if (data.history && data.history.length > 0) {
                let tableHtml = '';
                data.history.forEach(row => {
                    tableHtml += `
                        <tr>
                            <td>${row.created_at}</td>
                            <td>${row.temp_c}</td>
                            <td>${row.temp_f}</td>
                            <td>${row.humidity}%</td>
                            <td>${row.feels_like}</td>
                        </tr>
                    `;
                });
                document.getElementById('history-table').innerHTML = tableHtml;
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

// เรียกดึงข้อมูลทันทีเมื่อโหลดหน้าเว็บ
fetchData();

// ตั้งเวลาอัปเดตหน้าเว็บอัตโนมัติทุกๆ 10 วินาที (10000 ms)
setInterval(fetchData, 10000);
</script>

</body>
</html>