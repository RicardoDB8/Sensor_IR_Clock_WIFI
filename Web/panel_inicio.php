<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "sensor_placas";

// Conectar a la base de datos
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

// Variable para controlar el estado
$producto = "";
$actualizado = false;

// Si se envió un nuevo modelo desde el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['modelo'])) {
        // Guardar el nuevo modelo en la base
        $nuevoModelo = $_POST['modelo'];
        $sql = "UPDATE configuracion SET modelo='$nuevoModelo' WHERE id=1";
        if ($conn->query($sql) === TRUE) {
            $producto = $nuevoModelo;
            $actualizado = true; // Entramos al estado "producto elegido"
        }
    }

    // Si se presionó el botón "Parar"
    if (isset($_POST['parar'])) {
        $actualizado = false; // Volvemos al estado inicial
    }
}

// Leer el modelo actual de la base
$resultado = $conn->query("SELECT modelo FROM configuracion WHERE id=1");
$modelo = "";
if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $modelo = $fila['modelo'];
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurar Modelo</title>
    <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }

    header {
      display: flex;
      justify-content: space-between; /* separa izquierda y derecha */
      align-items: center; /* centra verticalmente */
      background-color: #f4f4f4;
      padding: 15px 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .nombre {
      font-size: 1.5em;
      font-weight: bold;
      color: #333;
    }

    .perfil img {
      width: 50px;
      height: 50px;
      border-radius: 50%; /* lo hace circular */
      object-fit: cover;  /* asegura que la imagen no se deforme */
      border: 2px solid #333;
    }
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
        <!-- Estado después de elegir -->
        <p>El producto es: <strong><?php echo $producto; ?></strong></p>
        <form method="POST">
            <button type="submit" name="parar">Parar</button>
        </form>
    <?php else: ?>
        <!-- Estado inicial -->
        <form method="POST">
            <label>Nuevo modelo:</label>
            <input type="text" name="modelo" required>
            <button type="submit">Actualizar</button>
        </form>
    <?php endif; ?>
</body>
</html>