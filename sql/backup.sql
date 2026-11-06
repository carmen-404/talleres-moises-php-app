-- =====================================================================
-- TALLERES MOISÉS · PANEL DE GESTIÓN
-- Copia de seguridad: creación de la base de datos, tablas, relaciones y datos.
-- Basado en relational-model.dbml (los comentarios del modelo se conservan aquí).
-- Requiere MySQL 8.0.16+ o MariaDB 10.2.1+ para que la restricción CHECK se aplique.
-- Nombres de tabla en MAYÚSCULAS, igual que en el modelo (en Linux MySQL distingue mayúsculas).
-- =====================================================================
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `talleres_moises` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `talleres_moises`;

-- Se borran en orden inverso a las dependencias (hijas primero)
DROP TABLE IF EXISTS `SERVICIO`;
DROP TABLE IF EXISTS `RESERVA`;
DROP TABLE IF EXISTS `COCHE`;
DROP TABLE IF EXISTS `CLIENTE`;
DROP TABLE IF EXISTS `CODIGO_POSTAL`;

-- =====================================================
-- CODIGO_POSTAL
-- Stores postal codes and their corresponding municipalities.
-- The combination of codigo and municipio must be unique.
-- (Almacena códigos postales y su municipio. La pareja codigo + municipio es única.)
-- =====================================================
CREATE TABLE `CODIGO_POSTAL` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria autoincremental',
  `codigo` VARCHAR(10) NOT NULL COMMENT 'Código postal',
  `municipio` VARCHAR(100) NOT NULL COMMENT 'Municipio al que corresponde el código postal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_codigo_postal_codigo_municipio` (`codigo`, `municipio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Códigos postales y municipios; la pareja codigo+municipio es única';

-- =====================================================
-- CLIENTE
-- A client can own multiple cars.
-- Email is mandatory and unique.
-- Every client must have a postal code.
-- (Un cliente puede tener varios coches. El email es obligatorio y único. Todo cliente tiene código postal.)
-- =====================================================
CREATE TABLE `CLIENTE` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria autoincremental',
  `nombre_completo` VARCHAR(150) NOT NULL COMMENT 'Nombre y apellidos',
  `telefono` VARCHAR(20) NULL COMMENT 'Teléfono (opcional)',
  `email` VARCHAR(150) NOT NULL COMMENT 'Email obligatorio y único',
  `id_cp` INT NOT NULL COMMENT 'FK a CODIGO_POSTAL.id: todo cliente tiene código postal',
  `image_path` VARCHAR(255) NULL COMMENT 'Optional profile image (imagen de perfil opcional)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cliente_email` (`email`),
  KEY `idx_cliente_id_cp` (`id_cp`),
  -- Ref: CLIENTE.id_cp > CODIGO_POSTAL.id
  -- Un código postal / municipio puede pertenecer a varios clientes.
  CONSTRAINT `fk_cliente_codigo_postal` FOREIGN KEY (`id_cp`) REFERENCES `CODIGO_POSTAL` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Clientes del taller; un cliente puede tener varios coches';

-- =====================================================
-- COCHE
-- Each car belongs to exactly one client.
-- Registration number (matricula) must be unique.
-- (Cada coche pertenece a un único cliente. La matrícula es única.)
-- =====================================================
CREATE TABLE `COCHE` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria autoincremental',
  `marca` VARCHAR(100) NOT NULL COMMENT 'Marca del vehículo',
  `modelo` VARCHAR(100) NOT NULL COMMENT 'Modelo del vehículo',
  `matricula` VARCHAR(15) NOT NULL COMMENT 'Matrícula única',
  `anio` INT NOT NULL COMMENT 'Año de matriculación / fabricación',
  `id_cliente` INT NOT NULL COMMENT 'FK a CLIENTE.id: propietario del coche',
  `image_path` VARCHAR(255) NULL COMMENT 'Optional vehicle image (imagen opcional del vehículo)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coche_matricula` (`matricula`),
  KEY `idx_coche_id_cliente` (`id_cliente`),
  -- Ref: COCHE.id_cliente > CLIENTE.id
  -- Un cliente puede tener varios coches.
  CONSTRAINT `fk_coche_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Coches; cada coche pertenece a un único cliente';

-- =====================================================
-- RESERVA
-- Each reservation belongs to one car.
-- A car cannot have two reservations in the same slot.
--
-- tiempo_calculado and precio_calculado are snapshots of the estimates agreed upon with the client.
-- This preserves the original estimate if service data is subsequently modified.
-- (Son una "foto" del presupuesto pactado: no cambian si luego se modifican los servicios.)
--
-- Time is stored in minutes; price in euros.
--
-- franja_horaria accepts only MANANA or TARDE (restricción CHECK implementada abajo).
-- =====================================================
CREATE TABLE `RESERVA` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria autoincremental',
  `dia` DATE NOT NULL COMMENT 'Día de la reserva',
  `franja_horaria` VARCHAR(10) NOT NULL COMMENT 'CHECK: MANANA or TARDE',
  `tiempo_calculado` INT NOT NULL COMMENT 'Snapshot del tiempo pactado, en minutos',
  `precio_calculado` DECIMAL(10,2) NOT NULL COMMENT 'Snapshot del precio pactado, en euros',
  `id_coche` INT NOT NULL COMMENT 'FK a COCHE.id: coche al que pertenece la reserva',
  `image_path` VARCHAR(255) NULL COMMENT 'Optional reservation image (imagen opcional de la reserva)',
  PRIMARY KEY (`id`),
  -- Un coche no puede tener dos reservas en la misma franja (este índice también sirve a la FK id_coche)
  UNIQUE KEY `uq_reserva_coche_dia_franja` (`id_coche`, `dia`, `franja_horaria`),
  CONSTRAINT `chk_reserva_franja_horaria` CHECK (`franja_horaria` IN ('MANANA', 'TARDE')),
  -- Ref: RESERVA.id_coche > COCHE.id
  -- Un coche puede tener varias reservas.
  CONSTRAINT `fk_reserva_coche` FOREIGN KEY (`id_coche`) REFERENCES `COCHE` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Reservas; tiempo_calculado y precio_calculado son una foto del presupuesto pactado';

-- =====================================================
-- SERVICIO
-- Services are described individually for each reservation.
-- They are not predefined catalogue entries.
-- Each reservation can contain multiple services.
-- Every service belongs to exactly one reservation.
-- (Los servicios se describen uno a uno en cada reserva; no son un catálogo predefinido.)
--
-- tiempo_estimado is stored in minutes.
-- precio_base is the estimated price in euros.
-- =====================================================
CREATE TABLE `SERVICIO` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria autoincremental',
  `descripcion` TEXT NOT NULL COMMENT 'Descripción del servicio',
  `tiempo_estimado` INT NOT NULL COMMENT 'Tiempo estimado, en minutos',
  `garantia` VARCHAR(255) NULL COMMENT 'Garantía (opcional)',
  `precio_base` DECIMAL(10,2) NOT NULL COMMENT 'Precio estimado, en euros',
  `id_reserva` INT NOT NULL COMMENT 'FK a RESERVA.id: reserva a la que pertenece el servicio',
  `image_path` VARCHAR(255) NULL COMMENT 'Optional service image (imagen opcional del servicio)',
  PRIMARY KEY (`id`),
  KEY `idx_servicio_id_reserva` (`id_reserva`),
  -- Ref: SERVICIO.id_reserva > RESERVA.id
  -- Una reserva puede contener varios servicios. Al borrar la reserva se borran sus servicios,
  -- porque un servicio no existe fuera de su reserva.
  CONSTRAINT `fk_servicio_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `RESERVA` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Servicios descritos individualmente en cada reserva (no son un catálogo)';

-- =====================================================================
-- DATOS DE EJEMPLO (más de 10 registros por tabla)
-- =====================================================================
INSERT INTO `CODIGO_POSTAL` (`id`, `codigo`, `municipio`) VALUES
(1, '41001', 'Sevilla'),
(2, '41003', 'Sevilla'),
(3, '41005', 'Sevilla'),
(4, '41008', 'Sevilla'),
(5, '41013', 'Sevilla'),
(6, '41700', 'Dos Hermanas'),
(7, '41500', 'Alcalá de Guadaíra'),
(8, '41927', 'Mairena del Aljarafe'),
(9, '41900', 'Camas'),
(10, '41930', 'Bormujos'),
(11, '41940', 'Tomares'),
(12, '41309', 'La Rinconada');

INSERT INTO `CLIENTE` (`id`, `nombre_completo`, `telefono`, `email`, `id_cp`) VALUES
(1, 'Antonio García Ruiz', '654123987', 'antonio.garcia@example.com', 1),
(2, 'María Fernández López', '611223344', 'maria.fernandez@example.com', 2),
(3, 'José Manuel Sánchez Pérez', '622334455', 'josem.sanchez@example.com', 6),
(4, 'Laura Jiménez Castro', '633445566', 'laura.jimenez@example.com', 3),
(5, 'Francisco Romero Díaz', '644556677', 'fran.romero@example.com', 7),
(6, 'Carmen Navarro Gil', NULL, 'carmen.navarro@example.com', 8),
(7, 'Pedro Molina Ortega', '666778899', 'pedro.molina@example.com', 4),
(8, 'Lucía Herrera Vega', '677889900', 'lucia.herrera@example.com', 9),
(9, 'Javier Domínguez Mora', '688990011', 'javier.dominguez@example.com', 10),
(10, 'Rocío Castillo León', '699001122', 'rocio.castillo@example.com', 5),
(11, 'Manuel Ortiz Prieto', '610111213', 'manuel.ortiz@example.com', 11),
(12, 'Ana Belén Cruz Soto', '620212223', 'anabelen.cruz@example.com', 12);

INSERT INTO `COCHE` (`id`, `marca`, `modelo`, `matricula`, `anio`, `id_cliente`) VALUES
(1, 'Seat', 'León', '4521KJH', 2018, 1),
(2, 'Renault', 'Clio', '7733LMN', 2020, 2),
(3, 'Volkswagen', 'Golf', '1289GTR', 2015, 3),
(4, 'Ford', 'Focus', '9087HBC', 2017, 4),
(5, 'Toyota', 'Corolla', '3345MPR', 2021, 5),
(6, 'Peugeot', '208', '6612KLD', 2019, 6),
(7, 'Citroën', 'C3', '2290JRT', 2016, 7),
(8, 'Opel', 'Corsa', '8854LCV', 2022, 8),
(9, 'Seat', 'Ibiza', '5501HNM', 2014, 9),
(10, 'Dacia', 'Sandero', '7788MZX', 2023, 10),
(11, 'BMW', 'Serie 1', '1024KFG', 2019, 11),
(12, 'Audi', 'A3', '6639LBN', 2020, 12),
(13, 'Seat', 'Arona', '4410MKT', 2021, 1),
(14, 'Renault', 'Megane', '2877HJP', 2013, 3);

INSERT INTO `RESERVA` (`id`, `dia`, `franja_horaria`, `tiempo_calculado`, `precio_calculado`, `id_coche`) VALUES
(1, '2026-11-09', 'MANANA', 105, 105.00, 1),
(2, '2026-11-09', 'TARDE', 30, 85.00, 2),
(3, '2026-11-10', 'MANANA', 240, 420.00, 3),
(4, '2026-11-10', 'TARDE', 110, 105.00, 4),
(5, '2026-11-11', 'MANANA', 60, 55.00, 5),
(6, '2026-11-11', 'TARDE', 40, 140.00, 6),
(7, '2026-11-12', 'MANANA', 45, 30.00, 7),
(8, '2026-11-12', 'TARDE', 60, 75.00, 8),
(9, '2026-11-13', 'MANANA', 300, 650.00, 9),
(10, '2026-11-13', 'TARDE', 90, 120.00, 10),
(11, '2026-11-14', 'MANANA', 90, 180.00, 11),
(12, '2026-11-16', 'MANANA', 120, 260.00, 12),
(13, '2026-11-16', 'TARDE', 60, 70.00, 13);

INSERT INTO `SERVICIO` (`id`, `descripcion`, `tiempo_estimado`, `garantia`, `precio_base`, `id_reserva`) VALUES
(1, 'Cambio de aceite y filtro', 45, '6 meses', 65.00, 1),
(2, 'Revisión de frenos', 60, '1 año', 40.00, 1),
(3, 'Sustitución de batería', 30, '2 años', 85.00, 2),
(4, 'Cambio de kit de distribución', 240, '2 años', 420.00, 3),
(5, 'Diagnosis electrónica', 60, NULL, 45.00, 4),
(6, 'Cambio de bujías', 50, '1 año', 60.00, 4),
(7, 'Alineación y equilibrado de ruedas', 60, '6 meses', 55.00, 5),
(8, 'Cambio de dos neumáticos', 40, 'Oficial fabricante', 140.00, 6),
(9, 'Revisión pre-ITV', 45, NULL, 30.00, 7),
(10, 'Recarga de aire acondicionado', 60, '1 año', 75.00, 8),
(11, 'Cambio de embrague', 300, '2 años', 650.00, 9),
(12, 'Mantenimiento oficial 10.000 km', 90, '1 año', 120.00, 10),
(13, 'Cambio de pastillas y discos delanteros', 90, '2 años', 180.00, 11),
(14, 'Reparación de la suspensión', 120, '1 año', 260.00, 12),
(15, 'Limpieza de inyectores', 60, NULL, 70.00, 13);
