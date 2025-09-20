#include <WiFi.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <HTTPClient.h>
#include <time.h>  // Para convertir epoch a fecha y hora

// Configuración de WiFi
const char* ssid = "Zhone_FC4C";
const char* password = "znid311140172";

// Configuración del sensor
int sensorPin = 15;
int sensorState = 0;
int lastSensorState = HIGH;

// Configuración NTP
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 0, 60000); // UTC, luego ajustamos -3 horas manual

// IP del servidor (tu PC con XAMPP) y puerto
const char* serverIP = "192.168.1.22";
const int serverPort = 8080; // Puerto de Apache

// Función para codificar parámetros en URL
String urlEncode(const String& str) {
  String encoded = "";
  char c;
  char code[4];
  for (int i = 0; i < str.length(); i++) {
    c = str.charAt(i);
    if (isalnum(c)) {
      encoded += c;
    } else {
      sprintf(code, "%%%02X", c);
      encoded += code;
    }
  }
  return encoded;
}

void setup() {
  Serial.begin(115200);
  pinMode(sensorPin, INPUT_PULLUP);

  WiFi.begin(ssid, password);
  Serial.print("Conectando a WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ Conectado!");
  Serial.print("IP del ESP32: ");
  Serial.println(WiFi.localIP());

  timeClient.begin();
}

void loop() {
  timeClient.update();
  sensorState = digitalRead(sensorPin);

  // Detectar borde descendente (sensor presionado)
  if (sensorState == LOW && lastSensorState == HIGH) {
    // Obtener fecha y hora dinámica desde NTP
    unsigned long epochTime = timeClient.getEpochTime();
    struct tm *ptm = gmtime((time_t *)&epochTime);

    // Ajuste UTC-3
    ptm->tm_hour -= 3;
    mktime(ptm); // Normaliza fecha y hora si se cruza día/mes/año

    char fechaHora[20];
    sprintf(fechaHora, "%04d-%02d-%02d %02d:%02d:%02d",
            ptm->tm_year + 1900,
            ptm->tm_mon + 1,
            ptm->tm_mday,
            ptm->tm_hour,
            ptm->tm_min,
            ptm->tm_sec);

    String evento = "Obstaculo detectado";
    int sensor_id = 1;
    String ubicacion = "Entrada principal";

    // Construir URL codificada con puerto
    String url = "http://" + String(serverIP) + ":" + String(serverPort) + "/sensor_placas/insertar_lectura.php";
    url += "?evento=" + urlEncode(evento);
    url += "&fecha_hora=" + urlEncode(String(fechaHora));
    url += "&sensor_id=" + String(sensor_id);
    url += "&ubicacion=" + urlEncode(ubicacion);

    Serial.println("📡 Enviando a: " + url);

    // Enviar solicitud HTTP
    if(WiFi.status() == WL_CONNECTED){
      HTTPClient http;
      http.begin(url);
      int httpCode = http.GET();
      if (httpCode > 0) {
        String respuesta = http.getString();
        Serial.println("✅ Servidor respondió: " + respuesta);
      } else {
        Serial.println("❌ Error HTTP: " + String(httpCode));
      }
      http.end();
    } else {
      Serial.println("❌ Error: ESP32 no conectado a WiFi");
    }
  }

  lastSensorState = sensorState;
  delay(200);
}





