-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-08-2025 a las 18:29:12
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `form`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `nombres_estudiante` varchar(255) NOT NULL,
  `nivel_aspira` text DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `mensualidad` decimal(10,2) DEFAULT NULL,
  `estrato_socioeconomico` int(11) DEFAULT NULL,
  `eps` varchar(100) DEFAULT NULL,
  `nivel_escolaridad` varchar(100) DEFAULT NULL,
  `doc_type` varchar(10) DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `municipio_residencia` varchar(100) DEFAULT NULL,
  `direccion_residencia` varchar(255) DEFAULT NULL,
  `celular1` varchar(50) DEFAULT NULL,
  `celular2` varchar(50) DEFAULT NULL,
  `barrio` varchar(100) DEFAULT NULL,
  `nombre_acudiente` varchar(255) DEFAULT NULL,
  `contacto_acudiente` varchar(100) DEFAULT NULL,
  `empresa_acudiente` varchar(150) DEFAULT NULL,
  `cargo_acudiente` varchar(100) DEFAULT NULL,
  `contacto_empresa_acudiente` varchar(100) DEFAULT NULL,
  `mensaje_bienvenida` text DEFAULT NULL,
  `reg_type` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `foto`, `nombres_estudiante`, `nivel_aspira`, `horario`, `mensualidad`, `estrato_socioeconomico`, `eps`, `nivel_escolaridad`, `doc_type`, `numero_documento`, `email`, `municipio_residencia`, `direccion_residencia`, `celular1`, `celular2`, `barrio`, `nombre_acudiente`, `contacto_acudiente`, `empresa_acudiente`, `cargo_acudiente`, `contacto_empresa_acudiente`, `mensaje_bienvenida`, `reg_type`, `created_at`) VALUES
(1, 'uploads/1754147279_Captura de pantalla 2025-02-05 112431.png', 'santiago arango', 'A1,A2,B1,B2', '3 a 4', 222.00, 5, 'sura', 'Secundaria', 'PPT', '1033455667', 'jose1243@gmail.com', 'bello', 'Stallsf 43 1212', '23232332', '33232323', 'bellavista', 'Sebastian Alejandro', '3104432794', 'Compuestudio', 'soporte', '1212212', 'dsfdfdfdfdffddf', 'new', '2025-08-02 15:07:59'),
(2, 'uploads/1754151179_Captura de pantalla 2025-02-05 112431.png', 'santiago arango', 'B1,B2', '3 a 4', 22222.00, 2, 'sura', 'Primaria', 'CC', '1033455667', 'jose1243@gmail.com', 'bello', 'Stallsf 43 1212', '23232332', '33232323', 'bellavista', 'Jose Alejandro', '3104432794', 'Compuestudio', 'soporte', '32332332', 'dsfdfdfdfdffddf', 'new', '2025-08-02 16:12:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `contrasena`, `rol`, `created_at`) VALUES
(1, 'admin', '$2y$10$VUwSHJPXFaDAY0cNkkWvrOWT2zEvOj6ratsKULGpnwRKxw5uLYtK2', 'admin', '2025-08-01 15:59:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
