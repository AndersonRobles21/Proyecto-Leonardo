<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'conexion2.php';

    $nombre       = $_POST['nombre'] ?? '';
    $Edad         = $_POST['Edad'] ?? '';
    $Fecha        = $_POST['Fecha'] ?? '';
    $VIP          = $_POST['VIP'] ?? '';
    $Direccion    = $_POST['Direccion'] ?? '';
    $Telefono     = $_POST['Telefono'] ?? '';
    $id_provincia = $_POST['Provincia'] ?? '';

    if (empty($id_provincia)) {
        die("⚠️ Debes seleccionar una provincia válida antes de guardar.");
    }

    // Verificar si la provincia existe realmente
    $check = $conexion2->query("SELECT * FROM provincia WHERE id_provincia = '$id_provincia'");
    if ($check->num_rows == 0) {
        die("❌ La provincia seleccionada no existe en la base de datos.");
    }

    $consulta = $conexion2->query("
        INSERT INTO compania (nombre, Edad, Fecha, VIP, Direccion, Telefono, id_provincia)
        VALUES ('$nombre', '$Edad', '$Fecha', '$VIP', '$Direccion', '$Telefono', '$id_provincia')
    ");

    if ($consulta) {
        echo "✅ Registro guardado correctamente.";
    } else {
        echo "❌ Error al guardar: " . $conexion2->error;
    }

} else {
    echo "⚠️ No se enviaron datos por el formulario.";
}
?>
