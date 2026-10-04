-- =========================================================
-- TECNOFIX - BASE DE DATOS OFICIAL (MYSQL / MARIADB)
-- Implementación para el Segundo Parcial - Prototipo V3
-- =========================================================

CREATE DATABASE IF NOT EXISTS `tecnofix_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tecnofix_db`;

-- ---------------------------------------------------------
-- 1. TABLA: usuarios
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `historial_ordenes`;
DROP TABLE IF EXISTS `ordenes_servicio`;
DROP TABLE IF EXISTS `equipos`;
DROP TABLE IF EXISTS `clientes`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `migracion_log`;

CREATE TABLE `usuarios` (
    `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `rol` ENUM('Administrador', 'Tecnico', 'Recepcion') NOT NULL DEFAULT 'Recepcion',
    `estado` ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    `debe_cambiar_pass` TINYINT(1) NOT NULL DEFAULT 0, -- 1: Debe cambiar clave en primer login
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2. TABLA: clientes
-- ---------------------------------------------------------
CREATE TABLE `clientes` (
    `id_cliente` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_completo` VARCHAR(120) NOT NULL,
    `cedula_rnc` VARCHAR(20) UNIQUE,
    `telefono` VARCHAR(20) NOT NULL,
    `email` VARCHAR(120),
    `direccion` TEXT,
    `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 3. TABLA: equipos
-- ---------------------------------------------------------
CREATE TABLE `equipos` (
    `id_equipo` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `tipo_dispositivo` VARCHAR(50) NOT NULL,
    `marca` VARCHAR(50) NOT NULL,
    `modelo` VARCHAR(50) NOT NULL,
    `numero_serie` VARCHAR(100),
    `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_equipos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 4. TABLA: ordenes_servicio
-- ---------------------------------------------------------
CREATE TABLE `ordenes_servicio` (
    `id_orden` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_orden` VARCHAR(20) NOT NULL UNIQUE,
    `id_cliente` INT NOT NULL,
    `id_equipo` INT NOT NULL,
    `id_tecnico` INT NULL,
    `diagnostico_inicial` TEXT NOT NULL,
    `solucion_propuesta` TEXT,
    `estado` ENUM('Pendiente', 'En Proceso', 'Esperando Repuesto', 'Completado', 'Entregado', 'Cancelado') NOT NULL DEFAULT 'Pendiente',
    `costo_estimado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `costo_final` DECIMAL(10,2) DEFAULT 0.00,
    `fecha_ingreso` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_estimada` DATE,
    `fecha_cierre` DATETIME NULL,
    CONSTRAINT `fk_ordenes_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_ordenes_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_ordenes_tecnico` FOREIGN KEY (`id_tecnico`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 5. TABLA: historial_ordenes
-- ---------------------------------------------------------
CREATE TABLE `historial_ordenes` (
    `id_historial` INT AUTO_INCREMENT PRIMARY KEY,
    `id_orden` INT NOT NULL,
    `estado_anterior` VARCHAR(30) NOT NULL,
    `estado_nuevo` VARCHAR(30) NOT NULL,
    `observaciones` TEXT,
    `id_usuario` INT NOT NULL,
    `fecha_cambio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_historial_orden` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_servicio` (`id_orden`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 6. TABLA: migracion_log
-- ---------------------------------------------------------
CREATE TABLE `migracion_log` (
    `id_log` INT AUTO_INCREMENT PRIMARY KEY,
    `sistema_origen` VARCHAR(100) NOT NULL,
    `registros_procesados` INT NOT NULL,
    `registros_exitosos` INT NOT NULL,
    `registros_fallidos` INT NOT NULL,
    `estado_migracion` ENUM('EXITOSO', 'CON_ERRORES', 'FALLIDO') NOT NULL,
    `detalles` TEXT,
    `fecha_ejecucion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DATOS INICIALES (SEED DATA)
-- =========================================================

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password_hash`, `rol`, `estado`, `debe_cambiar_pass`) VALUES
(1, 'Administrador TecnoFix', 'admin@tecnofix.com', '$2y$10$K2e.nIqjH35rXGZpYwS28.3m0F5Q7u.Xn1.Qz3Fz/N.P1O9Yn/6OW', 'Administrador', 'Activo', 0),
(2, 'Carlos Ruiz (Técnico Senior)', 'carlos.tecnico@tecnofix.com', '$2y$10$K2e.nIqjH35rXGZpYwS28.3m0F5Q7u.Xn1.Qz3Fz/N.P1O9Yn/6OW', 'Tecnico', 'Activo', 0),
(3, 'María López (Recepción)', 'maria.recepcion@tecnofix.com', '$2y$10$K2e.nIqjH35rXGZpYwS28.3m0F5Q7u.Xn1.Qz3Fz/N.P1O9Yn/6OW', 'Recepcion', 'Activo', 0),
(4, 'Pedro Ramírez (Técnico Nuevo)', 'pedro.nuevo@tecnofix.com', '$2y$10$w8T07S6.l/aNfC9Z1mH2eO0O1kG9x1u5q0G4o.Xn1.Qz3Fz/N.P1O', 'Tecnico', 'Activo', 1);

INSERT INTO `clientes` (`id_cliente`, `nombre_completo`, `cedula_rnc`, `telefono`, `email`, `direccion`) VALUES
(1, 'Juan Pérez', '001-1234567-8', '809-555-0101', 'juan.perez@email.com', 'Av. 27 de Febrero #45, SD'),
(2, 'Ana Gómez', '001-7654321-9', '809-555-0202', 'ana.gomez@email.com', 'Calle El Sol #12, Santiago'),
(3, 'Empresa Inversiones SRL', '130-998877-1', '809-555-0303', 'contacto@inversiones.com', 'Torre Empresarial Piso 5');

INSERT INTO `equipos` (`id_equipo`, `id_cliente`, `tipo_dispositivo`, `marca`, `modelo`, `numero_serie`) VALUES
(1, 1, 'Laptop', 'Dell', 'XPS 15 9520', 'SN-DELL-99812'),
(2, 2, 'Smartphone', 'Apple', 'iPhone 13 Pro', 'SN-APPL-77123'),
(3, 3, 'Desktop PC', 'Custom', 'Core i7 12th Gen', 'SN-CUST-33411');

INSERT INTO `ordenes_servicio` (`id_orden`, `codigo_orden`, `id_cliente`, `id_equipo`, `id_tecnico`, `diagnostico_inicial`, `solucion_propuesta`, `estado`, `costo_estimado`, `costo_final`, `fecha_ingreso`, `fecha_estimada`) VALUES
(1, 'ORD-2026-001', 1, 1, 2, 'El equipo no enciende tras descarga eléctrica.', 'Reemplazo de chip de carga y tarjeta madre.', 'En Proceso', 4500.00, 0.00, NOW(), '2026-10-10'),
(2, 'ORD-2026-002', 2, 2, 2, 'Pantalla fisurada y falla en digitalizador táctil.', 'Cambio de pantalla completa OLED.', 'Pendiente', 6800.00, 0.00, NOW(), '2026-10-08'),
(3, 'ORD-2026-003', 3, 3, NULL, 'Mantenimiento preventivo y formateo de sistema.', 'Limpieza de componentes y reinstalación de SO.', 'Completado', 2500.00, 2500.00, '2026-10-01', '2026-10-03');

INSERT INTO `historial_ordenes` (`id_historial`, `id_orden`, `estado_anterior`, `estado_nuevo`, `observaciones`, `id_usuario`) VALUES
(1, 1, 'Pendiente', 'En Proceso', 'Se desmonta la tarjeta para diagnóstico avanzado.', 2),
(2, 3, 'En Proceso', 'Completado', 'Mantenimiento realizado y pruebas pasadas exitosamente.', 1);
