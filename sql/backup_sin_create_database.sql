-- =====================================================================
-- TALLERES MOISÉS · PANEL DE GESTIÓN
-- Copia de seguridad: creación de la base de datos, tablas, relaciones y datos.
-- Basado en relational-model.dbml (los comentarios del modelo se conservan aquí).
-- Requiere MySQL 8.0.16+ o MariaDB 10.2.1+ para que la restricción CHECK se aplique.
-- Nombres de tabla en MAYÚSCULAS, igual que en el modelo (en Linux MySQL distingue mayúsculas).
-- =====================================================================
SET NAMES utf8mb4;


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