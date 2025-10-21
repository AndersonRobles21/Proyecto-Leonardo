<?php
session_start();
include("conexion.php");

// Validar rol del cliente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = "";

// Registrar nueva reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_viaje'])) {
    $id_viaje = $_POST['id_viaje'];
    $personas = $_POST['personas'];

    $sql = "INSERT INTO reservas (id_usuario, id_viaje, cantidad_personas) 
            VALUES ('$id_usuario', '$id_viaje', '$personas')";
    
    if (mysqli_query($conn, $sql)) {
        $mensaje = "✅ Reserva creada con éxito.";
    } else {
        $mensaje = "❌ Error al crear la reserva: " . mysqli_error($conn);
    }
}

// Consultar viajes disponibles
$viajes = mysqli_query($conn, "SELECT * FROM viajes ORDER BY fecha_salida ASC");

// Consultar reservas del cliente
$reservas = mysqli_query($conn, "
    SELECT r.id_reserva, v.destino, v.fecha_salida, v.fecha_regreso, r.cantidad_personas, r.estado
    FROM reservas r
    INNER JOIN viajes v ON r.id_viaje = v.id_viaje
    WHERE r.id_usuario = '$id_usuario'
    ORDER BY r.fecha_reserva DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Cliente</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .contenedor {
            display: flex;
            flex-direction: column;
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #009688;
            color: white;
        }
        button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover { background: #0d5ec2; }
        .mensaje { text-align: center; color: green; }
        a { text-decoration: none; color: #009688; }
    </style>
</head>
<body>
    <h1>Bienvenido, <?= $_SESSION['nombre']; ?> 🧳</h1>
    <p style="text-align:center;"><a href="logout.php">Cerrar Sesión</a></p>
    <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>

    <div class="contenedor">
        <!-- VIAJES DISPONIBLES -->
        <section>
            <h2>🌍 Viajes Disponibles</h2>
            <table>
                <tr>
                    <th>Destino</th>
                    <th>Salida</th>
                    <th>Regreso</th>
                    <th>Precio</th>
                    <th>Cupos</th>
                    <th>Reservar</th>
                </tr>
                <?php while($v = mysqli_fetch_assoc($viajes)) { ?>
                <tr>
                    <td><?= $v['destino']; ?></td>
                    <td><?= $v['fecha_salida']; ?></td>
                    <td><?= $v['fecha_regreso']; ?></td>
                    <td>$<?= number_format($v['precio'], 0, ',', '.'); ?></td>
                    <td><?= $v['cupos']; ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="id_viaje" value="<?= $v['id_viaje']; ?>">
                            <input type="number" name="personas" value="1" min="1" max="<?= $v['cupos']; ?>" required>
                            <button type="submit">Reservar</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </section>

        <!-- MIS RESERVAS -->
        <section>
            <h2>🧾 Mis Reservas</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Destino</th>
                    <th>Salida</th>
                    <th>Regreso</th>
                    <th>Personas</th>
                    <th>Estado</th>
                </tr>
                <?php if (mysqli_num_rows($reservas) > 0) {
                    while($r = mysqli_fetch_assoc($reservas)) { ?>
                        <tr>
                            <td><?= $r['id_reserva']; ?></td>
                            <td><?= $r['destino']; ?></td>
                            <td><?= $r['fecha_salida']; ?></td>
                            <td><?= $r['fecha_regreso']; ?></td>
                            <td><?= $r['cantidad_personas']; ?></td>
                            <td><?= $r['estado']; ?></td>
                        </tr>
                <?php } } else { ?>
                    <tr><td colspan="6">No tienes reservas aún.</td></tr>
                <?php } ?>
            </table>
        </section>
    </div>
</body>
</html>
