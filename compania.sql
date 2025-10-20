-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2017 a las 00:07:34
-- Versión del servidor: 10.1.25-MariaDB
-- Versión de PHP: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `almacen`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compania`
--

CREATE TABLE `compania` (
  `nombre` varchar(75) COLLATE utf8_spanish_ci DEFAULT NULL,
  `Edad` int(100) DEFAULT NULL,
  `Fecha` date DEFAULT NULL,
  `VIP` text COLLATE utf8_spanish_ci,
  `Provincia` varchar(20) COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `compania`
--

INSERT INTO `compania` (`nombre`, `Edad`, `Fecha`, `VIP`, `Provincia`) VALUES
('', NULL, NULL, NULL, NULL),
('', NULL, NULL, NULL, NULL),
('', NULL, NULL, NULL, NULL),
('', NULL, NULL, NULL, NULL),
('', NULL, NULL, NULL, NULL),
('', NULL, NULL, NULL, NULL),
('', NULL, NULL, NULL, NULL),
('', NULL, NULL, NULL, NULL),
('', 0, '0000-00-00', '', ''),
('fabio guio', 10, '0000-00-00', 'si', 'Madrid'),
('', 0, '0000-00-00', '', ''),
('', 0, '0000-00-00', '', ''),
('', 0, '0000-00-00', '', ''),
('', 0, '0000-00-00', '', ''),
('marlen2', 23, '2017-10-17', 'si', 'Bilbao'),
('', 0, '0000-00-00', '', ''),
('alejo', 23, '2017-10-22', 'si', 'Madrid'),
('dani', 22, '1995-04-20', 'si', 'Madrid'),
('hugo', 22, '1995-10-20', 'si', 'Madrid'),
('hitler', 50, '1971-08-20', 'si', 'Barcelona'),
('jhony', 23, '0000-00-00', 'si', 'Barcelona'),
('marcela  ', 21, '1996-10-13', 'si', 'Sevilla');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
