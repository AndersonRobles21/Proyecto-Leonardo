<?php
session_start();
include("../../models/conexion.php");

// 🔒 Validar que el usuario sea administrador (rol = 1)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

$mensaje = "";

// Procesar eliminación de usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_usuario'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        $id_usuario = $_POST['id_usuario'];
        
        // No permitir eliminar al administrador actual
        if ($id_usuario == $_SESSION['id_usuario']) {
            $mensaje = "❌ No puedes eliminar tu propia cuenta de administrador.";
        } else {
            // Primero eliminar las reservas asociadas
            $del_reservas = mysqli_prepare($conn, "DELETE FROM reservas WHERE id_usuario = ?");
            mysqli_stmt_bind_param($del_reservas, "i", $id_usuario);
            mysqli_stmt_execute($del_reservas);
            
            // Luego eliminar el usuario
            $del_usuario = mysqli_prepare($conn, "DELETE FROM usuarios WHERE id_usuario = ?");
            mysqli_stmt_bind_param($del_usuario, "i", $id_usuario);
            if (mysqli_stmt_execute($del_usuario)) {
                $mensaje = "✅ Usuario y sus reservas eliminados correctamente.";
            } else {
                $mensaje = "❌ Error al eliminar el usuario.";
            }
        }
    }
}

// Procesar eliminación de reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_reserva'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        $id_reserva = $_POST['id_reserva'];
        $del = mysqli_prepare($conn, "DELETE FROM reservas WHERE id_reserva = ?");
        mysqli_stmt_bind_param($del, "i", $id_reserva);
        if (mysqli_stmt_execute($del)) {
            $mensaje = "✅ Reserva eliminada correctamente.";
        } else {
            $mensaje = "❌ Error al eliminar la reserva.";
        }
    }
}

// Procesar cambio de estado de reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_estado'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        $id_reserva = $_POST['id_reserva'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        // Verificar el estado actual de la reserva
        $check_estado = mysqli_prepare($conn, "SELECT estado FROM reservas WHERE id_reserva = ?");
        mysqli_stmt_bind_param($check_estado, "i", $id_reserva);
        mysqli_stmt_execute($check_estado);
        $result = mysqli_stmt_get_result($check_estado);
        $estado_actual = mysqli_fetch_assoc($result)['estado'];
        mysqli_stmt_close($check_estado);
        
        // No permitir cambiar si ya está confirmada o cancelada
        if ($estado_actual === 'confirmada' && $nuevo_estado !== 'confirmada') {
            $mensaje = "⚠️ No se puede cambiar una reserva ya confirmada.";
        } elseif ($estado_actual === 'cancelada' && $nuevo_estado !== 'cancelada') {
            $mensaje = "⚠️ No se puede cambiar una reserva ya cancelada.";
        } else {
            // Estados válidos
            $estados_validos = ['pendiente', 'confirmada', 'cancelada'];
            
            if (in_array($nuevo_estado, $estados_validos)) {
                $update = mysqli_prepare($conn, "UPDATE reservas SET estado = ? WHERE id_reserva = ?");
                if ($update) {
                    mysqli_stmt_bind_param($update, "si", $nuevo_estado, $id_reserva);
                    if (mysqli_stmt_execute($update)) {
                        $mensaje = "✅ Estado de la reserva actualizado correctamente.";
                    } else {
                        $mensaje = "❌ Error al actualizar el estado.";
                    }
                    mysqli_stmt_close($update);
                }
            } else {
                $mensaje = "❌ Estado no válido.";
            }
        }
    }
}

// Obtener lista de usuarios
$query_usuarios = "SELECT u.id_usuario, u.nombre, u.correo, u.id_rol, 
                  (SELECT COUNT(*) FROM reservas WHERE id_usuario = u.id_usuario) as total_reservas 
                  FROM usuarios u ORDER BY u.id_usuario";
$resultado_usuarios = mysqli_query($conn, $query_usuarios);

// Obtener lista de reservas con detalles y mantener el estado actual
$query_reservas = "SELECT r.id_reserva, r.fecha_reserva, u.nombre as nombre_usuario, 
                   v.destino, v.fecha_salida, v.fecha_regreso, r.estado,
                   CASE 
                     WHEN r.estado = 'confirmada' THEN 'confirmada'
                     WHEN r.estado = 'cancelada' THEN 'cancelada'
                     ELSE 'pendiente'
                   END as estado_actual
                   FROM reservas r 
                   JOIN usuarios u ON r.id_usuario = u.id_usuario 
                   JOIN viajes v ON r.id_viaje = v.id_viaje 
                   ORDER BY r.fecha_reserva DESC";
$resultado_reservas = mysqli_query($conn, $query_reservas);

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
        
        // Primero eliminamos las reservas asociadas
        $del_reservas = mysqli_prepare($conn, "DELETE FROM reservas WHERE id_viaje = ?");
        if ($del_reservas) {
            mysqli_stmt_bind_param($del_reservas, 'i', $id_viaje);
            if (mysqli_stmt_execute($del_reservas)) {
                mysqli_stmt_close($del_reservas);
                
                // Ahora eliminamos el viaje
                $del = mysqli_prepare($conn, "DELETE FROM viajes WHERE id_viaje = ?");
                if ($del) {
                    mysqli_stmt_bind_param($del, 'i', $id_viaje);
                    if (mysqli_stmt_execute($del)) {
                        $mensaje = "🗑️ Viaje y sus reservas eliminados correctamente.";
                    } else {
                        $mensaje = "❌ Error al eliminar el viaje: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($del);
                } else {
                    $mensaje = "❌ Error en la preparación de la eliminación del viaje.";
                }
            } else {
                $mensaje = "❌ Error al eliminar las reservas asociadas: " . mysqli_error($conn);
                mysqli_stmt_close($del_reservas);
            }
        } else {
            $mensaje = "❌ Error en la preparación de la eliminación de las reservas.";
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
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --accent: #0d9488; /* teal-600 */
      --accent-hover: #0f766e;
    }
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <!-- HEADER -->
  <header class="bg-[color:var(--accent)] text-white py-5 shadow-lg">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
      <h1 class="text-2xl font-extrabold tracking-tight">🌍 Panel del Administrador</h1>
      <div class="text-sm flex items-center gap-3">
        <span>Bienvenido, <b><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></b></span>
        <a href="../../controllers/logout.php"
           class="bg-white/10 px-3 py-1 rounded-md border border-white/20 hover:bg-white/20 transition">Cerrar sesión</a>
      </div>
    </div>
  </header>

  <!-- MAIN -->
  <main class="max-w-7xl mx-auto p-6 mt-8 bg-white rounded-2xl shadow-xl">
    <?php if ($mensaje): ?>
      <div class="mb-5 p-4 rounded-lg text-center font-semibold
        <?= str_contains($mensaje, '✅') ? 'bg-green-50 text-green-700 border border-green-200' :
           (str_contains($mensaje, '⚠️') ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' :
           'bg-red-50 text-red-700 border border-red-200') ?>">
        <?= htmlspecialchars($mensaje); ?>
      </div>
    <?php endif; ?>

    <div class="flex flex-wrap justify-between items-center mb-6">
      <h2 class="text-xl font-bold text-gray-800">📋 Viajes Registrados</h2>
      <div class="flex gap-3">
        <input id="search" type="text" placeholder="Buscar destino o fecha..."
               class="px-3 py-2 border rounded-lg text-sm w-64 focus:ring-2 focus:ring-[color:var(--accent)] outline-none" />
        <button id="openModal"
                class="bg-[color:var(--accent)] hover:bg-[color:var(--accent-hover)] text-white px-4 py-2 rounded-lg shadow-md transition">
          + Agregar viaje
        </button>
      </div>
    </div>

    <!-- GRID SECTION -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- VIAJES -->
      <section class="bg-white rounded-xl shadow-md border border-gray-100">
        <h3 class="text-lg font-bold p-4 border-b bg-gray-50 rounded-t-xl">✈️ Viajes</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="bg-[color:var(--accent)] text-white">
              <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Destino</th>
                <th class="p-3 text-left">Salida</th>
                <th class="p-3 text-left">Regreso</th>
                <th class="p-3 text-left">Precio</th>
                <th class="p-3 text-left">Cupos</th>
                <th class="p-3 text-center">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if (mysqli_num_rows($viajes) > 0): ?>
                <?php while ($v = mysqli_fetch_assoc($viajes)): ?>
                  <tr class="hover:bg-gray-50 transition">
                    <td class="p-3"><?= $v['id_viaje']; ?></td>
                    <td class="p-3 font-medium"><?= htmlspecialchars($v['destino']); ?></td>
                    <td class="p-3"><?= $v['fecha_salida']; ?></td>
                    <td class="p-3"><?= $v['fecha_regreso']; ?></td>
                    <td class="p-3">$<?= number_format($v['precio'], 0, ',', '.'); ?></td>
                    <td class="p-3"><?= $v['cupos']; ?></td>
                    <td class="p-3 text-center">
                      <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar este viaje?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button name="eliminar" value="<?= $v['id_viaje']; ?>"
                                class="text-red-500 hover:text-red-700 transition">🗑️</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="p-4 text-center text-gray-500">No hay viajes registrados.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- USUARIOS -->
      <section class="bg-white rounded-xl shadow-md border border-gray-100">
        <h3 class="text-lg font-bold p-4 border-b bg-gray-50 rounded-t-xl">👥 Usuarios</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="bg-[color:var(--accent)] text-white">
              <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Nombre</th>
                <th class="p-3 text-left">Correo</th>
                <th class="p-3 text-left">Rol</th>
                <th class="p-3 text-left">Reservas</th>
                <th class="p-3 text-center">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php while ($u = mysqli_fetch_assoc($resultado_usuarios)): ?>
                <tr class="hover:bg-gray-50 transition">
                  <td class="p-3"><?= $u['id_usuario']; ?></td>
                  <td class="p-3 font-medium"><?= htmlspecialchars($u['nombre']); ?></td>
                  <td class="p-3"><?= htmlspecialchars($u['correo']); ?></td>
                  <td class="p-3"><?= $u['id_rol'] == 1 ? '👑 Admin' : ($u['id_rol'] == 2 ? '👔 Empleado' : '👤 Cliente'); ?></td>
                  <td class="p-3"><?= $u['total_reservas']; ?></td>
                  <td class="p-3 text-center">
                    <?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                      <form method="POST" onsubmit="return confirm('¿Eliminar este usuario?');" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario']; ?>">
                        <button name="eliminar_usuario" class="text-red-500 hover:text-red-700">🗑️</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- RESERVAS -->
    <section class="mt-8 bg-white rounded-xl shadow-md border border-gray-100">
      <h3 class="text-lg font-bold p-4 border-b bg-gray-50 rounded-t-xl">🎫 Reservas</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm divide-y divide-gray-200">
          <thead class="bg-[color:var(--accent)] text-white">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">Cliente</th>
              <th class="p-3">Destino</th>
              <th class="p-3">Reserva</th>
              <th class="p-3">Salida</th>
              <th class="p-3">Estado</th>
              <th class="p-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php while ($r = mysqli_fetch_assoc($resultado_reservas)): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="p-3 text-center"><?= $r['id_reserva']; ?></td>
                <td class="p-3"><?= htmlspecialchars($r['nombre_usuario']); ?></td>
                <td class="p-3"><?= htmlspecialchars($r['destino']); ?></td>
                <td class="p-3"><?= $r['fecha_reserva']; ?></td>
                <td class="p-3"><?= $r['fecha_salida']; ?></td>
                <td class="p-3 text-center font-semibold
                    <?= $r['estado'] == 'confirmada' ? 'text-green-600' :
                       ($r['estado'] == 'pendiente' ? 'text-yellow-600' : 'text-red-600'); ?>">
                  <?= ucfirst($r['estado']); ?>
                </td>
                <td class="p-3 text-center">
                  <form method="POST" onsubmit="return confirm('¿Eliminar esta reserva?');" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="id_reserva" value="<?= $r['id_reserva']; ?>">
                    <button name="eliminar_reserva" class="text-red-500 hover:text-red-700">🗑️</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- MODAL -->
  <div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl animate-[fadeIn_0.3s_ease]">
      <h3 class="text-xl font-bold mb-4 text-gray-800">✈️ Nuevo Viaje</h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="text" name="destino" placeholder="Destino"
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]" required>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-sm text-gray-600">Salida</label>
            <input type="date" name="fecha_salida"
                   class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]" required>
          </div>
          <div>
            <label class="text-sm text-gray-600">Regreso</label>
            <input type="date" name="fecha_regreso"
                   class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[color:var(--accent)]" required>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <input type="number" name="precio" placeholder="Precio"
                 class="border rounded-lg px-3 py-2" required>
          <input type="number" name="cupos" placeholder="Cupos"
                 class="border rounded-lg px-3 py-2" required>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button id="closeModal" type="button"
                  class="px-4 py-2 rounded-lg border hover:bg-gray-100 transition">Cancelar</button>
          <button name="agregar"
                  class="px-4 py-2 bg-[color:var(--accent)] text-white rounded-lg hover:bg-[color:var(--accent-hover)] shadow-md transition">
            Agregar
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById('modal');
    document.getElementById('openModal').onclick = () => modal.classList.remove('hidden','opacity-0');
    document.getElementById('closeModal').onclick = () => modal.classList.add('hidden');
  </script>
</body>
</html>

