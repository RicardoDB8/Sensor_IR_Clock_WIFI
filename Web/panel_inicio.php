<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "sensor_placas";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);
if ($conn->connect_error) { die("❌ Error de conexión: " . $conn->connect_error); }

// Variables de estado
$producto = "";
$actualizado = false;

// Leer modelo actual
$resultado = $conn->query("SELECT modelo FROM configuracion WHERE id=1");
$modelo = "";
if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $modelo = $fila['modelo'];
    $producto = $modelo; // Para mostrar como producto
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['modelo'])) {
        $nuevoModelo = $_POST['modelo'];
        $stmt = $conn->prepare("UPDATE configuracion SET modelo=? WHERE id=1");
        $stmt->bind_param("s", $nuevoModelo);
        $stmt->execute();
        $stmt->close();
        $producto = $nuevoModelo;
        $actualizado = true;
    }

    if (isset($_POST['parar'])) {
        $actualizado = false;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurar Modelo</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        header { display: flex; justify-content: space-between; align-items: center; background-color: #f4f4f4; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .nombre { font-size: 1.5em; font-weight: bold; color: #333; }
        .perfil img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #333; }
        h1 { margin: 20px; color: #333; }
        form { margin: 20px 0; }
        input { padding: 5px; font-size: 1em; }
        button { padding: 5px 10px; font-size: 1em; }
    </style>
</head>
<body>
    <header>
        <div class="nombre">Cariluma</div>
        <div class="perfil">
            <img src="./assets/Mi_Foto.jpg" alt="Foto de perfil">
        </div>
    </header>

    <h1>Modelo actual: <?php echo $modelo; ?></h1>

    <?php if ($actualizado): ?>
        <p>El producto es: <strong><?php echo $producto; ?></strong></p>
        <form method="POST">
            <button type="submit" name="parar">Parar</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <label>Nuevo modelo:</label>
            <input type="text" name="modelo" required>
            <button type="submit">Actualizar</button>
        </form>
    <?php endif; ?>
</body>
</html>