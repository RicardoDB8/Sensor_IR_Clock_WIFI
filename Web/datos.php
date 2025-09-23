<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "sensor_placas";

// Conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

// Consulta (incluye el campo modelo)
$sql = "SELECT id, evento, fecha_hora, sensor_id, ubicacion, modelo 
        FROM lecturas 
        ORDER BY id DESC 
        LIMIT 50";

$result = $conn->query($sql);

$datos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }
}

// Respuesta en JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($datos, JSON_UNESCAPED_UNICODE);

$conn->close();
?>
