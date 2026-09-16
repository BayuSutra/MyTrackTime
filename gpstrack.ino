#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <Wire.h>
#include <TinyGPSPlus.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <ArduinoJson.h>

// =======================
// KONFIGURASI WIFI
// =======================
#define WIFI_SSID "Galaxy A56 5G F235"
#define WIFI_PASS "0987654321"

// =======================
// KONFIGURASI MQTT (Server)
// =======================
#define MQTT_HOST "raf6fc91.ala.us-east-1.emqxsl.com"
#define MQTT_PORT 8883
#define MQTT_USER "Admin"
#define MQTT_PASS "Admin"
#define DEVICE_ID "ITEM-VGSLLYNV"

// =======================
// PIN CONFIG ESP32-C3
// =======================
#define OLED_SDA 8
#define OLED_SCL 9
#define GPS_RX 20
#define GPS_TX 21
#define GPS_BAUD 115200
#define BUZZER_PIN 10
#define LED_PIN 2
#define BATTERY_PIN 0
#define BUTTON_PIN 3

// =======================
// VOLTAGE DIVIDER
// =======================
#define R1 220.0
#define R2 220.0
#define ADC_REF 3.3
#define ADC_RESOLUTION 4095.0

// =======================
// INTERVAL
// =======================
#define INTERVAL_WIFI     5000
#define INTERVAL_MQTT     3000
#define INTERVAL_PUBLISH  15000
#define INTERVAL_STATUS   30000
#define INTERVAL_DISPLAY  200
#define INTERVAL_DEBUG    5000

// =======================
// OBJEK
// =======================
Adafruit_SSD1306 display(128, 64, &Wire, -1);
TinyGPSPlus gps;
HardwareSerial gpsSerial(1);
WiFiClientSecure espClient;
PubSubClient mqtt(espClient);

// =======================
// VARIABEL
// =======================
unsigned long lastWiFiCheck = 0, lastMQTTCheck = 0;
unsigned long lastPublish = 0, lastStatus = 0, lastDisplay = 0, lastDebug = 0;
unsigned long wifiStartTime = 0;

float lat = 0, lng = 0, speed = 0;
int sat = 0;
float batteryVoltage = 0.0;
int batteryPercent = 0;
bool gpsLock = false, wifiReady = false, mqttReady = false;
bool wifiConnecting = false;
int wifiRetryCount = 0;

char topicLoc[60], topicStatus[60], topicCmd[60];
String lastCommand = "None";

unsigned long buttonPressTime = 0;
bool buttonPressed = false;
bool buttonProcessed = false;
bool menuChanged = false;
unsigned long lastButtonCheck = 0;

// =======================
// MENU STATE
// =======================
enum MenuState {
  MENU_MAIN,
  MENU_START,
  MENU_RUNNING,
  MENU_RESULT,
  MENU_ABOUT
};

MenuState currentState = MENU_MAIN;
int menuIndex = 0;
String menuItems[] = {"Mulai", "Tentang"};
int menuCount = 2;

// =======================
// DATA HASIL
// =======================
unsigned long startTime = 0;
unsigned long runDuration = 0;
float maxSpeed = 0;
float avgSpeed = 0;
float totalDistance = 0;
int dataCount = 0;

// =======================
// SERTIFIKAT CA
// =======================
static const char *root_ca PROGMEM = R"EOF(
-----BEGIN CERTIFICATE-----
MIIDjjCCAnagAwIBAgIQAzrx5qcRqaC7KGSxHQn65TANBgkqhkiG9w0BAQsFADBh
MQswCQYDVQQGEwJVUzEVMBMGA1UEChMMRGlnaUNlcnQgSW5jMRkwFwYDVQQLExB3
d3cuZGlnaWNlcnQuY29tMSAwHgYDVQQDExdEaWdpQ2VydCBHbG9iYWwgUm9vdCBH
MjAeFw0xMzA4MDExMjAwMDBaFw0zODAxMTUxMjAwMDBaMGExCzAJBgNVBAYTAlVT
MRUwEwYDVQQKEwxEaWdpQ2VydCBJbmMxGTAXBgNVBAsTEHd3dy5kaWdpY2VydC5j
b20xIDAeBgNVBAMTF0RpZ2lDZXJ0IEdsb2JhbCBSb290IEcyMIIBIjANBgkqhkiG
9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuzfNNNx7a8myaJCtSnX/RrohCgiN9RlUyfuI
2/Ou8jqJkTx65qsGGmvPrC3oXgkkRLpimn7Wo6h+4FR1IAWsULecYxpsMNzaHxmx
1x7e/dfgy5SDN67sH0NO3Xss0r0upS/kqbitOtSZpLYl6ZtrAGCSYP9PIUkY92eQ
q2EGnI/yuum06ZIya7XzV+hdG82MHauVBJVJ8zUtluNJbd134/tJS7SsVQepj5Wz
tCO7TG1F8PapspUwtP1MVYwnSlcUfIKdzXOS0xZKBgyMUNGPHgm+F6HmIcr9g+UQ
vIOlCsRnKPZzFBQ9RnbDhxSJITRNrw9FDKZJobq7nMWxM4MphQIDAQABo0IwQDAP
BgNVHRMBAf8EBTADAQH/MA4GA1UdDwEB/wQEAwIBhjAdBgNVHQ4EFgQUTiJUIBiV
5uNu5g/6+rkS7QYXjzkwDQYJKoZIhvcNAQELBQADggEBAGBnKJRvDkhj6zHd6mcY
1Yl9PMWLSn/pvtsrF9+wX3N3KjITOYFnQoQj8kVnNeyIv/iPsGEMNKSuIEyExtv4
NeF22d+mQrvHRAiGfzZ0JFrabA0UWTW98kndth/Jsw1HKj2ZL7tcu7XUIOGZX1NG
Fdtom/DzMNU+MeKNhJ7jitralj41E6Vf8PlwUHBHQRFXGU7Aj64GxJUTFy8bJZ91
8rGOmaFvE7FBcf6IKshPECBV1/MUReXgRPTqh5Uykw7+U0b6LJ3/iyK5S9kJRaTe
pLiaWN0bfVKfjllDiIGknibVb63dDcY3fe0Dkhvld1927jyNxF1WW6LZZm6zNTfl
MrY=
-----END CERTIFICATE-----
)EOF";

// =======================
// SETUP TOPIK
// =======================
void setupTopics() {
  snprintf(topicLoc, sizeof(topicLoc), "gps/data/%s/location", DEVICE_ID);
  snprintf(topicStatus, sizeof(topicStatus), "gps/data/%s/status", DEVICE_ID);
  snprintf(topicCmd, sizeof(topicCmd), "gps/command/%s/control", DEVICE_ID);
}

// =======================
// BUZZER FUNCTIONS
// =======================
void buzzerBeep(int duration) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(duration);
  digitalWrite(BUZZER_PIN, LOW);
}

void bipBip() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    delay(100);
  }
}

void buzzerShort() {
  buzzerBeep(50);
}

void buzzerLong() {
  buzzerBeep(200);
  delay(100);
  buzzerBeep(200);
}

// =======================
// VOLTAGE DIVIDER
// =======================
float readBatteryVoltage() {
  int adcValue = analogRead(BATTERY_PIN);
  float adcVoltage = (adcValue / ADC_RESOLUTION) * ADC_REF;
  float inputVoltage = adcVoltage * ((R1 + R2) / R2);
  return inputVoltage;
}

int voltageToPercent(float voltage) {
  float minVoltage = 4.0;
  float maxVoltage = 5.3;
  if (voltage >= maxVoltage) return 100;
  if (voltage <= minVoltage) return 0;
  int percent = (int)((voltage - minVoltage) / (maxVoltage - minVoltage) * 100.0);
  return constrain(percent, 0, 100);
}

// =======================
// KONEKSI WIFI
// =======================
void connectWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    wifiReady = true;
    wifiConnecting = false;
    wifiRetryCount = 0;
    return;
  }
  if (wifiConnecting) return;
  if (wifiRetryCount >= 5) {
    Serial.println("⚠️ WiFi max retry, restarting...");
    delay(1000);
    ESP.restart();
    return;
  }
  wifiConnecting = true;
  wifiStartTime = millis();
  wifiRetryCount++;
  Serial.print("📶 WiFi attempt " + String(wifiRetryCount) + "...");
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
}

void cekWiFi() {
  if (millis() - lastWiFiCheck < INTERVAL_WIFI) return;
  lastWiFiCheck = millis();
  if (WiFi.status() == WL_CONNECTED) {
    wifiReady = true;
    wifiConnecting = false;
    wifiRetryCount = 0;
    return;
  }
  if (!wifiConnecting) {
    connectWiFi();
    return;
  }
  if (millis() - wifiStartTime > 15000) {
    wifiConnecting = false;
    wifiReady = false;
    Serial.println(" ❌ Timeout");
  }
}

// =======================
// KONEKSI MQTT (Server)
// =======================
void connectMQTT() {
  if (mqtt.connected()) { mqttReady = true; return; }
  if (!wifiReady) return;
  Serial.print("🔌 Server...");
  espClient.setCACert(root_ca);
  String clientId = "ESP_" + String(DEVICE_ID) + "_" + String(random(9999));
  mqttReady = mqtt.connect(clientId.c_str(), MQTT_USER, MQTT_PASS);
  if (mqttReady) {
    mqtt.subscribe(topicCmd);
    Serial.println(" ✅");
    sendStatus("online");
    buzzerShort();
  } else {
    Serial.print(" ❌ rc=");
    Serial.println(mqtt.state());
  }
}

void cekMQTT() {
  if (millis() - lastMQTTCheck < INTERVAL_MQTT) return;
  lastMQTTCheck = millis();
  if (!mqtt.connected() && wifiReady) {
    connectMQTT();
  }
}

// =======================
// KIRIM DATA KE SERVER
// =======================
void sendStatus(const char* s) {
  if (!mqttReady) return;
  StaticJsonDocument<64> doc;
  doc["device_id"] = DEVICE_ID;
  doc["status"] = s;
  doc["battery_voltage"] = batteryVoltage;
  doc["battery_percent"] = batteryPercent;
  String json; serializeJson(doc, json);
  mqtt.publish(topicStatus, json.c_str());
}

void sendGPS() {
  if (!mqttReady || !gpsLock) return;
  StaticJsonDocument<128> doc;
  doc["device_id"] = DEVICE_ID;
  doc["latitude"] = lat;
  doc["longitude"] = lng;
  doc["speed"] = speed;
  doc["battery_voltage"] = batteryVoltage;
  doc["battery_percent"] = batteryPercent;
  String json; serializeJson(doc, json);
  mqtt.publish(topicLoc, json.c_str());
}

void sendResponse(const char* r) {
  if (!mqttReady) return;
  StaticJsonDocument<64> doc;
  doc["response"] = r;
  doc["device_id"] = DEVICE_ID;
  String json; serializeJson(doc, json);
  char topic[60]; snprintf(topic, sizeof(topic), "gps/response/%s/%s", DEVICE_ID, r);
  mqtt.publish(topic, json.c_str());
}

// =======================
// UPDATE GPS
// =======================
void updateGPS() {
  while (gpsSerial.available()) {
    gps.encode(gpsSerial.read());
  }
  if (gps.location.isValid()) {
    lat = gps.location.lat();
    lng = gps.location.lng();
    speed = gps.speed.kmph();
    if (!gpsLock) {
      gpsLock = true;
      Serial.println("🛰️ GPS LOCKED!");
      bipBip();
    }
  } else {
    gpsLock = false;
  }
  sat = gps.satellites.isValid() ? gps.satellites.value() : 0;
  batteryVoltage = readBatteryVoltage();
  batteryPercent = voltageToPercent(batteryVoltage);
}

// =======================
// OLED DISPLAY - MENU UTAMA (Tanpa BAT)
// =======================
void displayMainMenu() {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  
  // Title
  display.setTextSize(1);
  display.setCursor(0, 0);
  display.println("My TrackTime");
  display.drawLine(0, 10, 128, 10, SSD1306_WHITE);
  
  // Menu items
  display.setTextSize(1);
  display.setCursor(5, 18);
  if (menuIndex == 0) display.print(">");
  else display.print(" ");
  display.print(" Mulai");
  
  display.setCursor(5, 30);
  if (menuIndex == 1) display.print(">");
  else display.print(" ");
  display.print(" Tentang");
  
  // Status bar (tanpa BAT)
  display.drawLine(0, 44, 128, 44, SSD1306_WHITE);
  display.setCursor(2, 48);
  display.print("S:");
  display.print(mqttReady ? "ON" : "OFF");
  display.print(" W:");
  display.print(wifiReady ? "ON" : "OFF");
  display.print(" G:");
  display.print(gpsLock ? "ON" : "OFF");
  
  // Versi
  display.setCursor(2, 58);
  display.print("v2.0 | Tekan pilih / Tahan eksekusi");
  
  display.display();
}

// =======================
// OLED DISPLAY - START
// =======================
void displayStart() {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(2);
  display.setCursor(30, 8);
  display.println("Mulai");
  display.setTextSize(1);
  display.setCursor(10, 35);
  display.print("GPS: ");
  display.print(gpsLock ? "LOCKED" : "WAIT...");
  display.setCursor(10, 48);
  display.print("SAT: ");
  display.print(sat);
  display.display();
}

// =======================
// OLED DISPLAY - RUNNING
// =======================
void displayRunning() {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  
  // Header
  display.setTextSize(1);
  display.setCursor(0, 0);
  display.print("Tracking");
  display.setCursor(80, 0);
  display.print("SAT:");
  display.print(sat);
  display.drawLine(0, 10, 128, 10, SSD1306_WHITE);
  
  // Data utama
  display.setTextSize(1);
  display.setCursor(0, 16);
  display.print("LAT");
  display.setCursor(0, 30);
  display.print("LON");
  display.setCursor(0, 44);
  display.print("SPD");
  
  // Nilai
  display.setTextSize(1);
  display.setCursor(35, 16);
  if (gpsLock) {
    display.print(lat, 5);
  } else {
    display.print("--.-----");
  }
  display.setCursor(35, 30);
  if (gpsLock) {
    display.print(lng, 5);
  } else {
    display.print("--.-----");
  }
  display.setCursor(35, 44);
  if (gpsLock) {
    display.print(speed, 1);
    display.print(" km/h");
  } else {
    display.print("--.- km/h");
  }
  
  // STOP button
  display.setCursor(100, 56);
  display.print("[STOP]");
  display.display();
}

// =======================
// OLED DISPLAY - RESULT
// =======================
void displayResult() {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);
  
  display.setCursor(0, 0);
  display.println("--- HASIL ---");
  display.drawLine(0, 10, 128, 10, SSD1306_WHITE);
  
  display.setCursor(0, 16);
  display.print("Durasi: ");
  display.println(formatDuration(runDuration));
  
  display.setCursor(0, 28);
  display.print("Max SPD: ");
  display.print(maxSpeed, 1);
  display.println(" km/h");
  
  display.setCursor(0, 40);
  display.print("Avg SPD: ");
  display.print(avgSpeed, 1);
  display.println(" km/h");
  
  display.setCursor(0, 52);
  display.print("Data: ");
  display.print(dataCount);
  display.print(" pt");
  
  display.display();
}

// =======================
// OLED DISPLAY - ABOUT
// =======================
void displayAbout() {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);
  
  display.setCursor(0, 0);
  display.println("--- Tentang ---");
  display.drawLine(0, 10, 128, 10, SSD1306_WHITE);
  
  display.setCursor(0, 18);
  display.println("My TrackTime v2.0");
  display.setCursor(0, 30);
  display.println("GPS Tracker");
  display.setCursor(0, 42);
  display.print("Device: ");
  display.println(DEVICE_ID);
  display.setCursor(0, 54);
  display.println("Tekan untuk kembali");
  
  display.display();
}

// =======================
// FORMAT DURASI
// =======================
String formatDuration(unsigned long ms) {
  unsigned long totalSeconds = ms / 1000;
  unsigned int hours = totalSeconds / 3600;
  unsigned int minutes = (totalSeconds % 3600) / 60;
  unsigned int seconds = totalSeconds % 60;
  char buffer[12];
  sprintf(buffer, "%02u:%02u:%02u", hours, minutes, seconds);
  return String(buffer);
}

// =======================
// HANDLE BUTTON (Tekan = pindah, Tahan = eksekusi)
// =======================
void checkButton() {
  if (millis() - lastButtonCheck < 50) return;
  lastButtonCheck = millis();
  
  if (digitalRead(BUTTON_PIN) == LOW) {
    if (!buttonPressed) {
      buttonPressed = true;
      buttonPressTime = millis();
      buttonProcessed = false;
    }
  } else {
    if (buttonPressed) {
      buttonPressed = false;
      
      // Cek durasi tekan
      unsigned long pressDuration = millis() - buttonPressTime;
      
      if (!buttonProcessed) {
        if (pressDuration > 1500) {
          // Tahan > 1.5 detik = EKSEKUSI
          handleLongPress();
        } else {
          // Tekan sebentar = PINDAH PILIHAN
          handleShortPress();
        }
        buttonProcessed = true;
      }
    }
  }
}

// =======================
// HANDLE SHORT PRESS (Pindah menu)
// =======================
void handleShortPress() {
  buzzerShort();
  
  switch (currentState) {
    case MENU_MAIN:
      // Pindah menu
      menuIndex++;
      if (menuIndex >= menuCount) menuIndex = 0;
      displayMainMenu();
      break;
      
    case MENU_ABOUT:
      currentState = MENU_MAIN;
      menuIndex = 0;
      displayMainMenu();
      break;
      
    case MENU_RESULT:
      currentState = MENU_MAIN;
      menuIndex = 0;
      displayMainMenu();
      break;
      
    case MENU_RUNNING:
    case MENU_START:
      // Tidak ada aksi di state ini
      break;
  }
}

// =======================
// HANDLE LONG PRESS (Eksekusi)
// =======================
void handleLongPress() {
  buzzerLong();
  
  switch (currentState) {
    case MENU_MAIN:
      if (menuIndex == 0) {
        // Pilih "Mulai"
        currentState = MENU_START;
        displayStart();
        delay(500);
        currentState = MENU_RUNNING;
        startTime = millis();
        maxSpeed = 0;
        dataCount = 0;
        totalDistance = 0;
        avgSpeed = 0;
        buzzerShort();
        sendStatus("tracking_start");
      } else {
        // Pilih "Tentang"
        currentState = MENU_ABOUT;
        displayAbout();
      }
      break;
      
    case MENU_RUNNING:
      // Stop tracking
      currentState = MENU_RESULT;
      runDuration = millis() - startTime;
      if (dataCount > 0) {
        avgSpeed = totalDistance / dataCount;
      }
      displayResult();
      sendStatus("tracking_stop");
      break;
      
    case MENU_ABOUT:
      currentState = MENU_MAIN;
      menuIndex = 0;
      displayMainMenu();
      break;
      
    case MENU_RESULT:
      currentState = MENU_MAIN;
      menuIndex = 0;
      displayMainMenu();
      break;
      
    case MENU_START:
      break;
  }
}

// =======================
// EKSEKUSI COMMAND DARI SERVER
// =======================
void executeCommand(String cmd) {
  lastCommand = cmd;
  Serial.println("⚡ " + cmd);
  if (cmd == "buzzer_on") {
    digitalWrite(BUZZER_PIN, HIGH);
    sendResponse("buzzer_on");
  } else if (cmd == "buzzer_off") {
    digitalWrite(BUZZER_PIN, LOW);
    sendResponse("buzzer_off");
  } else if (cmd == "buzzer_beep") {
    for (int i = 0; i < 3; i++) {
      buzzerBeep(150);
      delay(150);
    }
    sendResponse("buzzer_beep");
  } else if (cmd == "led_on") {
    digitalWrite(LED_PIN, HIGH);
    sendResponse("led_on");
  } else if (cmd == "led_off") {
    digitalWrite(LED_PIN, LOW);
    sendResponse("led_off");
  } else if (cmd == "ping") {
    sendResponse("pong");
  } else if (cmd == "restart") {
    sendResponse("restart");
    delay(500);
    ESP.restart();
  } else if (cmd == "get_location") {
    if (gpsLock) sendGPS();
  }
}

// =======================
// MQTT CALLBACK
// =======================
void mqttCallback(char* topic, byte* payload, unsigned int len) {
  String msg;
  for (int i = 0; i < len; i++) msg += (char)payload[i];
  Serial.println("📩 CMD: " + msg);
  StaticJsonDocument<128> doc;
  if (deserializeJson(doc, msg) == DeserializationError::Ok) {
    const char* cmd = doc["command"];
    if (cmd) executeCommand(String(cmd));
  }
}

// =======================
// DEBUG SERIAL
// =======================
void debugSerial() {
  if (millis() - lastDebug < INTERVAL_DEBUG) return;
  lastDebug = millis();
  Serial.print("📡 ");
  Serial.print(wifiReady ? "W✓" : "W✗");
  Serial.print(" S:"); Serial.print(mqttReady ? "✓" : "✗");
  Serial.print(" G:"); Serial.print(gpsLock ? "✓" : "✗");
  Serial.print(" SAT:"); Serial.print(sat);
  Serial.print(" BAT:");
  Serial.print(batteryVoltage, 2);
  Serial.print("V (");
  Serial.print(batteryPercent);
  Serial.println("%)");
}

// =======================
// SETUP
// =======================
void setup() {
  Serial.begin(115200);
  delay(2000);

  Serial.println("\n=== My TrackTime ===");
  Serial.println("Device: " + String(DEVICE_ID));

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BATTERY_PIN, INPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_PIN, LOW);

  bipBip();

  Wire.begin(OLED_SDA, OLED_SCL);
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("OLED FAIL");
  }
  display.clearDisplay();
  display.setTextSize(1);
  display.setCursor(30, 20);
  display.println("My");
  display.setCursor(30, 32);
  display.println("TrackTime");
  display.display();
  delay(2000);

  gpsSerial.begin(GPS_BAUD, SERIAL_8N1, GPS_RX, GPS_TX);

  setupTopics();
  mqtt.setServer(MQTT_HOST, MQTT_PORT);
  mqtt.setCallback(mqttCallback);

  connectWiFi();
  connectMQTT();

  currentState = MENU_MAIN;
  menuIndex = 0;
  displayMainMenu();

  Serial.println("SYSTEM READY!");
}

// =======================
// LOOP
// =======================
void loop() {
  mqtt.loop();
  cekWiFi();
  cekMQTT();
  updateGPS();
  checkButton();

  // Update data jika running
  if (currentState == MENU_RUNNING && gpsLock) {
    dataCount++;
    totalDistance += speed;
    if (speed > maxSpeed) maxSpeed = speed;
  }

  // Kirim GPS ke server
  if (millis() - lastPublish > INTERVAL_PUBLISH) {
    lastPublish = millis();
    if (gpsLock && mqttReady) sendGPS();
    if (mqttReady) sendStatus(gpsLock ? "online" : "searching");
  }

  // Update display
  if (millis() - lastDisplay > INTERVAL_DISPLAY) {
    lastDisplay = millis();
    switch (currentState) {
      case MENU_MAIN: displayMainMenu(); break;
      case MENU_START: displayStart(); break;
      case MENU_RUNNING: displayRunning(); break;
      case MENU_RESULT: displayResult(); break;
      case MENU_ABOUT: displayAbout(); break;
    }
  }

  debugSerial();
  delay(10);
}