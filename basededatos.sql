-- Create database
CREATE DATABASE IF NOT EXISTS `comedor_comunitario` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `comedor_comunitario`;

-- Create tables
CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text,
  `edad` int DEFAULT NULL,
  `sexo` enum('masculino','femenino','otro') DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `tipo_usuario` enum('beneficiario','empleado','admin') NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1',
  `recordar_token` varchar(100) DEFAULT NULL,
  `token_recuperacion` varchar(100) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `secciones_menu` (
  `id_seccion` int NOT NULL AUTO_INCREMENT,
  `nombre_seccion` varchar(50) NOT NULL,
  `descripcion` text,
  `orden` int DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_seccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `platillos` (
  `id_platillo` int NOT NULL AUTO_INCREMENT,
  `id_seccion` int NOT NULL,
  `nombre_platillo` varchar(100) NOT NULL,
  `descripcion` text,
  `ingredientes` text,
  `es_vegetariano` tinyint(1) DEFAULT '0',
  `es_vegano` tinyint(1) DEFAULT '0',
  `contiene_gluten` tinyint(1) DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_platillo`),
  KEY `id_seccion` (`id_seccion`),
  CONSTRAINT `platillos_ibfk_1` FOREIGN KEY (`id_seccion`) REFERENCES `secciones_menu` (`id_seccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menus_dia` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `notas` text,
  `id_usuario_creador` int DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_menu`),
  UNIQUE KEY `fecha` (`fecha`),
  KEY `id_usuario_creador` (`id_usuario_creador`),
  CONSTRAINT `menus_dia_ibfk_1` FOREIGN KEY (`id_usuario_creador`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menu_platillos` (
  `id_menu_platillo` int NOT NULL AUTO_INCREMENT,
  `id_menu` int NOT NULL,
  `id_platillo` int NOT NULL,
  `disponible` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_menu_platillo`),
  UNIQUE KEY `id_menu` (`id_menu`,`id_platillo`),
  KEY `id_platillo` (`id_platillo`),
  CONSTRAINT `menu_platillos_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menus_dia` (`id_menu`),
  CONSTRAINT `menu_platillos_ibfk_2` FOREIGN KEY (`id_platillo`) REFERENCES `platillos` (`id_platillo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservaciones` (
  `id_reservacion` int NOT NULL AUTO_INCREMENT,
  `codigo_reservacion` varchar(20) NOT NULL,
  `id_usuario` int NOT NULL,
  `id_menu` int NOT NULL,
  `fecha_reservacion` date NOT NULL,
  `hora_recogida` time NOT NULL,
  `num_porciones` int NOT NULL DEFAULT '1',
  `estado` enum('pendiente','completada','cancelada') DEFAULT 'pendiente',
  `notas` text,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_usuario_atendio` int DEFAULT NULL,
  `fecha_atencion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_reservacion`),
  UNIQUE KEY `codigo_reservacion` (`codigo_reservacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_menu` (`id_menu`),
  KEY `id_usuario_atendio` (`id_usuario_atendio`),
  CONSTRAINT `reservaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `reservaciones_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menus_dia` (`id_menu`),
  CONSTRAINT `reservaciones_ibfk_3` FOREIGN KEY (`id_usuario_atendio`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservacion_platillos` (
  `id_reservacion_platillo` int NOT NULL AUTO_INCREMENT,
  `id_reservacion` int NOT NULL,
  `id_platillo` int NOT NULL,
  `tipo_seccion` enum('primer_tiempo','plato_principal','acompanamiento','postre','bebida') NOT NULL,
  PRIMARY KEY (`id_reservacion_platillo`),
  KEY `id_reservacion` (`id_reservacion`),
  KEY `id_platillo` (`id_platillo`),
  CONSTRAINT `reservacion_platillos_ibfk_1` FOREIGN KEY (`id_reservacion`) REFERENCES `reservaciones` (`id_reservacion`),
  CONSTRAINT `reservacion_platillos_ibfk_2` FOREIGN KEY (`id_platillo`) REFERENCES `platillos` (`id_platillo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `asistencias` (
  `id_asistencia` int NOT NULL AUTO_INCREMENT,
  `fecha_asistencia` datetime DEFAULT CURRENT_TIMESTAMP,
  `confirmada_por` int DEFAULT NULL,
  `id_reservacion` int NOT NULL,
  PRIMARY KEY (`id_asistencia`),
  KEY `confirmada_por` (`confirmada_por`),
  KEY `id_reservacion` (`id_reservacion`),
  CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`confirmada_por`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`id_reservacion`) REFERENCES `reservaciones` (`id_reservacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `gastos` (
  `id_gasto` int NOT NULL AUTO_INCREMENT,
  `concepto` varchar(100) NOT NULL,
  `categoria` enum('ingredientes','personal','servicios','mantenimiento','otros') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_gasto` date NOT NULL,
  `descripcion` text,
  `id_usuario_registro` int NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `comprobante` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_gasto`),
  KEY `id_usuario_registro` (`id_usuario_registro`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inventario` (
  `id_item` int NOT NULL AUTO_INCREMENT,
  `nombre_item` varchar(100) NOT NULL,
  `categoria` enum('alimento','utensilio','limpieza','otros') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `nivel_minimo` decimal(10,2) DEFAULT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `fecha_ultima_compra` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reportes` (
  `id_reporte` int NOT NULL AUTO_INCREMENT,
  `nombre_reporte` varchar(100) NOT NULL,
  `tipo_reporte` enum('general','reservaciones','menu','usuarios','financiero') NOT NULL,
  `parametros` text,
  `id_usuario_creador` int NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reporte`),
  KEY `id_usuario_creador` (`id_usuario_creador`),
  CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`id_usuario_creador`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test data
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellidos`, `correo`, `direccion`, `edad`, `sexo`, `contrasena`, `tipo_usuario`, `fecha_registro`, `activo`, `recordar_token`, `token_recuperacion`, `token_expiracion`) VALUES
(1, 'Admin', 'Sistema', 'admin@comedor.com', 'casa', 20, 'masculino', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin', '2025-04-22 16:25:03', 1, NULL, NULL, NULL),
(2, 'Angel', 'Roberto', 'angelrobord55@gmail.com', 'casita', 19, 'masculino', '76487f8343c9852dacafaa276b7c474cbc5a7cc5346c0235f4c691209dc082b8', 'beneficiario', '2025-04-23 10:14:16', 1, '52663a17e8ca749e11b99c18ac8704d6e8b1f7d7652bb53df3bbf428550150e2', NULL, NULL),
(4, 'Emir', 'Sistema', 'emir@gmail.com', 'casa', 20, 'masculino', '5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5', 'empleado', '2025-04-23 12:37:39', 1, '00699b9edd1aee525f0dc6a4dbe993e0faa9ad387e7aae6ae6ca442538458c85', NULL, NULL),
(7, 'richi', 'xd', 'richi@gmail.com', 'casa', 19, 'masculino', '5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5', 'beneficiario', '2025-04-26 18:39:09', 1, 'cacb4d3feef4668b95a855d4089753ec934a93fb13c8bdc67f7f2af41e6871ba', NULL, NULL);

INSERT INTO `secciones_menu` (`id_seccion`, `nombre_seccion`, `descripcion`, `orden`, `activo`) VALUES
(1, 'Primer Tiempo', 'Sopas y consomés', 1, 1),
(2, 'Plato Principal', 'Platos fuertes del día', 2, 1),
(3, 'Acompañamientos', 'Guarniciones y complementos', 3, 1),
(4, 'Postres', 'Dulces y postres', 4, 1),
(5, 'Bebidas', 'Aguas frescas y otras bebidas', 5, 1);

INSERT INTO `platillos` (`id_platillo`, `id_seccion`, `nombre_platillo`, `descripcion`, `ingredientes`, `es_vegetariano`, `es_vegano`, `contiene_gluten`, `activo`) VALUES
(1, 1, 'Sopa de Verduras', 'Deliciosa sopa casera con zanahorias, calabacín y verdura fresca.', NULL, 0, 0, 0, 1),
(2, 1, 'Consomé de Pollo', 'Consomé tradicional con pollo, arroz, garbanzos y verduras.', NULL, 0, 0, 0, 1),
(3, 2, 'Guisado de Res', 'Tiernos trozos de res en salsa de tomate con papas y zanahorias.', NULL, 0, 0, 0, 1),
(4, 2, 'Pollo en Mole', 'Piezas de pollo bañadas en salsa de mole poblano con arroz.', NULL, 0, 0, 0, 1),
(5, 3, 'Arroz a la Mexicana', 'Arroz con verduras y especias mexicanas.', NULL, 0, 0, 0, 1),
(6, 3, 'Frijoles Refritos', 'Frijoles refritos tradicionales con queso fresco.', NULL, 0, 0, 0, 1),
(7, 4, 'Arroz con Leche', 'Arroz con leche casero con canela y pasas.', NULL, 0, 0, 0, 1),
(8, 5, 'Agua de Jamaica', 'Refrescante agua de jamaica natural.', NULL, 0, 0, 0, 1),
(9, 5, 'Agua de Horchata', 'Tradicional agua de horchata con canela.', NULL, 0, 0, 0, 1);

INSERT INTO `menus_dia` (`id_menu`, `fecha`, `precio`, `notas`, `id_usuario_creador`, `fecha_creacion`) VALUES
(1, '2025-04-24', 11.00, '', 4, '2025-04-24 09:50:44'),
(2, '2025-04-25', 11.00, NULL, 1, '2025-04-25 13:00:00'),
(3, '2025-05-05', 11.00, '', 4, '2025-05-05 14:37:33');

INSERT INTO `menu_platillos` (`id_menu_platillo`, `id_menu`, `id_platillo`, `disponible`) VALUES
(613, 1, 5, 1),
(614, 1, 6, 1),
(615, 1, 9, 1),
(616, 1, 8, 1),
(617, 1, 3, 1),
(618, 1, 4, 1),
(619, 1, 7, 1),
(620, 1, 2, 1),
(621, 1, 1, 1),
(622, 2, 1, 1),
(623, 2, 2, 1),
(624, 2, 3, 1),
(625, 2, 4, 0),
(626, 2, 5, 1),
(627, 2, 6, 1),
(628, 2, 7, 1),
(629, 2, 8, 1),
(630, 2, 9, 1),
(694, 3, 5, 1),
(695, 3, 6, 1),
(696, 3, 9, 1),
(697, 3, 8, 1),
(698, 3, 3, 1),
(699, 3, 4, 1),
(700, 3, 7, 1),
(701, 3, 2, 1),
(702, 3, 1, 1);

INSERT INTO `reservaciones` (`id_reservacion`, `codigo_reservacion`, `id_usuario`, `id_menu`, `fecha_reservacion`, `hora_recogida`, `num_porciones`, `estado`, `notas`, `fecha_creacion`, `id_usuario_atendio`, `fecha_atencion`) VALUES
(1, 'CC-2504-01', 2, 1, '2025-04-24', '12:00:00', 2, 'pendiente', 'Sin cebolla en la sopa', '2025-04-23 14:30:00', NULL, NULL),
(2, 'CC-2504-02', 7, 1, '2025-04-24', '12:30:00', 1, 'pendiente', 'Extra mole por favor', '2025-04-23 15:45:00', NULL, NULL),
(3, 'CC-2504-03', 2, 1, '2025-04-24', '13:00:00', 3, 'completada', '', '2025-04-23 16:20:00', 4, '2025-04-24 13:05:00'),
(4, 'CC-2504-04', 7, 1, '2025-04-24', '13:30:00', 2, 'cancelada', 'Ya no podré asistir', '2025-04-23 17:10:00', NULL, NULL);

INSERT INTO `reservacion_platillos` (`id_reservacion_platillo`, `id_reservacion`, `id_platillo`, `tipo_seccion`) VALUES
(1, 1, 1, 'primer_tiempo'),
(2, 1, 3, 'plato_principal'),
(3, 1, 8, 'bebida'),
(4, 2, 2, 'primer_tiempo'),
(5, 2, 4, 'plato_principal'),
(6, 2, 9, 'bebida'),
(7, 3, 1, 'primer_tiempo'),
(8, 3, 4, 'plato_principal'),
(9, 3, 8, 'bebida'),
(10, 4, 2, 'primer_tiempo'),
(11, 4, 3, 'plato_principal'),
(12, 4, 9, 'bebida');

INSERT INTO `asistencias` (`id_asistencia`, `fecha_asistencia`, `confirmada_por`, `id_reservacion`) VALUES
(1, '2025-04-24 13:05:00', 4, 3);