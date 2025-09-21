# Sistema de Conteo y Monitoreo de Productos

## Objetivo del proyecto: e.g., “Registrar detecciones de sensor IR y mostrar en una pagina web con actualización automática.”

## Requisitos

Para ejecutar este proyecto, se necesita:

- **NodeMCU ESP32**: Es una tarjeta de desarrollo o placa que integra el microcontrolador ESP32 con conectividad WiFi y Bluetooth.
- **Sensor IR HW-006 (IR sensor)** : El módulo sensor de seguimiento de línea HW-006 (V1.3) se basa en el sensor de reflectancia infrarroja  
- **Modulo Reloj HW-111** : El HW-111 es el nombre común de un módulo de reloj de tiempo real (RTC) que utiliza el chip DS3231, un componente de bajo coste y alta precisión que mantiene un registro de la fecha y hora actuale
- **XAMPP** : Es un paquete de software libre que integra un servidor web Apache, una base de datos MariaDB (o MySQL) y los intérpretes para los lenguajes de programación PHP y Perl.
- librerías
- NTPClient
- **WiFi**: Conexión WIFI
- **HTTPClient**: Un HttpClient es una clase de programación, disponible en lenguajes como C# (en el namespace System.Net.Http) y JavaScript (en Angular, bajo @angular/common/http), que permite a una aplicación enviar solicitudes HTTP a un servidor y recibir las respuestas correspondientes.

## Conexiones

| Dispositivo           | Pin del Dispositivo | Pin del ESP32 |
|-----------------------|------------------|---------------|
| **HW-006 IR Sensor**  | VCC               | 3.3V          |
|                       | GND               | GND           |
|                       | OUT               | GPIO 15       |
| **HW-111 RTC DS3231** | VCC               | 3.3V          |
|                       | GND               | GND           |
|                       | SDA               | GPIO 21       |
|                       | SCL               | GPIO 22       |

## Instrucciones de Armado

Sigue estos pasos para ensamblar el sistema correctamente:

1. **Preparar el hardware**
   - Coloca el **NodeMCU ESP32** sobre la superficie de trabajo.
   - Ten listos el **HW-006 IR Sensor** y el **HW-111 RTC DS3231**.
   - Asegúrate de tener cables de conexión adecuados.

2. **Conectar el HW-006 IR Sensor al ESP32**
   - VCC → 3.3V del ESP32
   - GND → GND del ESP32
   - OUT → GPIO 15 del ESP32 (puede cambiarse según tu configuración)

3. **Conectar el HW-111 RTC DS3231 al ESP32**
   - VCC → 3.3V del ESP32
   - GND → GND del ESP32
   - SDA → GPIO 21 del ESP32
   - SCL → GPIO 22 del ESP32

4. **Verificar conexiones**
   - Revisa que todos los cables estén firmes y en los pines correctos.
   - Asegúrate de no invertir polaridad (VCC y GND).

5. **Encender el sistema**
   - Conecta el ESP32 a tu computadora mediante cable USB.
   - Observa si los sensores reciben alimentación correctamente.
   - Listo, el sistema está armado y listo para cargar el código.

## Configuración del IDE de Arduino y carga del código

Sigue estos pasos para preparar el IDE y cargar el programa en el ESP32:

1. **Abrir el IDE de Arduino**
   - Asegúrate de tener instalada la versión más reciente del IDE.
   - Abre el programa en tu computadora.

2. **Instalar los drivers del ESP32 (si es necesario)**
   - Conecta el ESP32 mediante un cable USB.
   - Si tu sistema no reconoce el dispositivo, instala el driver correspondiente:
     - [Drivers CH340 para Windows](https://sparks.gogo.co.nz/ch340.html) (común en NodeMCU)
     - macOS y Linux normalmente no requieren drivers adicionales.

3. **Seleccionar la placa correcta**
   - Ve a `Herramientas > Placa > ESP32 Arduino`.
   - Selecciona la opción correspondiente, por ejemplo: `NodeMCU-32S` o `ESP32 Dev Module`.

4. **Seleccionar el puerto**
   - Ve a `Herramientas > Puerto` y selecciona el puerto COM que corresponda al ESP32.

5. **Cargar el código**

```cpp
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
```

6.**Compilar y cargar el código**

- Haz clic en el botón **Verificar** (icono de ✔️) para compilar.
- Luego haz clic en **Subir** (icono de ➡️) para cargar el código en el ESP32.
- Espera a que aparezca el mensaje `Subido correctamente` en la consola del IDE.

7.**Verificar funcionamiento**
-Abre el **Monitor Serie** (`Herramientas > Monitor Serie`) para observar la salida del sistema.
-Confirma que el sensor IR y el RTC respondan según lo esperado.

## Prueba del Sistema

1. Conecta el ESP32 al computador mediante USB.
2. Abre el Monitor Serie en el IDE (`Herramientas > Monitor Serie`).
3. Observa los valores que envía el sensor IR.
4. Verifica que la hora del RTC DS3231 se muestre correctamente.

## Base de Datos MySQL

El proyecto utiliza una base de datos llamada `sensor_placas` con una tabla `lecturas` para registrar los eventos de los sensores.

### 1.Seleccionar la base de datos

```sql
## Base de Datos MySQL con Comentarios

-- Selecciona la base de datos que vamos a usar
USE sensor_placas;

-- Mostrar todos los registros de la tabla 'lecturas'
SELECT * FROM lecturas;

-- Mostrar los registros más recientes primero
SELECT * FROM lecturas ORDER BY id DESC;

-- Crear la tabla 'lecturas' si no existe
CREATE TABLE IF NOT EXISTS lecturas (
    id INT AUTO_INCREMENT PRIMARY KEY,   -- Identificador único de cada registro
    evento VARCHAR(255),                 -- Descripción del evento detectado
    fecha_hora DATETIME,                 -- Fecha y hora del evento
    sensor_id INT,                        -- ID del sensor que detectó el evento
    ubicacion VARCHAR(255)               -- Ubicación del sensor o del evento
);
```

## Inserción de Lecturas en MySQL (`insertar_lectura.php`)

```php
<?php
// Configuración de conexión a MySQL
$host = "localhost";
$usuario = "root";
$contrasena = ""; // Cambiar si tu MySQL tiene contraseña
$base_datos = "sensor_placas";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

// Obtener datos del ESP32 (por GET)
$evento = isset($_GET['evento']) ? $_GET['evento'] : '';
$fecha_hora = isset($_GET['fecha_hora']) ? $_GET['fecha_hora'] : '';
$sensor_id = isset($_GET['sensor_id']) ? intval($_GET['sensor_id']) : 0;
$ubicacion = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : '';

// Validar que no estén vacíos
if ($evento && $fecha_hora && $sensor_id && $ubicacion) {
    // Insertar en la base de datos
    $sql = "INSERT INTO lecturas (evento, fecha_hora, sensor_id, ubicacion)
            VALUES ('$evento', '$fecha_hora', $sensor_id, '$ubicacion')";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Lectura registrada correctamente.";
    } else {
        echo "❌ Error SQL: " . $conn->error;
    }
} else {
    echo "⚠️ Faltan parámetros en la solicitud.";
}

$conn->close();
?>
```
## Visualización de Lecturas en Tiempo Real

Esta sección muestra cómo usar un **archivo HTML (`index.html`)** junto con un **script PHP (`datos.php`)** para mostrar las lecturas del sensor IR en tiempo real.

### Código del `index.html`

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lecturas del Sensor IR</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        ul { list-style-type: none; padding: 0; }
        li { padding: 5px 0; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Lecturas del Sensor IR</h1>
    <ul id="listaDatos"></ul>

    <script>
        async function cargarDatos() {
            try {
                const response = await fetch('datos.php');
                const data = await response.json();

                const lista = document.getElementById('listaDatos');
                lista.innerHTML = ''; // limpiar lista antes de mostrar

                data.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item.fecha_hora;
                    lista.appendChild(li);
                });
            } catch (error) {
                console.error('Error al cargar los datos:', error);
            }
        }

        // Cargar datos inicialmente
        cargarDatos();

        // Actualizar cada 0.1 segundos
        setInterval(cargarDatos, 100);
    </script>
</body>
</html>
```

### Código del `datos.php`

```php
<?php
header('Content-Type: application/json'); // Devolver JSON

$servername = "localhost";
$username = "root";          // Usuario MySQL
$password = "";              // Contraseña MySQL
$dbname = "sensor_placas";   // Base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Consulta SQL para obtener las lecturas más recientes
$sql = "SELECT fecha_hora FROM lecturas ORDER BY id DESC"; 
$result = $conn->query($sql);

$datos = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Devolver los datos en formato JSON
echo json_encode($datos);

$conn->close();
?>
```

## Cómo usar `index.html` y `datos.php` con XAMPP

Sigue estos pasos para visualizar las lecturas del sensor IR en tiempo real desde tu navegador usando XAMPP:

---

### 1.Instalar y abrir XAMPP

1.Descarga XAMPP desde [https://www.apachefriends.org](https://www.apachefriends.org).  
2. Instala XAMPP en tu computadora.  
3. Abre el **Panel de Control de XAMPP**.

---

### 2.Iniciar los servicios necesarios

1.En el panel de control, inicia **Apache** (servidor web) y **MySQL**.  
2. Asegúrate de que ambos servicios estén corriendo (deberían verse en verde).

---

### 3.Colocar los archivos en la carpeta correcta

1.Ubica la carpeta `htdocs` de XAMPP.  

- Por ejemplo, en Windows: `C:\xampp\htdocs\`.  
2.Crea una subcarpeta para tu proyecto, por ejemplo: `sensor_ir`.

3.Copia los archivos `index.html` y `datos.php` dentro de esa carpeta:
C:\xampp\htdocs\sensor_ir\index.html
C:\xampp\htdocs\sensor_ir\datos.php


---

### 4. Configurar la conexión a la base de datos en `datos.php`
1. Abre `datos.php` en tu editor de código.  
2. Verifica las siguientes variables:
```php
$servername = "localhost";   // normalmente localhost
$username = "root";           // usuario por defecto de XAMPP
$password = "";               // contraseña por defecto (vacía)
$dbname = "sensor_placas";    // base de datos que creaste





Direcciones importantes para la ejecucion
http://192.168.1.22:8080/sensor_placas/index.html


