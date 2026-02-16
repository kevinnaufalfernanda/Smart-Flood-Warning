/*
 * ============================================================
 *  NodeMCU ESP8266 — Smart Flood Warning Sensor
 *  Mengirim data jarak sensor ultrasonik ke Web Dashboard
 * ============================================================
 *  Wiring:
 *    HC-SR04 VCC  → NodeMCU VIN (5V)
 *    HC-SR04 GND  → NodeMCU GND
 *    HC-SR04 TRIG → NodeMCU D1 (GPIO5)
 *    HC-SR04 ECHO → NodeMCU D2 (GPIO4) *via voltage divider
 *    Buzzer (+)   → NodeMCU D5 (GPIO14)
 *    LED Hijau    → NodeMCU D6 (GPIO12) + Resistor 220Ω
 *    LED Merah    → NodeMCU D7 (GPIO13) + Resistor 220Ω
 * ============================================================
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

// ========== KONFIGURASI WiFi ==========
const char* ssid     = "NAMA_WIFI_KAMU";      // Ganti dengan nama WiFi
const char* password = "PASSWORD_WIFI_KAMU";   // Ganti dengan password WiFi

// ========== KONFIGURASI SERVER ==========
// IP Address laptop yang menjalankan server Laravel
// Cek dengan perintah: ipconfig (Windows) atau ifconfig (Linux)
const char* serverUrl = "http://192.168.1.100:8000/api/sensor";
const int deviceId = 1;  // ID perangkat di database

// ========== PIN KONFIGURASI ==========
const int TRIG_PIN   = D1;  // GPIO5
const int ECHO_PIN   = D2;  // GPIO4
const int BUZZER_PIN = D5;  // GPIO14
const int LED_GREEN  = D6;  // GPIO12
const int LED_RED    = D7;  // GPIO13

// ========== THRESHOLD (sama dengan di web) ==========
const float DANGER_THRESHOLD  = 5.0;   // cm - BAHAYA
const float WARNING_THRESHOLD = 7.0;   // cm - SIAGA
const float SAFE_THRESHOLD    = 10.0;  // cm - AMAN

// ========== INTERVAL ==========
const unsigned long SEND_INTERVAL = 3000;  // Kirim data setiap 3 detik
unsigned long lastSendTime = 0;

void setup() {
    Serial.begin(115200);
    Serial.println();
    Serial.println("================================");
    Serial.println(" Smart Flood Warning System");
    Serial.println(" NodeMCU ESP8266 + HC-SR04");
    Serial.println("================================");

    // Setup pin
    pinMode(TRIG_PIN, OUTPUT);
    pinMode(ECHO_PIN, INPUT);
    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(LED_GREEN, OUTPUT);
    pinMode(LED_RED, OUTPUT);

    // Matikan semua output
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_GREEN, LOW);
    digitalWrite(LED_RED, LOW);

    // Konek ke WiFi
    Serial.print("Menghubungkan ke WiFi: ");
    Serial.println(ssid);
    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
        // Blink LED merah saat mencoba konek
        digitalWrite(LED_RED, !digitalRead(LED_RED));
    }

    Serial.println();
    Serial.println("WiFi Terhubung!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());

    // Indikator berhasil konek: LED hijau nyala
    digitalWrite(LED_GREEN, HIGH);
    digitalWrite(LED_RED, LOW);
    delay(1000);
}

void loop() {
    unsigned long currentTime = millis();

    if (currentTime - lastSendTime >= SEND_INTERVAL) {
        lastSendTime = currentTime;

        // 1. Baca sensor ultrasonik
        float distance = readUltrasonic();

        // 2. Tentukan status & kontrol LED/Buzzer
        handleStatus(distance);

        // 3. Kirim data ke server
        sendToServer(distance);

        // Debug output
        Serial.print("Jarak: ");
        Serial.print(distance);
        Serial.print(" cm | Status: ");
        if (distance <= DANGER_THRESHOLD) Serial.println("BAHAYA!");
        else if (distance <= WARNING_THRESHOLD) Serial.println("SIAGA");
        else Serial.println("AMAN");
    }
}

// ==========================================
// Membaca jarak dari sensor HC-SR04
// ==========================================
float readUltrasonic() {
    // CleanTrigger
    digitalWrite(TRIG_PIN, LOW);
    delayMicroseconds(2);

    // Kirim pulse 10μs
    digitalWrite(TRIG_PIN, HIGH);
    delayMicroseconds(10);
    digitalWrite(TRIG_PIN, LOW);

    // Baca durasi echo (dalam mikrodetik)
    long duration = pulseIn(ECHO_PIN, HIGH, 30000); // Timeout 30ms

    // Hitung jarak: kecepatan suara = 343 m/s = 0.0343 cm/μs
    // Jarak = (durasi × 0.0343) / 2
    float distance = (duration * 0.0343) / 2.0;

    // Validasi: jika timeout atau error, kembalikan nilai besar
    if (distance <= 0 || distance > 400) {
        distance = 999;
    }

    return distance;
}

// ==========================================
// Kontrol LED dan Buzzer berdasarkan status
// ==========================================
void handleStatus(float distance) {
    if (distance <= DANGER_THRESHOLD) {
        // BAHAYA: LED Merah ON, Buzzer ON
        digitalWrite(LED_RED, HIGH);
        digitalWrite(LED_GREEN, LOW);
        // Buzzer bunyi intermiten
        tone(BUZZER_PIN, 1000, 200);  // 1kHz selama 200ms
    }
    else if (distance <= WARNING_THRESHOLD) {
        // SIAGA: Kedua LED kedap-kedip bergantian
        digitalWrite(LED_RED, HIGH);
        digitalWrite(LED_GREEN, HIGH);
        digitalWrite(BUZZER_PIN, LOW);
    }
    else {
        // AMAN: LED Hijau ON
        digitalWrite(LED_RED, LOW);
        digitalWrite(LED_GREEN, HIGH);
        digitalWrite(BUZZER_PIN, LOW);
    }
}

// ==========================================
// Kirim data jarak ke server via HTTP POST
// ==========================================
void sendToServer(float distance) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi tidak terhubung! Mencoba reconnect...");
        WiFi.reconnect();
        return;
    }

    WiFiClient client;
    HTTPClient http;

    http.begin(client, serverUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Kirim data
    String postData = "distance=" + String(distance, 2) + "&device_id=" + String(deviceId);

    Serial.print("Mengirim: ");
    Serial.println(postData);

    int httpCode = http.POST(postData);

    if (httpCode > 0) {
        Serial.print("Response code: ");
        Serial.println(httpCode);

        if (httpCode == 201) {
            String response = http.getString();
            Serial.println("Server: " + response);
        }
    } else {
        Serial.print("Error: ");
        Serial.println(http.errorToString(httpCode));
    }

    http.end();
}
