<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "sensor_placas";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);
if ($conn->connect_error) { die("❌ Error de conexión: " . $conn->connect_error); }

// Recibir datos del ESP32 (sin modelo)
$evento     = isset($_GET['evento']) ? $_GET['evento'] : '';
$fecha_hora = isset($_GET['fecha_hora']) ? $_GET['fecha_hora'] : '';
$sensor_id  = isset($_GET['sensor_id']) ? intval($_GET['sensor_id']) : 0;
$ubicacion  = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : '';

// Validar parámetros obligatorios
if ($evento && $fecha_hora && $sensor_id && $ubicacion) {

    // Obtener el modelo actual desde la tabla configuracion
    $resultado = $conn->query("SELECT modelo FROM configuracion WHERE id=1");
    $modelo = "";
    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $modelo = $fila['modelo'];
    } else {
        echo "⚠️ No se encontró un modelo configurado.";
        exit;
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO lecturas (evento, fecha_hora, sensor_id, ubicacion, modelo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $evento, $fecha_hora, $sensor_id, $ubicacion, $modelo);

    if ($stmt->execute()) {
        echo "✅ Lectura registrada correctamente con modelo: $modelo";
    } else {
        echo "❌ Error SQL: " . $stmt->error;
    }

    $stmt->close();

} else {
    echo "⚠️ Faltan parámetros en la solicitud.";
}

$conn->close();
?>
