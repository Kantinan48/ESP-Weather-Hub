# 🌤️ LAB 6: ESP8266 Local Weather Station Dashboard

ระบบตรวจวัดอุณหภูมิและความชื้นแบบ Real-Time ด้วย **ESP8266** และ **DHT11** บันทึกข้อมูลลงฐานข้อมูล **MySQL (phpMyAdmin)** และแสดงผลบน **Web Dashboard** ผ่าน Web Server (XAMPP)

---

## 📌 คุณสมบัติของระบบ (Features)
- 🌡️ **Real-Time Data Collection**: รับค่าอุณหภูมิ (°C, °F) ความชื้น (%) และ Feels Like จาก ESP8266 ทุกๆ 10 วินาที
- 🗄️ **Database Storage**: บันทึกข้อมูลลงฐานข้อมูล MySQL บน Localhost อัตโนมัติพร้อม Stamp เวลา
- 📊 **Dynamic Dashboard**: หน้าเว็บ UI ทันสมัย แสดงผลข้อมูลปัจจุบัน สถิติ (Min/Max/Avg) และตารางประวัติย้อนหลัง 5 รายการ
- 🔄 **Auto Refresh**: หน้า Dashboard อัปเดตข้อมูลอัตโนมัติด้วย JavaScript (Fetch API) โดยไม่ต้องกด Refresh หน้าเว็บ

---

## 🛠️ อุปกรณ์และเทคโนโลยีที่ใช้ (Tech Stack & Hardware)

### Hardware
* **Microcontroller**: ESP8266 (NodeMCU)
* **Sensor**: DHT11 (Temperature & Humidity Sensor)

### Software & Backend
* **Web Server**: Apache (XAMPP Port 8000)
* **Database**: MySQL / phpMyAdmin (`weather_db`)
* **Backend Language**: PHP

### Frontend UI
* **Structure & Style**: HTML5, CSS3 (Modern Dark Theme)
* **Scripting**: JavaScript (AJAX / Fetch API)

---

## 📂 โครงสร้างฐานข้อมูล (Database Schema)

**Database Name:** `weather_db`  
**Table Name:** `dht_data`

```sql
CREATE TABLE dht_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temp_c FLOAT NOT NULL,
    temp_f FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    feels_like FLOAT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
