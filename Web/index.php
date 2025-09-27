<?php
// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "sensor_placas";
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}
//TEST
// Leer el modelo actual y su fecha/hora de inicio
$resultado = $conn->query("SELECT modelo, inicio_modelo FROM configuracion WHERE id=1");
$modeloActual = "";
$inicioModelo = date('Y-m-d H:i:s'); // default
if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $modeloActual = $fila['modelo'];
    if (!empty($fila['inicio_modelo'])) {
        $inicioModelo = $fila['inicio_modelo'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lecturas del Sensor IR</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f9f9f9; }
h1 { color: #333; }
table { border-collapse: collapse; width: 100%; background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { background-color: #4CAF50; color: white; }
tr:nth-child(even) { background-color: #f2f2f2; }
.recent { font-weight: bold; border: 2px solid #000; }
@media (max-width: 600px) { table, th, td { font-size: 12px; } }
</style>
</head>
<body>

<h1>Resumen del Último Modelo</h1>
<table id="tablaResumen">
    <thead>
        <tr>
            <th>Modelo</th>
            <th>Cantidad de lecturas</th>
            <th>Tiempo total</th>
            <th>Tiempo de ciclo promedio</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="4">Cargando...</td>
        </tr>
    </tbody>
</table>

<h1>Lecturas del Sensor IR</h1>
<table id="tablaDatos">
    <thead>
        <tr>
            <th>Fecha / Hora</th>
            <th>Evento</th>
            <th>Sensor ID</th>
            <th>Ubicación</th>
            <th>Modelo</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
// Variables desde PHP
const modeloActual = '<?php echo $modeloActual; ?>';
const inicioModelo = '<?php echo $inicioModelo; ?>';

// Colores automáticos por modelo
const coloresModelo = {};
function colorAleatorio() {
    const r = Math.floor((Math.random() * 127) + 127);
    const g = Math.floor((Math.random() * 127) + 127);
    const b = Math.floor((Math.random() * 127) + 127);
    return `rgb(${r},${g},${b})`;
}

function parseFecha(fechaStr) {
    return new Date(fechaStr.replace(' ', 'T')).getTime() / 1000;
}

function segundosAHHMMSS(segundos) {
    const h = Math.floor(segundos / 3600).toString().padStart(2, '0');
    const m = Math.floor((segundos % 3600) / 60).toString().padStart(2, '0');
    const s = Math.floor(segundos % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

async function cargarDatos() {
    try {
        const response = await fetch('datos.php');
        const data = await response.json();

        const tbody = document.querySelector("#tablaDatos tbody");
        tbody.innerHTML = '';

        // Filtrar solo lecturas del modelo actual desde inicioModelo
        let lecturasModelo = data.filter(d => 
            d.modelo === modeloActual && parseFecha(d.fecha_hora) >= parseFecha(inicioModelo)
        );

        // Ordenar cronológicamente
        lecturasModelo.sort((a,b) => parseFecha(a.fecha_hora) - parseFecha(b.fecha_hora));

        // Últimas 5 lecturas para resaltar
        const recientes = lecturasModelo.slice(-5);

        // Mostrar lecturas y asignar evento desde 1
        lecturasModelo.forEach((item, index) => {
            const tr = document.createElement('tr');
            if (!coloresModelo[item.modelo]) coloresModelo[item.modelo] = colorAleatorio();
            tr.style.backgroundColor = coloresModelo[item.modelo];
            if (recientes.includes(item)) tr.classList.add('recent');

            tr.innerHTML = `
                <td>${item.fecha_hora}</td>
                <td>${index + 1}</td> <!-- evento reiniciado desde 1 -->
                <td>${item.sensor_id}</td>
                <td>${item.ubicacion}</td>
                <td>${item.modelo}</td>
            `;
            tbody.appendChild(tr);
        });

        // Resumen del modelo actual
        const cantidad = lecturasModelo.length;

        let tiempoTotal = 0;
        let tiempoCicloPromedio = 0;

        if (cantidad > 0) {
            // Fechas mínima y máxima
            let minFecha = parseFecha(lecturasModelo[0].fecha_hora);
            let maxFecha = minFecha;
            lecturasModelo.forEach(l => {
                const t = parseFecha(l.fecha_hora);
                if (t < minFecha) minFecha = t;
                if (t > maxFecha) maxFecha = t;
            });
            tiempoTotal = maxFecha - minFecha;

            // Ciclo promedio
            if (cantidad > 1) {
                let sum = 0;
                for (let i = 0; i < lecturasModelo.length -1; i++) {
                    const t1 = parseFecha(lecturasModelo[i].fecha_hora);
                    const t2 = parseFecha(lecturasModelo[i+1].fecha_hora);
                    sum += t2 - t1;
                }
                tiempoCicloPromedio = sum / (lecturasModelo.length -1);
            }
        }

        const resumenTbody = document.querySelector("#tablaResumen tbody");
        resumenTbody.innerHTML = `
            <tr style="background-color:${coloresModelo[modeloActual] || '#fff'}">
                <td>${modeloActual}</td>
                <td>${cantidad}</td>
                <td>${segundosAHHMMSS(tiempoTotal)}</td>
                <td>${segundosAHHMMSS(tiempoCicloPromedio)}</td>
            </tr>
        `;

    } catch (error) {
        console.error('Error al cargar los datos:', error);
    }
}

cargarDatos();
setInterval(cargarDatos, 1000);
</script>
</body>
</html>

