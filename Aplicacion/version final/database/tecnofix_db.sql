-- =========================================================
-- TECNOFIX - BASE DE DATOS OFICIAL (MYSQL / MARIADB)
-- Implementación para el Segundo Parcial - Prototipo V3
-- =========================================================

CREATE DATABASE IF NOT EXISTS `tecnofix_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tecnofix_db`;

-- ---------------------------------------------------------
-- 1. TABLA: usuarios
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
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
CREATE TABLE IF NOT EXISTS `clientes` (
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
CREATE TABLE IF NOT EXISTS `equipos` (
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
CREATE TABLE IF NOT EXISTS `ordenes_servicio` (
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
CREATE TABLE IF NOT EXISTS `historial_ordenes` (
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
CREATE TABLE IF NOT EXISTS `migracion_log` (
    `id_log` INT AUTO_INCREMENT PRIMARY KEY,
    `sistema_origen` VARCHAR(100) NOT NULL,
    `registros_procesados` INT NOT NULL,
    `registros_exitosos` INT NOT NULL,
    `registros_fallidos` INT NOT NULL,
    `estado_migracion` ENUM('EXITOSO', 'CON_ERRORES', 'FALLIDO') NOT NULL,
    `detalles` TEXT,
    `fecha_ejecucion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Los datos de demostración se cargan explícitamente desde tecnofix_demo_data.sql.
