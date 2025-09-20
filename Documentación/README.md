Objetivo del proyecto: e.g., “Registrar detecciones de sensor IR y mostrar en web con actualización automática.”


Requisitos: ESP32, sensor IR, XAMPP, librerías NTPClient, WiFi, HTTPClient.

Conexiones: 
Conexión de pines 
🔹 1. Conexión del HW-006 (IR sensor)

Este módulo tiene 3 pines:

OUT → Señal digital (al ESP32)

VCC → Alimentación (3.3 V o 5 V, depende de la versión; en ESP32 mejor usar 3.3 V)

GND → Tierra

👉 Conexiones:

OUT → GPIO 15 (puede ser otro pin digital, configurable en el código)

VCC → 3.3V del ESP32

GND → GND del ESP32

🔹 2. Conexión del HW-111 (RTC DS3231)

Este módulo usa I2C, solo necesita 4 pines:

SDA → Datos I2C

SCL → Reloj I2C

VCC → Alimentación (funciona con 3.3 V o 5 V, en ESP32 mejor usar 3.3 V)

GND → Tierra

👉 En el ESP32, los pines por defecto de I2C son:

SDA → GPIO 21

SCL → GPIO 22

👉 Conexiones:

SDA → GPIO 21 del ESP32

SCL → GPIO 22 del ESP32

VCC → 3.3V del ESP32

GND → GND del ESP32

🔹 3. Resumen del cableado

HW-006 IR sensor

VCC → 3.3V

GND → GND

OUT → GPIO 15

HW-111 RTC DS3231

VCC → 3.3V

GND → GND

SDA → GPIO 21

SCL → GPIO 22

Instrucciones:

1 - Abrir Arduino IDE y cargar el  codigo en "Sensor_IR_Clock_WIFI.ino"
2 - Crear una base de datos
Estructura de la base de datos:

USE sensor_placas;

SELECT * FROM lecturas;

CREATE TABLE IF NOT EXISTS lecturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento VARCHAR(255),
    fecha_hora DATETIME,
    sensor_id INT,
    ubicacion VARCHAR(255)
);

SELECT * FROM lecturas ORDER BY id DESC;

3 - Crear servidor que conecte la base de datos con el codigo en "insertar_lectura.php"
4 - Verificar las lecturas con el comando en MYSQL
SELECT * FROM lecturas ORDER BY id DESC;
5 - Crear un html que pueda leer la base de datos "Index.html"
6 - Crear un php que que lea la base de datos desde el html "datos.php"


Direcciones importantes para la ejecucion
http://192.168.1.22:8080/sensor_placas/index.html


