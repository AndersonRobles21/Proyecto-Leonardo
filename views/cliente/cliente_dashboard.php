<?php
session_start();
include("../../models/conexion.php");

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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#3B82F6',
                    },
                },
            },
        }
    </script>
</head>

<body class="bg-gradient-to-br from-indigo-50 via-blue-50 to-white dark:from-gray-900 dark:via-slate-900 dark:to-gray-800 min-h-screen text-gray-800 dark:text-gray-100 transition-all duration-500">

    <!-- NAVBAR -->
    <header class="flex justify-between items-center px-8 py-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg shadow-md sticky top-0 z-50">
        <h1 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
            🌎 Panel del Cliente
        </h1>
        <div class="flex items-center gap-4">
            <span class="font-semibold"><?= $_SESSION['nombre']; ?> 🧳</span>
            <a href="../../controllers/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg transition">
                Cerrar sesión
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto py-10 px-5 space-y-10">

        <?php if ($mensaje): ?>
            <p class="text-center text-green-600 dark:text-green-400 font-semibold text-lg"><?= $mensaje; ?></p>
        <?php endif; ?>

        <!-- VIAJES DISPONIBLES -->
        <section class="bg-white/80 dark:bg-gray-900/60 backdrop-blur-md rounded-3xl shadow-lg p-8 border border-gray-200/40 dark:border-gray-700">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2 text-blue-700 dark:text-blue-400">
                🌍 Viajes Disponibles
            </h2>
            
            <div class="overflow-x-auto rounded-xl">
                <table class="w-full text-sm text-center border-collapse">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="p-3">Destino</th>
                            <th class="p-3">Salida</th>
                            <th class="p-3">Regreso</th>
                            <th class="p-3">Precio</th>
                            <th class="p-3">Cupos</th>
                            <th class="p-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php while($v = mysqli_fetch_assoc($viajes)) { ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-gray-800 transition">
                            <td class="p-3 font-semibold"><?= $v['destino']; ?></td>
                            <td class="p-3"><?= $v['fecha_salida']; ?></td>
                            <td class="p-3"><?= $v['fecha_regreso']; ?></td>
                            <td class="p-3">$<?= number_format($v['precio'], 0, ',', '.'); ?></td>
                            <td class="p-3"><?= $v['cupos']; ?></td>
                            <td class="p-3">
                                <form method="POST" action="" class="flex flex-col sm:flex-row items-center justify-center gap-2">
                                    <input 
                                        type="hidden" 
                                        name="id_viaje" 
                                        value="<?= $v['id_viaje']; ?>">
                                    <input 
                                        type="number" 
                                        name="personas" 
                                        value="1" 
                                        min="1" 
                                        max="<?= $v['cupos']; ?>" 
                                        required 
                                        class="w-20 text-center border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md p-1 focus:ring-2 focus:ring-indigo-400 outline-none">
                                    <button 
                                        type="submit" 
                                        class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-indigo-600 hover:to-blue-600 text-white font-semibold py-1.5 px-4 rounded-md shadow-md transition-all">
                                        Reservar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- MIS RESERVAS -->
        <section class="bg-white/80 dark:bg-gray-900/60 backdrop-blur-md rounded-3xl shadow-lg p-8 border border-gray-200/40 dark:border-gray-700">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2 text-indigo-700 dark:text-indigo-400">
                🧾 Mis Reservas
            </h2>
            
            <div class="overflow-x-auto rounded-xl">
                <table class="w-full text-sm text-center border-collapse">
                    <thead class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Destino</th>
                            <th class="p-3">Salida</th>
                            <th class="p-3">Regreso</th>
                            <th class="p-3">Personas</th>
                            <th class="p-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (mysqli_num_rows($reservas) > 0) {
                            while($r = mysqli_fetch_assoc($reservas)) { ?>
                                <tr class="hover:bg-indigo-50 dark:hover:bg-gray-800 transition">
                                    <td class="p-3"><?= $r['id_reserva']; ?></td>
                                    <td class="p-3 font-semibold"><?= $r['destino']; ?></td>
                                    <td class="p-3"><?= $r['fecha_salida']; ?></td>
                                    <td class="p-3"><?= $r['fecha_regreso']; ?></td>
                                    <td class="p-3"><?= $r['cantidad_personas']; ?></td>
                                    <td class="p-3">
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                            <?= $r['estado'] == 'Pendiente' ? 'bg-yellow-100 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-200' : 
                                               ($r['estado'] == 'Aprobada' ? 'bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200' : 
                                               'bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200'); ?>">
                                            <?= $r['estado']; ?>
                                        </span>
                                    </td>
                                </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="6" class="p-3 text-gray-500 dark:text-gray-400 italic">No tienes reservas aún.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
        © <?= date("Y"); ?> Agencia de Viajes · Todos los derechos reservados
    </footer>

</body>
</html>
