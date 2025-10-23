# Happy Flye - Sistema de Gestión de Viajes
Descripción

Happy Flye es una aplicación web para la gestión de viajes y reservas. Permite a los usuarios registrarse, iniciar sesión y reservar viajes, y a los administradores gestionar usuarios, reservas y viajes.
El sistema soporta tres tipos de roles:
Administrador (rol 1): controla usuarios, viajes y reservas.
Empleado (rol 2): funciones limitadas de gestión.
Cliente (rol 3): puede ver viajes disponibles y gestionar sus reservas.
El proyecto está desarrollado con PHP, MySQL y Tailwind CSS, incluyendo seguridad básica, protección CSRF y compatibilidad con modo oscuro.

# Funcionalidades
Administrador
Ver lista de usuarios y reservas.
Agregar, eliminar y actualizar viajes.
Cambiar el estado de reservas (pendiente, confirmada, cancelada).
Evitar eliminar al propio administrador.
Protección contra CSRF en todas las acciones.
Cliente
Ver viajes disponibles.
Crear reservas indicando la cantidad de personas.
Visualizar estado de sus reservas.
Interfaz moderna con modo oscuro y fondo con imagen transparente.
Seguridad
Uso de sessions para autenticación.
Prepared statements (mysqli_prepare) para evitar inyección SQL.
Protección CSRF mediante tokens en formularios de administrador.
Contraseñas almacenadas en hash (password_hash) y verificación con password_verify.
UI y Estilos
Tailwind CSS para diseño responsivo y moderno.
Gradientes de fondo combinados con imagen transparente (::before).
pointer-events: none en la pseudo-capa ::before para que la imagen no bloquee botones ni formularios.
# Requisitos Previos

Servidor web con PHP
XAMPP, WAMP, MAMP, Laragon, etc.
PHP >= 8.x recomendado.
Servidor de base de datos XAMMP o LARAGON
MySQL o MariaDB.
Navegador moderno
Chrome, Firefox, Edge, Safari, etc.
Editor de código recomendado
VS Code, PhpStorm, Sublime Text.
Soporte para modo oscuro.
Formularios y tablas con bordes redondeados, sombras y colores accesibles.

# Tecnologías Usadas
PHP 8.x
MySQL / MariaDB
Tailwind CSS 3.x
JavaScript (interactividad y animaciones)

# Paleta de colores 
En administrador
#0d9488
#0f766e;
