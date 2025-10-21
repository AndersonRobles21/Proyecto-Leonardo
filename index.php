<?php
session_start();
include("conexion.php");

// Si ya hay sesión activa, redirigir
if (isset($_SESSION['rol'])) {
    switch ($_SESSION['rol']) {
        case 1: header("Location: admin_panel.php"); exit();
        case 2: header("Location: empleado_panel.php"); exit();
        case 3: header("Location: cliente_dashboard.php"); exit();
    }
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];

    $sql = "SELECT * FROM usuarios WHERE correo='$correo' AND id_rol='$rol'";
    $resultado = mysqli_query($conn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);

        // Aquí puedes usar password_verify() si usas hash
        if ($usuario['contrasena'] == $contrasena) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['id_rol'];

            switch ($usuario['id_rol']) {
                case 1: header("Location: admin_panel.php"); exit();
                case 2: header("Location: empleado_panel.php"); exit();
                case 3: header("Location: cliente_dashboard.php"); exit();
            }
        } else {
            $mensaje = "❌ Contraseña incorrecta.";
        }
    } else {
        $mensaje = "⚠️ Usuario o rol no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #009688, #1a73e8);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            width: 350px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        h2 { text-align: center; color: #1a73e8; }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #1a73e8;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover { background: #0d5ec2; }
        .mensaje { color: red; text-align: center; margin-top: 10px; }
        .registro { text-align: center; margin-top: 15px; }
        a { color: #1a73e8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Iniciar Sesión</h2>
        <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>

        <form method="POST" action="">
            <input type="email" name="correo" placeholder="Correo" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>

            <select name="rol" required>
                <option value="">Selecciona tu rol</option>
                <option value="1">Administrador</option>
                <option value="2">Empleado</option>
                <option value="3">Cliente</option>
            </select>

            <button type="submit">Ingresar</button>
        </form>

        <p class="registro">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>
</body>
</html>
