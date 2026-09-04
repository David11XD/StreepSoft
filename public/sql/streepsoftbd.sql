-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-09-2026 a las 00:48:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `streepsoftbd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categorias` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categorias`, `nombre`) VALUES
(2, 'Sub-10'),
(3, 'Sub-13'),
(4, 'Sub-15'),
(5, 'Sub-17'),
(6, 'Sub-20'),
(1, 'Sub-6');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas`
--

CREATE TABLE `deudas` (
  `id_deudas` int(11) NOT NULL,
  `id_jugadores` int(11) NOT NULL,
  `matricula` decimal(10,2) DEFAULT 0.00,
  `mes` enum('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre') NOT NULL,
  `anio` year(4) NOT NULL,
  `totalidad` decimal(10,2) DEFAULT 0.00,
  `fecha_limite_pago` date NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `pago` enum('pagado','pendiente','mora') DEFAULT 'pendiente',
  `id_tipo_becas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deudas`
--

INSERT INTO `deudas` (`id_deudas`, `id_jugadores`, `matricula`, `mes`, `anio`, `totalidad`, `fecha_limite_pago`, `fecha_pago`, `pago`, `id_tipo_becas`) VALUES
(6, 10, 100000.00, 'Junio', '2026', 150000.00, '2026-06-30', NULL, 'mora', 1),
(7, 9, 90000.00, 'Junio', '2026', 80000.00, '2026-06-29', NULL, 'mora', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL,
  `id_jugadores` int(11) DEFAULT NULL,
  `documento` varchar(25) DEFAULT NULL,
  `id_tipo_documento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos`
--

INSERT INTO `documentos` (`id_documento`, `id_jugadores`, `documento`, `id_tipo_documento`) VALUES
(7, 10, '1023456789', 2),
(8, 9, '1034567890', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eps`
--

CREATE TABLE `eps` (
  `id_eps` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eps`
--

INSERT INTO `eps` (`id_eps`, `nombre`) VALUES
(1, 'Nueva EPS'),
(2, 'Sura EPS'),
(3, 'Sanitas EPS'),
(4, 'Compensar EPS'),
(5, 'Famisanar EPS'),
(6, 'Salud Total EPS'),
(7, 'Sanidad Militar EPS'),
(8, 'Capital Salud EPS'),
(9, 'EPS SOS'),
(10, 'ServiSalud EPS'),
(11, 'Policía Nacional EPS'),
(12, 'Asmet Salud EPS'),
(13, 'Cajacopi EPS'),
(14, 'Aliansalud EPS'),
(15, 'Mallamas EPS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructor`
--

CREATE TABLE `instructor` (
  `id_instructor` int(11) NOT NULL,
  `nombres` varchar(20) DEFAULT NULL,
  `apellidos` varchar(20) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `numero_instructor` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instructor`
--

INSERT INTO `instructor` (`id_instructor`, `nombres`, `apellidos`, `edad`, `numero_instructor`) VALUES
(1, 'Saenz', '', 20, '+57 300000'),
(2, 'Julian', '', 20, '+57 300000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadores`
--

CREATE TABLE `jugadores` (
  `id_jugadores` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `primer_apellido` varchar(20) NOT NULL,
  `segundo_apellido` varchar(20) DEFAULT NULL,
  `primer_nombre` varchar(20) NOT NULL,
  `segundo_nombre` varchar(20) DEFAULT NULL,
  `tipo_de_documento` enum('R.C','T.I','C.C','C.E') NOT NULL,
  `identificacion` varchar(10) NOT NULL,
  `iniciales` varchar(3) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `edad` int(11) DEFAULT NULL,
  `sexo` enum('Masculino','Femenino') DEFAULT NULL,
  `eps` varchar(35) DEFAULT NULL,
  `instructor` varchar(80) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `talla_camiseta` varchar(5) DEFAULT NULL,
  `numero_camiseta` int(11) DEFAULT NULL,
  `talla_pantaloneta` varchar(5) DEFAULT NULL,
  `talla_media` varchar(5) DEFAULT NULL,
  `acudiente` varchar(100) NOT NULL,
  `tipo` enum('R.C','T.I','C.C','C.E') DEFAULT NULL,
  `identificacion_acudiente` varchar(10) DEFAULT NULL,
  `numero_acudiente` varchar(10) DEFAULT NULL,
  `id_categorias` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_eps` int(11) NOT NULL,
  `id_instructor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id_jugadores`, `foto`, `primer_apellido`, `segundo_apellido`, `primer_nombre`, `segundo_nombre`, `tipo_de_documento`, `identificacion`, `iniciales`, `fecha_nacimiento`, `edad`, `sexo`, `eps`, `instructor`, `categoria`, `talla_camiseta`, `numero_camiseta`, `talla_pantaloneta`, `talla_media`, `acudiente`, `tipo`, `identificacion_acudiente`, `numero_acudiente`, `id_categorias`, `created_at`, `id_eps`, `id_instructor`) VALUES
(9, 'juan.jpg', '', NULL, '', NULL, 'R.C', '', 'JDP', '2010-05-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Carlos Pérez', NULL, NULL, '3001234567', 1, '2026-06-22 04:51:13', 2, 1),
(10, 'maria.jpg', '', NULL, '', NULL, 'R.C', '', 'MFL', '2011-08-20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ana Ruiz', NULL, NULL, '3119876543', 1, '2026-06-22 04:51:13', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `id_metodo_pago` int(11) NOT NULL,
  `tipo_metodo_pago` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`id_metodo_pago`, `tipo_metodo_pago`) VALUES
(1, 'Nequi'),
(2, 'Transferencia'),
(3, 'Efectivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responsables`
--

CREATE TABLE `responsables` (
  `id_responsable` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `id_tipo_documento` int(11) NOT NULL,
  `identificacion` varchar(25) NOT NULL,
  `numero_celular` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `responsables`
--

INSERT INTO `responsables` (`id_responsable`, `nombres`, `apellidos`, `id_tipo_documento`, `identificacion`, `numero_celular`) VALUES
(1, 'Carlos', 'Pérez', 3, '', '3001234567'),
(2, 'Ana', 'Ruiz', 3, '', '3119876543'),
(3, 'Lopez', 'Lopez', 3, '', '300024904'),
(4, 'julian', 'julian', 3, '', '312227789'),
(5, 'Lopez', 'Lopez', 3, '', '3216733893'),
(6, 'Andres', 'Andres', 3, '', '320789222334'),
(7, 'cristian', 'cristian', 3, '', '3146649074'),
(8, 'sdsdsdsds', 'sdsdsdsds', 3, '', 'dsdsdsds');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_beca`
--

CREATE TABLE `tipos_beca` (
  `id_tipo_beca` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descuento` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_beca`
--

INSERT INTO `tipos_beca` (`id_tipo_beca`, `nombre`, `descuento`) VALUES
(1, 'Sin beca', 0.00),
(2, 'Media beca', 50.00),
(3, 'Beca completa', 100.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documento`
--

CREATE TABLE `tipo_documento` (
  `id_tipo_documento` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_documento`
--

INSERT INTO `tipo_documento` (`id_tipo_documento`, `nombre`) VALUES
(1, 'R.C'),
(2, 'T.I'),
(3, 'C.C'),
(4, 'C.E');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `pin_recuperacion` varchar(255) DEFAULT NULL,
  `token_password` varchar(255) DEFAULT NULL,
  `expired_session` datetime DEFAULT NULL,
  `request_password` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `usuario`, `contrasena`, `creado_en`, `pin_recuperacion`, `token_password`, `expired_session`, `request_password`) VALUES
(1, 'David mora', 'davi1@gmail.com', '$2y$10$aFuRwi7s9XI9m0nDGWM92OEqSXninYWWIg5RexHdyVtd4EpFbX5DW', '0000-00-00 00:00:00', '$2y$10$mszLjkxG3FaN1OuvMayYSO0i4YQSFLFqQQtEx6q7AeKFI97DsjbYS', 'e2b15e21a6f6c04d8a3fbf2a9c0ab3ccda0289c837a63c3ad49428cf41c30664', '2026-05-28 05:30:53', 0),
(2, 'Noni', 'Noni@gmail.com', '$2y$10$aFuRwi7s9XI9m0nDGWM92OEqSXninYWWIg5RexHdyVtd4EpFbX5DW', '2026-05-17 23:20:48', NULL, NULL, NULL, 0),
(5, 'cristian', 'cdavidg4396@gmail.com', '$2y$10$kW.AWrCQUAUATwRnvuZCMOUkeRlkfsQjn888H7ST8.C/fwwgqjEsC', '2026-05-26 15:54:10', '$2y$10$YkxmZnOtfrXl5n4V0Wfa1OM5qiPd3Wjk9TogPpn7K9GVN0IXxqcRK', '114f64ee71806087ffa8460b01d527d797eb74dddaf61a30b3ee7c20f84259ca', '2026-07-20 08:03:57', 0),
(6, 'JuanS', 'jsebastian1315@gmail.com', '$2y$10$StsqBLHimiWOmybVBZxeHuw647eiOScW4jTFzNclez3hKUbOkR.0W', '2026-05-26 23:19:13', '$2y$10$aTDDQCYkr.HaM/jb/0VdOe6F9NgZfaoB4MY28rLKkJ9ZwRFULX63K', '9bba13f36a47a4570ea8ef80d5919ecbf8862a215f9dc4448ddd541c067fd4af', '2026-07-20 07:58:29', 0),
(7, 'David Angel', 'davidangel11222@gmail.com', '$2y$10$rIIaAD07wWtThQHaLq6M3uaZaC4W1fKCrjGaNuw/1Pbn9VdVjItcm', '2026-07-23 03:58:21', NULL, NULL, NULL, 0),
(8, 'davidprueba', 'davidsllvv12@gmail.com', '$2y$10$TGlEt7VFp6MknL13h62PkOJnQzAVKSre2xsrsJ5ym5GUJpWLmmnuy', '2026-07-24 02:28:20', '$2y$10$tEbhBGxv23Q1eOUYh7LOseEeEiEBCw6yil5VhdY/G/dQ6ucxNTsSy', '8e763fea5b0d72d88f635422527d9b1704870e99e6da73434ef966d09760e5bf', '2026-07-24 04:36:13', 0);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_jugadores`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_jugadores` (
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_jugadores`
--
DROP TABLE IF EXISTS `vista_jugadores`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_jugadores`  AS SELECT `j`.`id_jugadores` AS `id_jugadores`, `j`.`foto` AS `foto`, `j`.`apellidos` AS `apellidos`, `j`.`nombres` AS `nombres`, `dc`.`documento` AS `documentos`, `j`.`fecha_nacimiento` AS `fecha_nacimiento`, timestampdiff(YEAR,`j`.`fecha_nacimiento`,curdate()) AS `edad`, `c`.`nombre` AS `categoria`, `tb`.`nombre` AS `tipo_beca`, `i`.`nombres` AS `instructor`, CASE WHEN `d`.`pago` = 'mora' THEN 'Inactivo' ELSE 'Activo' END AS `estado`, `d`.`fecha_pago` AS `fecha_pago`, `d`.`pago` AS `pago` FROM (((((`jugadores` `j` join `categorias` `c` on(`j`.`id_categorias` = `c`.`id_categorias`)) left join `deudas` `d` on(`d`.`id_jugadores` = `j`.`id_jugadores`)) left join `tipos_beca` `tb` on(`tb`.`id_tipo_beca` = `d`.`id_tipo_becas`)) left join `documentos` `dc` on(`dc`.`id_jugadores` = `j`.`id_jugadores`)) left join `instructor` `i` on(`i`.`id_instructor` = `j`.`id_instructor`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categorias`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD PRIMARY KEY (`id_deudas`),
  ADD KEY `id_jugadores` (`id_jugadores`),
  ADD KEY `fk_deuda_beca` (`id_tipo_becas`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `fk_tipo_documento` (`id_tipo_documento`),
  ADD KEY `fk_jugadores_documentos` (`id_jugadores`);

--
-- Indices de la tabla `eps`
--
ALTER TABLE `eps`
  ADD PRIMARY KEY (`id_eps`);

--
-- Indices de la tabla `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`id_instructor`);

--
-- Indices de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD PRIMARY KEY (`id_jugadores`),
  ADD KEY `fk_jugador_categoria` (`id_categorias`),
  ADD KEY `idx_jugador_instructor` (`id_instructor`),
  ADD KEY `fk_jugadores_eps` (`id_eps`);

--
-- Indices de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`id_metodo_pago`);

--
-- Indices de la tabla `responsables`
--
ALTER TABLE `responsables`
  ADD PRIMARY KEY (`id_responsable`),
  ADD KEY `fk_responsable_tipo_documento` (`id_tipo_documento`);

--
-- Indices de la tabla `tipos_beca`
--
ALTER TABLE `tipos_beca`
  ADD PRIMARY KEY (`id_tipo_beca`);

--
-- Indices de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  ADD PRIMARY KEY (`id_tipo_documento`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categorias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `deudas`
--
ALTER TABLE `deudas`
  MODIFY `id_deudas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `eps`
--
ALTER TABLE `eps`
  MODIFY `id_eps` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `instructor`
--
ALTER TABLE `instructor`
  MODIFY `id_instructor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id_jugadores` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `tipos_beca`
--
ALTER TABLE `tipos_beca`
  MODIFY `id_tipo_beca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  MODIFY `id_tipo_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD CONSTRAINT `deudas_ibfk_1` FOREIGN KEY (`id_jugadores`) REFERENCES `jugadores` (`id_jugadores`),
  ADD CONSTRAINT `fk_deuda_beca` FOREIGN KEY (`id_tipo_becas`) REFERENCES `tipos_beca` (`id_tipo_beca`);

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `fk_jugadores_documentos` FOREIGN KEY (`id_jugadores`) REFERENCES `jugadores` (`id_jugadores`),
  ADD CONSTRAINT `fk_tipo_documento` FOREIGN KEY (`id_tipo_documento`) REFERENCES `tipo_documento` (`id_tipo_documento`);

--
-- Filtros para la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `fk_jugador_categoria` FOREIGN KEY (`id_categorias`) REFERENCES `categorias` (`id_categorias`),
  ADD CONSTRAINT `fk_jugadores_eps` FOREIGN KEY (`id_eps`) REFERENCES `eps` (`id_eps`),
  ADD CONSTRAINT `fk_jugadores_instructores` FOREIGN KEY (`id_instructor`) REFERENCES `instructor` (`id_instructor`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
