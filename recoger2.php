<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: recoger2.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol']; // admin, empleado o cliente
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de usuario</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
  <div class="bg-white shadow-lg rounded-2xl p-8 w-[90%] max-w-lg text-center">
    <h1 class="text-2xl font-bold text-gray-700 mb-4">¡Bienvenido, <?= htmlspecialchars($usuario) ?>!</h1>
    <p class="text-gray-600 mb-6">Has iniciado sesión como <span class="font-semibold text-blue-600"><?= ucfirst($rol) ?></span></p>

    <?php if ($rol == 'administrador'): ?>
      <div class="space-y-3">
        <a href="usuarios.php" class="block bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg">Gestionar Usuarios</a>
        <a href="viajes.php" class="block bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg">Gestionar Viajes</a>
        <a href="reservas.php" class="block bg-purple-500 hover:bg-purple-600 text-white py-2 rounded-lg">Ver Reservas</a>
      </div>

    <?php elseif ($rol == 'empleado'): ?>
      <div class="space-y-3">
        <a href="reservas.php" class="block bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg">Consultar / Modificar Reservas</a>
        <a href="disponibilidad.php" class="block bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg">Ver Disponibilidad</a>
      </div>

    <?php elseif ($rol == 'cliente'): ?>
      <div class="space-y-3">
        <a href="nueva_reserva.php" class="block bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg">Hacer una Reservación</a>
        <a href="mis_reservas.php" class="block bg-purple-500 hover:bg-purple-600 text-white py-2 rounded-lg">Ver mis Reservas</a>
      </div>
    <?php endif; ?>

    <div class="mt-8">
      <a href="logout.php" class="text-red-500 hover:underline">Cerrar sesión</a>
    </div>
  </div>
</body>
</html>
