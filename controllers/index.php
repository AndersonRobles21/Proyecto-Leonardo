<?php
// controllers/index.php
session_start();

if (isset($_SESSION['rol'])) {
    switch ($_SESSION['rol']) {
        case 1:
            header("Location: ../views/admin_dashboard.php");
            break;
        case 2:
            header("Location: ../views/empleado_dashboard.php");
            break;
        case 3:
            header("Location: ../views/cliente_dashboard.php");
            break;
        default:
            session_destroy();
            header("Location: ../views/login.php");
            break;
    }
} else {
    header("Location: ../views/login.php");
}
exit();
