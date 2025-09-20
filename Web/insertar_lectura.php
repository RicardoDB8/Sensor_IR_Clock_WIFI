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
