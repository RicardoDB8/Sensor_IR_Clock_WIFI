#include <WiFi.h>
#include <HTTPClient.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <time.h>  // Para convertir epoch a fecha y hora

// ---------- Configuración de WiFi ----------
const char* ssid = "Zhone_FC4C";
const char* password = "znid311140172";

// ---------- Configuración del sensor ----------
const int sensorPin = 15;
int sensorState = 0;
int lastSensorState = HIGH;

// ---------- Configuración NTP ----------
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 0, 60000); // UTC, actualizar cada 60s

// ---------- IP del servidor y puerto ----------
const char* serverIP = "192.168.1.22";
const int serverPort = 8080;

// ---------- Variables de modelo y contador ----------
String modelo = "";
int eventoCounter = 1; // Contador de eventos

// ---------- Función para codificar URL ----------
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

// ---------- Setup ----------
void setup() {
  Serial.begin(115200);
  pinMode(sensorPin, INPUT_PULLUP);

  // Conectar a WiFi
  WiFi.begin(ssid, password);
  Serial.print("Conectando a WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ Conectado!");
  Serial.print("IP del ESP32: ");
  Serial.println(WiFi.localIP());

  // Iniciar cliente NTP
  timeClient.begin();
  timeClient.update();
  delay(2000); // espera sincronización

  // Pedir modelo solo al inicio
  Serial.println("❓ Ingresa el modelo:");
  while (modelo == "") {
    if (Serial.available()) {
      modelo = Serial.readStringUntil('\n');
      modelo.trim();
    }
    delay(50);
  }
  Serial.println("Modelo guardado: " + modelo);
}

// ---------- Loop ----------
void loop() {
  // --- Comandos desde monitor serie ---
  if (Serial.available()) {
    String comando = Serial.readStringUntil('\n');
    comando.trim();

if (comando.startsWith("MODELO ")) {
  modelo = comando.substring(7); // toma lo que viene después de "MODELO "
  modelo.trim();
  eventoCounter = 1; // reiniciar contador al cambiar modelo
  Serial.println("✅ Modelo actualizado a: " + modelo + " y contador reiniciado a 1");
}

    if (comando == "RESET") {
      eventoCounter = 1;
      Serial.println("✅ Contador reiniciado a 1");
    }
  }

  // --- Lectura del sensor ---
  timeClient.update();
  sensorState = digitalRead(sensorPin);

  if (sensorState == LOW && lastSensorState == HIGH) {
    // Obtener fecha y hora
    unsigned long epochTime = timeClient.getEpochTime();
    if(epochTime == 0){
      Serial.println("❌ Error: NTP no sincronizado");
      return;
    }

    struct tm *ptm = gmtime((time_t *)&epochTime);
    ptm->tm_hour = (ptm->tm_hour + 24 - 3) % 24; // UTC-3
    mktime(ptm);

    char fechaHora[20];
    sprintf(fechaHora, "%04d-%02d-%02d %02d:%02d:%02d",
            ptm->tm_year + 1900,
            ptm->tm_mon + 1,
            ptm->tm_mday,
            ptm->tm_hour,
            ptm->tm_min,
            ptm->tm_sec);

    // Evento como número incremental
    String evento = String(eventoCounter);
    eventoCounter++; // incrementa para la próxima lectura

    int sensor_id = 1;
    String ubicacion = "Entrada principal";

    // Construir URL
    String url = "http://" + String(serverIP) + ":" + String(serverPort) + "/sensor_placas/insertar_lectura.php";
    url += "?evento=" + urlEncode(evento);
    url += "&fecha_hora=" + urlEncode(String(fechaHora));
    url += "&sensor_id=" + String(sensor_id);
    url += "&ubicacion=" + urlEncode(ubicacion);
    url += "&modelo=" + urlEncode(modelo);

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
// MODELO "Nombre del nuevo modelo"





