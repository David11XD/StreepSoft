-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-09-2026 a las 01:39:45
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
(9, 24, 90000.00, 'Septiembre', '2026', 80000.00, '2026-09-01', '2026-09-01', 3, 'Matrícula y mensualidad de inscripción', 0, 170000.00, 'pagado', 1);

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
(21, 24, '1056789922', 3);

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
  `numero_instructor` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instructor`
--

INSERT INTO `instructor` (`id_instructor`, `nombres`, `apellidos`, `edad`, `numero_instructor`) VALUES
(1, 'Crisitan Ivan ', 'Saenz', 20, '+57 3000000000'),
(2, 'Julian David', 'Munevar', 20, '+57 3000000000'),
(3, 'Estaban', 'Moreno Rojas', 25, '+57 3000000000'),
(4, 'Luis Camilo', 'Beltran', 25, '+57 3000000000');

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
  `id_responsable` int(11) DEFAULT NULL,
  `id_categorias` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_eps` int(11) NOT NULL,
  `id_instructor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id_jugadores`, `foto`, `nombres`, `apellidos`, `fecha_nacimiento`, `iniciales`, `acudiente`, `numero_acudiente`, `id_responsable`, `id_categorias`, `created_at`, `id_eps`, `id_instructor`) VALUES
(24, '76bc64a6e1e58ada2870899b4fd6e021.jpg', 'Kevin', 'Martinez', '2007-06-20', 'MCK', 'Juan', '3100293045', NULL, 3, '2026-09-01 22:09:33', 3, 2);

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
(5, 'cristian', 'cdavidg4396@gmail.com', '1099922277', '3146649074', NULL, '$2y$10$kW.AWrCQUAUATwRnvuZCMOUkeRlkfsQjn888H7ST8.C/fwwgqjEsC', '2026-05-26 15:54:10', '$2y$10$N8M44q.KCAvOq.2uIVuKqunMGmPevTVeAkJSXSQIp7f32waVY38jW', 'ef384ea181bba152d426ea67e005965f169356701d8846fa226e960058846403', '2026-08-21 00:10:29', 0),
(6, 'JuanS', 'jsebastian1315@gmail.com', NULL, NULL, NULL, '$2y$10$StsqBLHimiWOmybVBZxeHuw647eiOScW4jTFzNclez3hKUbOkR.0W', '2026-05-26 23:19:13', '$2y$10$aTDDQCYkr.HaM/jb/0VdOe6F9NgZfaoB4MY28rLKkJ9ZwRFULX63K', '9bba13f36a47a4570ea8ef80d5919ecbf8862a215f9dc4448ddd541c067fd4af', '2026-07-20 07:58:29', 0),
(7, 'David Angel', 'davidangel11222@gmail.com', NULL, NULL, NULL, '$2y$10$rIIaAD07wWtThQHaLq6M3uaZaC4W1fKCrjGaNuw/1Pbn9VdVjItcm', '2026-07-23 03:58:21', NULL, NULL, NULL, 0);

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
  ADD KEY `fk_jugadores_eps` (`id_eps`),
  ADD KEY `fk_jugadores_responsable` (`id_responsable`);

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
  MODIFY `id_deudas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `eps`
--
ALTER TABLE `eps`
  MODIFY `id_eps` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `instructor`
--
ALTER TABLE `instructor`
  MODIFY `id_instructor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id_jugadores` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `id_metodo_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `responsables`
--
ALTER TABLE `responsables`
  MODIFY `id_responsable` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD CONSTRAINT `actividad_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

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
  ADD CONSTRAINT `fk_jugadores_instructores` FOREIGN KEY (`id_instructor`) REFERENCES `instructor` (`id_instructor`),
  ADD CONSTRAINT `fk_jugadores_responsable` FOREIGN KEY (`id_responsable`) REFERENCES `responsables` (`id_responsable`);

--
-- Filtros para la tabla `responsables`
--
ALTER TABLE `responsables`
  ADD CONSTRAINT `fk_responsable_tipo_documento` FOREIGN KEY (`id_tipo_documento`) REFERENCES `tipo_documento` (`id_tipo_documento`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
