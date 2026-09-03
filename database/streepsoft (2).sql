-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-08-2026 a las 15:25:58
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
-- Base de datos: `streepsoft`
--

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
  `id_metodo_pago` int(11) DEFAULT NULL,
  `concepto` varchar(100) DEFAULT NULL,
  `descuento_porcentaje` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `valor_pagado` decimal(10,2) DEFAULT NULL,
  `pago` enum('pagado','pendiente','mora') DEFAULT 'pendiente',
  `id_tipo_becas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deudas`
--

INSERT INTO `deudas` (`id_deudas`, `id_jugadores`, `matricula`, `mes`, `anio`, `totalidad`, `fecha_limite_pago`, `fecha_pago`, `id_metodo_pago`, `concepto`, `descuento_porcentaje`, `valor_pagado`, `pago`, `id_tipo_becas`) VALUES
(6, 10, 100000.00, 'Junio', '2026', 150000.00, '2026-06-30', '2026-08-03', 1, NULL, 20, 119998.40, 'pagado', 1),
(7, 9, 90000.00, 'Junio', '2026', 80000.00, '2026-06-29', '2026-08-24', 1, NULL, 0, 80000.00, 'pagado', 2);

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
(8, 9, '1034567890', 2),
(14, 16, '10011201234', 2),
(15, 17, '2034668904', 3),
(16, 18, '3450039932', 2),
(17, 19, '456779955', 3);

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
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `iniciales` varchar(10) DEFAULT NULL,
  `acudiente` varchar(100) NOT NULL,
  `numero_acudiente` varchar(20) NOT NULL,
  `id_categorias` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_eps` int(11) NOT NULL,
  `id_instructor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id_jugadores`, `foto`, `nombres`, `apellidos`, `fecha_nacimiento`, `iniciales`, `acudiente`, `numero_acudiente`, `id_categorias`, `created_at`, `id_eps`, `id_instructor`) VALUES
(9, 'juan.jpg', 'Juan David', 'Pérez Gómez', '2010-05-15', 'JDPG', 'Carlos Pérez', '3001234567', 1, '2026-06-22 04:51:13', 2, 1),
(10, 'maria.jpg', 'María Fernanda', 'López Ruiz', '2011-08-20', 'MFLR', 'Ana Ruiz', '3119876543', 1, '2026-06-22 04:51:13', 1, 1),
(16, NULL, 'JUAN andres', 'lopez Guzman', '2006-06-12', 'JAG', 'Lopez', '300024904', 6, '2026-08-12 19:03:53', 12, 2),
(17, NULL, 'JUAN andres', 'lopez Guzman', '2022-06-12', 'Mat', 'julian', '312227789', 4, '2026-08-12 21:43:43', 9, 1),
(18, NULL, 'Kevin', 'Bolaños', '2016-06-14', 'BKI', 'Lopez', '3216733893', 4, '2026-08-13 03:25:02', 4, 1),
(19, '05c2d8e0b6f3ccd3df9c500a844b45a8.jpg', 'Fabian', 'Garzon', '2013-10-15', 'GFN', 'Andres', '320789222334', 5, '2026-08-13 03:31:47', 8, 2);

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
  `documento_identidad` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
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

INSERT INTO `usuarios` (`id`, `nombre_completo`, `usuario`, `documento_identidad`, `telefono`, `foto`, `contrasena`, `creado_en`, `pin_recuperacion`, `token_password`, `expired_session`, `request_password`) VALUES
(1, 'David mora', 'davi1@gmail.com', NULL, NULL, NULL, '$2y$10$aFuRwi7s9XI9m0nDGWM92OEqSXninYWWIg5RexHdyVtd4EpFbX5DW', '0000-00-00 00:00:00', '$2y$10$uVpBPJnHhM96bhIH2fzFS.Cy3OTnWOlvJPpTaoM3CX1W0TURplSfW', '4f5a39adaecee0ee98e902f88bdb268e7a2675607282b61211893a968bc564aa', '2026-08-04 05:40:14', 0),
(2, 'Noni', 'Noni@gmail.com', NULL, NULL, NULL, '$2y$10$aFuRwi7s9XI9m0nDGWM92OEqSXninYWWIg5RexHdyVtd4EpFbX5DW', '2026-05-17 23:20:48', NULL, NULL, NULL, 0),
(5, 'cristian', 'cdavidg4396@gmail.com', NULL, NULL, NULL, '$2y$10$kW.AWrCQUAUATwRnvuZCMOUkeRlkfsQjn888H7ST8.C/fwwgqjEsC', '2026-05-26 15:54:10', '$2y$10$cVao9ENsVCxaxsq3uJal2OvLzPsbyifp3xMEJWt6PSzafmx/7Qg1W', 'a34d7fa3c5010fc0dc1b017b93a13e280665301406f0122bb372c4367764d247', '2026-08-13 00:04:45', 0),
(6, 'JuanS', 'jsebastian1315@gmail.com', NULL, NULL, NULL, '$2y$10$StsqBLHimiWOmybVBZxeHuw647eiOScW4jTFzNclez3hKUbOkR.0W', '2026-05-26 23:19:13', '$2y$10$aTDDQCYkr.HaM/jb/0VdOe6F9NgZfaoB4MY28rLKkJ9ZwRFULX63K', '9bba13f36a47a4570ea8ef80d5919ecbf8862a215f9dc4448ddd541c067fd4af', '2026-07-20 07:58:29', 0),
(7, 'David Angel', 'davidangel11222@gmail.com', NULL, NULL, NULL, '$2y$10$rIIaAD07wWtThQHaLq6M3uaZaC4W1fKCrjGaNuw/1Pbn9VdVjItcm', '2026-07-23 03:58:21', NULL, NULL, NULL, 0),
(8, 'Eliana Cortes', 'karitolcortes2008@gmail.com', '1071839153', '3222870087', NULL, '$2y$10$rM/.zqtyimwOD5G6MqKPz.X9522YRsaaDFjFzn34sNwFDvtn5CWXK', '2026-08-25 04:15:26', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_jugadores`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_jugadores` (
`id_jugadores` int(11)
,`foto` varchar(255)
,`apellidos` varchar(100)
,`nombres` varchar(100)
,`iniciales` varchar(10)
,`acudiente` varchar(100)
,`numero_acudiente` varchar(20)
,`documentos` varchar(25)
,`tipo_documento` varchar(100)
,`fecha_nacimiento` date
,`edad` bigint(21)
,`categoria` varchar(30)
,`tipo_beca` varchar(100)
,`instructor` varchar(20)
,`estado` varchar(8)
,`fecha_pago` date
,`fecha_limite_pago` date
,`pago` enum('pagado','pendiente','mora')
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_jugadores`
--
DROP TABLE IF EXISTS `vista_jugadores`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_jugadores`  AS SELECT `j`.`id_jugadores` AS `id_jugadores`, `j`.`foto` AS `foto`, `j`.`apellidos` AS `apellidos`, `j`.`nombres` AS `nombres`, `j`.`iniciales` AS `iniciales`, `j`.`acudiente` AS `acudiente`, `j`.`numero_acudiente` AS `numero_acudiente`, `dc`.`documento` AS `documentos`, `td`.`nombre` AS `tipo_documento`, `j`.`fecha_nacimiento` AS `fecha_nacimiento`, timestampdiff(YEAR,`j`.`fecha_nacimiento`,curdate()) AS `edad`, `c`.`nombre` AS `categoria`, `tb`.`nombre` AS `tipo_beca`, `i`.`nombres` AS `instructor`, CASE WHEN `d`.`pago` = 'mora' THEN 'Inactivo' ELSE 'Activo' END AS `estado`, `d`.`fecha_pago` AS `fecha_pago`, `d`.`fecha_limite_pago` AS `fecha_limite_pago`, `d`.`pago` AS `pago` FROM ((((((`jugadores` `j` join `categorias` `c` on(`j`.`id_categorias` = `c`.`id_categorias`)) left join `deudas` `d` on(`d`.`id_jugadores` = `j`.`id_jugadores`)) left join `tipos_beca` `tb` on(`tb`.`id_tipo_beca` = `d`.`id_tipo_becas`)) left join `documentos` `dc` on(`dc`.`id_jugadores` = `j`.`id_jugadores`)) left join `tipo_documento` `td` on(`td`.`id_tipo_documento` = `dc`.`id_tipo_documento`)) left join `instructor` `i` on(`i`.`id_instructor` = `j`.`id_instructor`)) ;

--
-- Índices para tablas volcadas
--

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
  ADD KEY `fk_deuda_beca` (`id_tipo_becas`),
  ADD KEY `fk_deudas_metodo_pago` (`id_metodo_pago`);

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
  ADD CONSTRAINT `fk_deuda_beca` FOREIGN KEY (`id_tipo_becas`) REFERENCES `tipos_beca` (`id_tipo_beca`),
  ADD CONSTRAINT `fk_deudas_metodo_pago` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodo_pago` (`id_metodo_pago`);

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
