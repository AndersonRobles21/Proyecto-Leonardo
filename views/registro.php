<?php
include("../models/conexion.php");
session_start();

$mensaje = "";
$nombre_val = "";
$correo_val = "";
$rol_val = "";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? '';

    $nombre_val = htmlspecialchars($nombre, ENT_QUOTES);
    $correo_val = htmlspecialchars($correo, ENT_QUOTES);
    $rol_val = htmlspecialchars($rol, ENT_QUOTES);

    if ($nombre === '' || $correo === '' || $contrasena === '' || $rol === '') {
        $mensaje = "⚠️ Completa todos los campos.";
    } else {
        // Verificar si el correo ya existe
        $stmt = mysqli_prepare($conn, "SELECT id_usuario FROM usuarios WHERE correo = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $correo);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && mysqli_num_rows($res) > 0) {
                $mensaje = "⚠️ Ese correo ya está registrado.";
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);
                // Insertar usuario con password_hash
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $ins = mysqli_prepare($conn, "INSERT INTO usuarios (nombre, correo, contrasena, id_rol) VALUES (?, ?, ?, ?)");
                if ($ins) {
                    mysqli_stmt_bind_param($ins, 'sssi', $nombre, $correo, $hash, $rol);
                    if (mysqli_stmt_execute($ins)) {
                        $mensaje = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
                        // Limpiar valores
                        $nombre_val = $correo_val = $rol_val = '';
                    } else {
                        $mensaje = "❌ Error al registrar: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($ins);
                } else {
                    $mensaje = "❌ Error en la consulta de inserción.";
                }
            }
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
    <title>Registro - Gestión de Viajes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>:root{--accent:#009688;--primary:#1a73e8}</style>
</head>
<body class="antialiased bg-gradient-to-br from-[var(--primary)] to-[var(--accent)] min-h-screen flex items-center justify-center p-4">
    <main class="max-w-4xl w-full bg-white rounded-xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <section class="hidden md:flex flex-col items-center justify-center bg-gradient-to-b from-[var(--accent)]/90 to-[var(--accent)]/70 text-white p-8">
            <div class="text-5xl mb-4">🧾</div>
            <h1 class="text-2xl font-extrabold mb-2">Crear cuenta</h1>
            <p class="text-sm/relaxed max-w-xs text-white/90">Regístrate para gestionar viajes y reservas desde el panel.</p>
        </section>

        <section class="p-8">
            <div class="max-w-md mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">📝 Crear Cuenta</h2>
                <p class="text-sm text-gray-500 mb-6">Rellena el formulario para crear tu cuenta.</p>

                <?php if ($mensaje): ?>
                    <div class="mb-4 p-3 rounded-md text-sm <?php echo strpos($mensaje,'❌')!==false ? 'bg-red-50 text-red-700 border border-red-100' : (strpos($mensaje,'✅')!==false ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-yellow-50 text-yellow-800 border border-yellow-100'); ?>">
                        <?= htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
                        <input name="nombre" type="text" required value="<?= $nombre_val; ?>" class="mt-1 block w-full rounded-md border-2 border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--accent)]" placeholder="Tu nombre">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Correo</label>
                        <input name="correo" type="email" required value="<?= $correo_val; ?>" class="mt-1 block w-full rounded-md border-2 border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--accent)]" placeholder="tucorreo@ejemplo.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input name="contrasena" type="password" required class="mt-1 block w-full rounded-md border-2 border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--accent)]" placeholder="●●●●●●">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rol</label>
                        <select name="rol" required class="mt-1 block w-full rounded-md border-2 border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--accent)]">
                            <option value="" <?= $rol_val==="" ? 'selected' : '' ?>>Selecciona tu rol</option>
                            <option value="1" <?= $rol_val==="1" ? 'selected' : '' ?>>Administrador</option>
                            <option value="2" <?= $rol_val==="2" ? 'selected' : '' ?>>Empleado</option>
                            <option value="3" <?= $rol_val==="3" ? 'selected' : '' ?>>Cliente</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="index.php" class="text-sm text-[var(--accent)] hover:underline">¿Ya tienes cuenta? Inicia sesión</a>
                        <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-[var(--accent)] text-white rounded-md shadow-sm hover:brightness-95">Registrarse</button>
                    </div>
                </form>

                <p class="mt-6 text-xs text-gray-400">Consejo: tu contraseña se guardará de forma segura usando hashing.</p>
            </div>
        </section>
    </main>
</body>
</html>
