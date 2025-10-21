<?php
session_start();
include("conexion.php");

// Validar rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

$mensaje = "";

// Agregar nuevo viaje
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nuevo_viaje'])) {
    $destino = $_POST['destino'];
    $salida = $_POST['fecha_salida'];
    $regreso = $_POST['fecha_regreso'];
    $precio = $_POST['precio'];
    $cupos = $_POST['cupos'];

    $sql = "INSERT INTO viajes (destino, fecha_salida, fecha_regreso, precio, cupos)
            VALUES ('$destino', '$salida', '$regreso', '$precio', '$cupos')";
    if (mysqli_query($conn, $sql)) {
        $mensaje = "✅ Viaje agregado correctamente.";
    } else {
        $mensaje = "❌ Error al agregar viaje: " . mysqli_error($conn);
    }
}

// Eliminar viaje
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    mysqli_query($conn, "DELETE FROM viajes WHERE id_viaje = '$id'");
    $mensaje = "🗑️ Viaje eliminado.";
}

// Consultar viajes y usuarios
$viajes = mysqli_query($conn, "SELECT * FROM viajes ORDER BY fecha_salida ASC");
$usuarios = mysqli_query($conn, "SELECT u.id_usuario, u.nombre, u.correo, r.nombre_rol 
                                 FROM usuarios u
                                 INNER JOIN roles r ON u.id_rol = r.id_rol
                                 ORDER BY u.id_usuario ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador</title>
    <style>
        body { font-family: Arial; background: #f9fafc; padding: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #009688; color: white; }
        button { background: #e53935; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #c62828; }
        input, select { padding: 5px; border-radius: 5px; }
        .mensaje { text-align:center; color:green; }
        form { margin-bottom: 20px; background:#fff; padding:15px; border-radius:10px; box-shadow:0 0 5px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <h1>Panel del Administrador 👑</h1>
    <p style="text-align:center;"><a href="logout.php">Cerrar Sesión</a></p>
    <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>

    <!-- Formulario nuevo viaje -->
    <form method="POST">
        <h2>✈️ Agregar Nuevo Viaje</h2>
        <input type="text" name="destino" placeholder="Destino" required>
        <input type="date" name="fecha_salida" required>
        <input type="date" name="fecha_regreso" required>
        <input type="number" name="precio" placeholder="Precio" required>
        <input type="number" name="cupos" placeholder="Cupos" required>
        <button type="submit" name="nuevo_viaje">Agregar</button>
    </form>

    <!-- Tabla viajes -->
    <h2>🌍 Viajes Actuales</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Destino</th>
            <th>Salida</th>
            <th>Regreso</th>
            <th>Precio</th>
            <th>Cupos</th>
            <th>Eliminar</th>
        </tr>
        <?php while($v = mysqli_fetch_assoc($viajes)) { ?>
        <tr>
            <td><?= $v['id_viaje']; ?></td>
            <td><?= $v['destino']; ?></td>
            <td><?= $v['fecha_salida']; ?></td>
            <td><?= $v['fecha_regreso']; ?></td>
            <td>$<?= number_format($v['precio'], 0, ',', '.'); ?></td>
            <td><?= $v['cupos']; ?></td>
            <td><a href="?eliminar=<?= $v['id_viaje']; ?>"><button>Eliminar</button></a></td>
        </tr>
        <?php } ?>
    </table>

    <!-- Usuarios -->
    <h2>👥 Usuarios Registrados</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
        </tr>
        <?php while($u = mysqli_fetch_assoc($usuarios)) { ?>
        <tr>
            <td><?= $u['id_usuario']; ?></td>
            <td><?= $u['nombre']; ?></td>
            <td><?= $u['correo']; ?></td>
            <td><?= $u['nombre_rol']; ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
