<?php
header('Content-Type: application/json'); // Indicamos que devolverá JSON

$servername = "localhost";
$username = "root";          // tu usuario MySQL
$password = "";              // tu contraseña MySQL
$dbname = "sensor_placas";   // tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Consulta SQL usando tu tabla correcta
$sql = "SELECT fecha_hora FROM lecturas ORDER BY id DESC"; 
$result = $conn->query($sql);

$datos = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }
}

echo json_encode($datos);
$conn->close();
?>
