#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include "DHT.h"

#define DHTPIN D4
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);
const char* ssid     = "@CMRU-SCI";
const char* password = "";
const char* serverIP = "http://10.80.101.44:8000/weather/index.php";

void setup() {
  Serial.begin(115200);
  dht.begin();
  
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi!");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    float humidity   = dht.readHumidity();
    float temp_c     = dht.readTemperature();
    float temp_f     = dht.readTemperature(true);
    float feels_like = dht.computeHeatIndex(temp_c, humidity, false);

    // เช็คว่าเซนเซอร์อ่านค่าได้หรือไม่
    if (isnan(humidity) || isnan(temp_c) || isnan(temp_f)) {
      Serial.println("Failed to read from DHT sensor!");
      delay(2000);
      return;
    }

    // สร้าง URL พร้อมพารามิเตอร์ส่งแบบ GET
    String url = String(serverIP) + "?temp_c=" + String(temp_c) 
                                 + "&temp_f=" + String(temp_f) 
                                 + "&humidity=" + String(humidity) 
                                 + "&feels_like=" + String(feels_like);

    http.begin(client, url);

    int httpResponseCode = http.GET();

    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Server Response: " + response);
    } else {
      Serial.print("Error sending GET, code: ");
      Serial.println(httpResponseCode);
    }
    
    http.end();
  } else {
    Serial.println("WiFi Disconnected!");
  }
  delay(10000);
}