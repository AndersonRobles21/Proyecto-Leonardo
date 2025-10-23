<?php
session_start();

// Destruir la sesión actual
session_unset();
session_destroy();

// Redirigir al login (index dentro de views)
header("Location: ../views/index.php");
exit();
?>
