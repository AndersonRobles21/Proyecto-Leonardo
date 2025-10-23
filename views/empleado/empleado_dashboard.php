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
        $mensaje = "✅ Estado de la reserva actualizado correctamente.";
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-400 via-sky-300 to-teal-300 flex flex-col items-center py-10">

    <!-- Contenedor principal -->
    <div class="w-[95%] md:w-4/5 lg:w-3/4 bg-white/90 backdrop-blur-xl shadow-2xl rounded-3xl p-10 border border-blue-200 transition-all duration-500">

        <!-- Encabezado -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 via-blue-500 to-teal-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0m7.5 0a3.75 3.75 0 11-7.5 0m7.5 0V6a2.25 2.25 0 00-2.25-2.25H9A2.25 2.25 0 006.75 6v.75M4.5 20.25a7.5 7.5 0 1115 0" />
                    </svg>
                </div>
                <h1 class="text-4xl font-extrabold text-indigo-700 drop-shadow-lg">Panel del Empleado</h1>
            </div>
            <a href="../../controllers/logout.php"
                class="bg-gradient-to-r from-red-500 via-pink-500 to-orange-400 hover:scale-105 transition-transform text-white font-bold py-3 px-6 rounded-xl shadow-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75V6A2.25 2.25 0 0015 3.75H9A2.25 2.25 0 006.75 6v.75m10.5 0v10.5A2.25 2.25 0 0115 19.5H9a2.25 2.25 0 01-2.25-2.25V6.75m10.5 0h-13.5" />
                </svg>
                Cerrar sesión
            </a>
        </div>

        <!-- Mensaje -->
        <?php if ($mensaje): ?>
            <div class="text-center text-green-700 font-semibold text-lg mb-6 animate-pulse">
                <?= $mensaje; ?>
            </div>
        <?php endif; ?>

        <!-- Tabla de reservas -->
        <h2 class="text-2xl font-bold text-teal-700 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17l4 4 4-4m0-5V3m-8 9v6a2 2 0 002 2h4a2 2 0 002-2v-6" />
            </svg>
            Reservas de Clientes
        </h2>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-xl">
            <table class="w-full text-center">
                <thead class="bg-gradient-to-r from-indigo-500 via-sky-500 to-teal-500 text-white text-lg">
                    <tr>
                        <th class="py-4 px-2 font-semibold">ID</th>
                        <th class="py-4 px-2 font-semibold">Cliente</th>
                        <th class="py-4 px-2 font-semibold">Destino</th>
                        <th class="py-4 px-2 font-semibold">Salida</th>
                        <th class="py-4 px-2 font-semibold">Regreso</th>
                        <th class="py-4 px-2 font-semibold">Personas</th>
                        <th class="py-4 px-2 font-semibold">Estado</th>
                        <th class="py-4 px-2 font-semibold">Actualizar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                <?php while($r = mysqli_fetch_assoc($reservas)) { ?>
                    <tr class="hover:bg-indigo-50 transition duration-200">
                        <td class="py-3 font-bold text-indigo-700"><?= $r['id_reserva']; ?></td>
                        <td class="py-3 text-gray-800"><?= $r['cliente']; ?></td>
                        <td class="py-3 text-blue-600 font-semibold"><?= $r['destino']; ?></td>
                        <td class="py-3 text-gray-600"><?= $r['fecha_salida']; ?></td>
                        <td class="py-3 text-gray-600"><?= $r['fecha_regreso']; ?></td>
                        <td class="py-3 text-gray-600"><?= $r['cantidad_personas']; ?></td>
                        <td class="py-3">
                            <span class="inline-flex items-center gap-2 px-4 py-1 rounded-full font-bold text-xs shadow 
                                <?php
                                    if ($r['estado'] == 'Pendiente') echo 'bg-yellow-100 text-yellow-800 border border-yellow-300';
                                    elseif ($r['estado'] == 'Confirmada') echo 'bg-green-100 text-green-800 border border-green-300';
                                    else echo 'bg-red-100 text-red-800 border border-red-300';
                                ?>">
                                <?= $r['estado']; ?>
                            </span>
                        </td>
                        <td class="py-3">
                            <form method="POST" action="" class="flex items-center justify-center gap-2">
                                <input type="hidden" name="id_reserva" value="<?= $r['id_reserva']; ?>">
                                <select name="estado" class="border border-gray-300 rounded-lg px-2 py-1 bg-white font-semibold focus:ring-2 focus:ring-indigo-400">
                                    <option <?= $r['estado']=="Pendiente"?"selected":""; ?>>Pendiente</option>
                                    <option <?= $r['estado']=="Confirmada"?"selected":""; ?>>Confirmada</option>
                                    <option <?= $r['estado']=="Cancelada"?"selected":""; ?>>Cancelada</option>
                                </select>
                                <button type="submit" class="bg-gradient-to-r from-indigo-500 via-sky-500 to-teal-400 hover:scale-105 transition-transform text-white font-bold px-4 py-2 rounded-lg shadow-lg">
                                    Guardar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
