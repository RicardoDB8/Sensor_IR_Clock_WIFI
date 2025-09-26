<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "sensor_placas";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);
if ($conn->connect_error) { die("❌ Error: " . $conn->connect_error); }

$sql = "SELECT fecha_hora, evento, sensor_id, ubicacion, modelo FROM lecturas ORDER BY id DESC";
$result = $conn->query($sql);

$datos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($datos);
?>