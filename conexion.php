<?php
$servername = "localhost";
$username = "root"; // o tu usuario MySQL
$password = ""; // tu contraseña MySQL
$dbname = "gestion_viajes";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
