<?php
include("conexion.php");
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];

    // Evitar duplicados
    $verificar = mysqli_query($conn, "SELECT * FROM usuarios WHERE correo='$correo'");
    if (mysqli_num_rows($verificar) > 0) {
        $mensaje = "⚠️ Ese correo ya está registrado.";
    } else {
        $sql = "INSERT INTO usuarios (nombre, correo, contrasena, id_rol)
                VALUES ('$nombre', '$correo', '$contrasena', '$rol')";
        if (mysqli_query($conn, $sql)) {
            $mensaje = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
        } else {
            $mensaje = "❌ Error al registrar: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Gestión de Viajes</title>
    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #1a73e8, #009688);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        h2 { text-align: center; color: #009688; }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #009688;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover { background: #00796b; }
        .mensaje { text-align: center; color: green; margin-top: 10px; }
        a { color: #1a73e8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>📝 Crear Cuenta</h2>
        <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>

        <form method="POST" action="">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="correo" placeholder="Correo electrónico" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>

            <select name="rol" required>
                <option value="">Selecciona tu rol</option>
                <option value="1">Administrador</option>
                <option value="2">Empleado</option>
                <option value="3">Cliente</option>
            </select>

            <button type="submit">Registrarse</button>
        </form>

        <p style="text-align:center; margin-top:10px;">
            ¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a>
        </p>
    </div>
</body>
</html>
