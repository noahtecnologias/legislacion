CREATE TABLE `compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) DEFAULT NULL,
  `unidades` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL,
  `fecha_actualizacion` datetime NOT NULL,
  `municipio_id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `observacion` text DEFAULT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_COMPRA_MUNICIPIO` (`municipio_id`),
  KEY `FK_COMPRA_DEPARTAMENTO` (`departamento_id`),
  CONSTRAINT `FK_COMPRA_DEPARTAMENTO` FOREIGN KEY (`departamento_id`) REFERENCES `departamento` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_COMPRA_MUNICIPIO` FOREIGN KEY (`municipio_id`) REFERENCES `municipio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `modulo` VALUES (3,'Compras','COMPRAS',3,1);