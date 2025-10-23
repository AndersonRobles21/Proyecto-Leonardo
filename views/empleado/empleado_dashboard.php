<?php
session_start();
include("../../models/conexion.php");


// Validar rol de empleado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
    header("Location: index.php");
    exit();
}

$mensaje = "";

// Actualizar estado de reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_reserva'])) {
    $id_reserva = $_POST['id_reserva'];
    $estado = $_POST['estado'];

    $sql = "UPDATE reservas SET estado = '$estado' WHERE id_reserva = '$id_reserva'";
    if (mysqli_query($conn, $sql)) {
        $mensaje = "✅ Estado de reserva actualizado correctamente.";
    } else {
        $mensaje = "❌ Error al actualizar: " . mysqli_error($conn);
    }
}

// Consultar todas las reservas
$reservas = mysqli_query($conn, "
    SELECT r.id_reserva, u.nombre AS cliente, v.destino, v.fecha_salida, v.fecha_regreso, r.cantidad_personas, r.estado
    FROM reservas r
    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
    INNER JOIN viajes v ON r.id_viaje = v.id_viaje
    ORDER BY r.fecha_reserva DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Empleado</title>
    <style>
        body { font-family: Arial; background: #f5f7fa; padding: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #1a73e8; color: white; }
        select, button { padding: 5px; border-radius: 5px; }
        button { background: #009688; color: white; border: none; cursor: pointer; }
        button:hover { background: #00796b; }
        .mensaje { text-align:center; color:green; }
    </style>
</head>
<body>
    <h1>Panel del Empleado 👨‍💼</h1>
    <a href="../../controllers/logout.php" class="text-red-600 font-bold hover:underline">Cerrar sesión</a>
    <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>

    <h2>Reservas de Clientes</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Destino</th>
            <th>Salida</th>
            <th>Regreso</th>
            <th>Personas</th>
            <th>Estado</th>
            <th>Actualizar</th>
        </tr>
        <?php while($r = mysqli_fetch_assoc($reservas)) { ?>
        <tr>
            <td><?= $r['id_reserva']; ?></td>
            <td><?= $r['cliente']; ?></td>
            <td><?= $r['destino']; ?></td>
            <td><?= $r['fecha_salida']; ?></td>
            <td><?= $r['fecha_regreso']; ?></td>
            <td><?= $r['cantidad_personas']; ?></td>
            <td><?= $r['estado']; ?></td>
            <td>
                <form method="POST" action="">
                    <input type="hidden" name="id_reserva" value="<?= $r['id_reserva']; ?>">
                    <select name="estado">
                        <option <?= $r['estado']=="Pendiente"?"selected":""; ?>>Pendiente</option>
                        <option <?= $r['estado']=="Confirmada"?"selected":""; ?>>Confirmada</option>
                        <option <?= $r['estado']=="Cancelada"?"selected":""; ?>>Cancelada</option>
                    </select>
                    <button type="submit">Guardar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
