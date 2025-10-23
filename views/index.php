<?php
session_start();
include("../models/conexion.php");

// Si ya hay sesión activa, redirigir según rol
if (isset($_SESSION['rol'])) {
    switch ($_SESSION['rol']) {
        case 1: 
            header("Location: ../views/administrador/admin_dashboard.php"); 
            exit();
        case 2: 
            header("Location: ../views/empleado/empleado_dashboard.php"); 
            exit();
        case 3: 
            header("Location: ../views/cliente/cliente_dashboard.php"); 
            exit();
    }
}


$mensaje = "";
$correo_val = "";
$rol_val = "";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? '';

    $correo_val = htmlspecialchars($correo, ENT_QUOTES);
    $rol_val = htmlspecialchars($rol, ENT_QUOTES);

    if ($correo === '' || $contrasena === '' || $rol === '') {
        $mensaje = "⚠️ Por favor completa todos los campos.";
    } else {
        // Prepared statement para evitar inyección SQL
        $stmt = mysqli_prepare($conn, "SELECT id_usuario, nombre, contrasena, id_rol FROM usuarios WHERE correo = ? AND id_rol = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $correo, $rol);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && mysqli_num_rows($res) > 0) {
                $usuario = mysqli_fetch_assoc($res);

                // Soporta contraseñas hashed (password_verify) o texto plano (compatibilidad)
                if ((isset($usuario['contrasena']) && password_verify($contrasena, $usuario['contrasena'])) || $usuario['contrasena'] === $contrasena) {
                    // Login correcto
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['rol'] = $usuario['id_rol'];

                    switch ($usuario['id_rol']) {
                        case 1: header("Location: administrador/admin_dashboard.php"); exit();
                        case 2: header("Location: empleado/empleado_dashboard.php"); exit();
                         case 3: header("Location: cliente/cliente_dashboard.php"); exit();
                    }

                } else {
                    $mensaje = "❌ Contraseña incorrecta.";
                }
            } else {
                $mensaje = "⚠️ Usuario no encontrado para ese rol.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $mensaje = "❌ Error en la consulta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inicio de Sesión - Gestión de Viajes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--primary:#1a73e8;--accent:#009688}
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-[var(--primary)] to-[var(--accent)] min-h-screen flex items-center justify-center p-4">
    <main class="max-w-4xl w-full bg-white rounded-xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <!-- Left: illustration / brand -->
        <section class="hidden md:flex flex-col items-center justify-center bg-gradient-to-b from-[var(--primary)]/90 to-[var(--primary)]/70 text-white p-8">
            <div class="text-5xl mb-4">✈️</div>
            <h1 class="text-2xl font-extrabold mb-2">Gestión de Viajes</h1>
            <p class="text-sm/relaxed max-w-xs text-white/90">Administra viajes, reservas y usuarios desde un panel sencillo y rápido. Inicia sesión para continuar.</p>
        </section>

        <!-- Right: login form -->
        <section class="p-8">
            <div class="max-w-md mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">🔐 Iniciar Sesión</h2>
                <p class="text-sm text-gray-500 mb-6">Introduce tus credenciales para acceder.</p>

                <?php if ($mensaje): ?>
                    <div class="mb-4 p-3 rounded-md text-sm <?php echo strpos($mensaje,'❌')!==false ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-yellow-50 text-yellow-800 border border-yellow-100'; ?>">
                        <?= htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Correo</label>
                        <input name="correo" type="email" required value="<?= $correo_val; ?>" class="mt-1 block w-full rounded-md border-2 border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary)]" placeholder="tucorreo@ejemplo.com">
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <button type="button" id="togglePwd" class="text-xs text-gray-500 hover:underline">Mostrar</button>
                        </div>
                        <input id="contrasena" name="contrasena" type="password" required class="mt-1 block w-full rounded-md border-2 border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary)]" placeholder="●●●●●●">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rol</label>
                        <select name="rol" required class="mt-1 block w-full rounded-md border-2 border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary)]">
                            <option value="" <?= $rol_val==="" ? 'selected' : '' ?>>Selecciona tu rol</option>
                            <option value="1" <?= $rol_val==="1" ? 'selected' : '' ?>>Administrador</option>
                            <option value="2" <?= $rol_val==="2" ? 'selected' : '' ?>>Empleado</option>
                            <option value="3" <?= $rol_val==="3" ? 'selected' : '' ?>>Cliente</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between">
                       <a href="../views/registro.php" class="text-sm text-[var(--primary)] hover:underline">Crear cuenta</a>
                        <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-[var(--primary)] text-white rounded-md shadow-sm hover:brightness-95">Ingresar</button>
                    </div>
                </form>

                <p class="mt-6 text-xs text-gray-400">Consejo: para producción, utiliza contraseñas hasheadas (password_hash) y HTTPS.</p>
            </div>
        </section>
    </main>

    <script>
        // Toggle mostrar/ocultar contraseña
        const toggle = document.getElementById('togglePwd');
        const pwd = document.getElementById('contrasena');
        toggle?.addEventListener('click', () => {
            if (pwd.type === 'password') { pwd.type = 'text'; toggle.textContent = 'Ocultar'; }
            else { pwd.type = 'password'; toggle.textContent = 'Mostrar'; }
        });
    </script>
</body>
</html>
