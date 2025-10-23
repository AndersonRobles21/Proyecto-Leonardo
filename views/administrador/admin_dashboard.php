<?php
session_start();
include("../../models/conexion.php");


// 🔒 Validar que el usuario sea administrador (rol = 1)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

$mensaje = "";

// Generar CSRF token simple
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// ✅ Agregar viaje nuevo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar'])) {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        $destino = trim($_POST['destino']);
        $fecha_salida = $_POST['fecha_salida'];
        $fecha_regreso = $_POST['fecha_regreso'];
        $precio = $_POST['precio'];
        $cupos = $_POST['cupos'];   

        // Validar formato de fecha
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha_salida) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha_regreso)) {
            $mensaje = "❌ Formato de fecha inválido. Usa AAAA-MM-DD.";
        } else {
            $anio = (int)substr($fecha_salida, 0, 4);
            if ($anio < 2020 || $anio > 2035) {
                $mensaje = "❌ Año fuera de rango (2020–2035).";
            } else {
                // Prepared statement
                $ins = mysqli_prepare($conn, "INSERT INTO viajes (destino, fecha_salida, fecha_regreso, precio, cupos) VALUES (?, ?, ?, ?, ?)");
                if ($ins) {
                    mysqli_stmt_bind_param($ins, 'sssii', $destino, $fecha_salida, $fecha_regreso, $precio, $cupos);
                    if (mysqli_stmt_execute($ins)) {
                        $mensaje = "✅ Viaje agregado correctamente.";
                    } else {
                        $mensaje = "❌ Error al insertar: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($ins);
                } else {
                    $mensaje = "❌ Error en la preparación de la consulta.";
                }
            }
        }
    }
}

// ❌ Eliminar viaje (ahora via POST y CSRF)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        $id_viaje = intval($_POST['eliminar']);
        $del = mysqli_prepare($conn, "DELETE FROM viajes WHERE id_viaje = ?");
        if ($del) {
            mysqli_stmt_bind_param($del, 'i', $id_viaje);
            if (mysqli_stmt_execute($del)) {
                $mensaje = "🗑️ Viaje eliminado correctamente.";
            } else {
                $mensaje = "❌ Error al eliminar: " . mysqli_error($conn);
            }
            mysqli_stmt_close($del);
        } else {
            $mensaje = "❌ Error en la preparación de la eliminación.";
        }
    }
}

// 📋 Consultar todos los viajes
$viajes = mysqli_query($conn, "SELECT * FROM viajes ORDER BY fecha_salida ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador</title>
    <!-- Tailwind CDN (Play CDN) - good for prototyping -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Pequeño ajuste para fuentes por defecto */
        :root { --accent: #00695c; }
    </style>
</head>
<body>
    <header class="bg-[color:var(--accent)] text-white py-6 shadow-md border-b-4 border-black/20">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
            <h1 class="text-2xl font-extrabold tracking-tight">🌍 Panel del Administrador</h1>
            <div class="text-sm">
                Bienvenido, <span class="font-semibold"><?= htmlspecialchars($_SESSION['nombre'] ?? ''); ?></span>
                <a href="../../controllers/logout.php" class="text-red-600 font-bold hover:underline">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto mt-8 bg-white p-6 rounded-sm shadow-2xl border-2 border-black/5">
        <?php if ($mensaje): ?>
            <p class="mb-4 text-center font-bold text-[color:var(--accent)]"><?= htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl text-[color:var(--accent)] font-bold">📋 Viajes Registrados</h2>
            <div class="flex items-center gap-3">
                <input id="search" type="text" placeholder="Buscar destino o fecha..." class="px-3 py-2 border-2 border-black/10 rounded-sm" />
                <button id="openModal" class="bg-[color:var(--accent)] text-white px-4 py-2 rounded-sm border-2 border-black/10 hover:brightness-90">+ Agregar viaje</button>
            </div>
        </div>

        <h2 class="text-lg font-bold mb-3">📋 Viajes Registrados</h2>
        <div class="overflow-x-auto">
    <table id="viajesTable" class="w-full table-fixed border-collapse text-sm">
            <thead>
            <tr class="bg-[color:var(--accent)] text-white">
                <th class="p-2 border-r border-black/10">ID</th>
                <th class="p-2 border-r border-black/10">Destino</th>
                <th class="p-2 border-r border-black/10">Salida</th>
                <th class="p-2 border-r border-black/10">Regreso</th>
                <th class="p-2 border-r border-black/10">Precio</th>
                <th class="p-2 border-r border-black/10">Cupos</th>
                <th class="p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($viajes) > 0): ?>
                <?php while ($v = mysqli_fetch_assoc($viajes)): ?>
                <tr class="odd:bg-gray-50 even:bg-white border-t border-black/5">
                    <td class="p-2 text-center"><?= htmlspecialchars($v['id_viaje']); ?></td>
                    <td class="p-2 text-center"><?= htmlspecialchars($v['destino']); ?></td>
                    <td class="p-2 text-center"><?= htmlspecialchars($v['fecha_salida']); ?></td>
                    <td class="p-2 text-center"><?= htmlspecialchars($v['fecha_regreso']); ?></td>
                    <td class="p-2 text-center"><?= htmlspecialchars(number_format($v['precio'], 0, ',', '.')); ?></td>
                    <td class="p-2 text-center"><?= htmlspecialchars($v['cupos']); ?></td>
                    <td class="p-2 text-center">
                        <form method="POST" action="" onsubmit="return confirm('¿Eliminar este viaje?');" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button name="eliminar" value="<?= htmlspecialchars($v['id_viaje']); ?>" class="text-red-600 font-bold">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="p-4 text-center">No hay viajes registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </main>
    <!-- Modal: Agregar viaje -->
    <div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-md p-6 w-full max-w-lg">
            <h3 class="text-lg font-bold mb-4">✈️ Agregar nuevo viaje</h3>
            <form method="POST" action="" class="grid grid-cols-1 gap-3" id="addForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input class="px-3 py-2 border-2 border-black/10 rounded-sm" type="text" name="destino" placeholder="Destino" required>
                <div class="flex gap-3">
                    <input class="px-3 py-2 border-2 border-black/10 rounded-sm w-1/2" type="date" name="fecha_salida" required min="2024-01-01" max="2035-12-31">
                    <input class="px-3 py-2 border-2 border-black/10 rounded-sm w-1/2" type="date" name="fecha_regreso" required min="2024-01-01" max="2035-12-31">
                </div>
                <div class="flex gap-3">
                    <input class="px-3 py-2 border-2 border-black/10 rounded-sm w-1/2" type="number" name="precio" placeholder="Precio" min="100" required>
                    <input class="px-3 py-2 border-2 border-black/10 rounded-sm w-1/2" type="number" name="cupos" placeholder="Cupos" min="1" required>
                </div>
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" id="closeModal" class="px-3 py-2 border rounded-sm">Cancelar</button>
                    <button type="submit" name="agregar" class="px-4 py-2 bg-[color:var(--accent)] text-white rounded-sm">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal
        const modal = document.getElementById('modal');
        const openModal = document.getElementById('openModal');
        const closeModal = document.getElementById('closeModal');
        openModal.addEventListener('click', () => modal.classList.remove('hidden'));
        closeModal.addEventListener('click', () => modal.classList.add('hidden'));

        // Búsqueda cliente
        const search = document.getElementById('search');
        const table = document.getElementById('viajesTable');
        search.addEventListener('input', () => {
            const q = search.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(r => {
                const text = r.innerText.toLowerCase();
                r.style.display = text.includes(q) ? '' : 'none';
            });
        });

        // Simple paginación cliente (opcional) - muestra 10 por página
        (function(){
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const perPage = 10;
            if (rows.length <= perPage) return;
            let current = 0;
            const pager = document.createElement('div');
            pager.className = 'mt-4 flex gap-2 justify-center';
            function render() {
                // limpiar
                table.querySelectorAll('tbody tr').forEach((r,i)=> r.style.display = (i>=current && i<current+perPage) ? '' : 'none');
                pager.innerHTML = '';
                const pages = Math.ceil(rows.length / perPage);
                for (let i=0;i<pages;i++){
                    const btn = document.createElement('button');
                    btn.textContent = i+1;
                    btn.className = 'px-2 py-1 border rounded-sm ' + (i===Math.floor(current/perPage) ? 'bg-[color:var(--accent)] text-white' : '');
                    btn.onclick = ()=>{ current = i*perPage; render(); };
                    pager.appendChild(btn);
                }
            }
            table.parentNode.appendChild(pager);
            render();
        })();
    </script>
</body>
</html>
