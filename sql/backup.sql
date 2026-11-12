-- =====================================================================
-- TALLERES MOISÉS · PANEL DE GESTIÓN
-- Copia de seguridad: creación de la base de datos, tablas, relaciones y datos.
-- Basado en relational-model.dbml (los comentarios del modelo se conservan aquí).
--
-- Requiere MySQL 8.0.16+ o MariaDB 10.2.1+ para que la restricción CHECK se aplique.
-- Nombres de tabla en MAYÚSCULAS, igual que en el modelo (en Linux MySQL distingue mayúsculas).
-- Todas las claves primarias son "id INT AUTO_INCREMENT".
--
-- IMAGES HANDLING: la columna opcional imagen (MEDIUMBLOB), las imágenes se guardan dentro de la propia base de datos
-- (máx. 2 MB por imagen, límite aplicado por la aplicación); el tipo (JPG/PNG/WEBP) se autodetecta.

-- OPCIONES A NIVEL TE TABLA QUE SE APLICAN A TODAS:
-- InnoDB: supports transactions and foreign key constraints.
-- utf8mb4: supports the full Unicode character set, including emojis.
-- utf8mb4_unicode_ci: provides Unicode-aware sorting and comparisons,
--                     ignoring case (uppercase/lowercase) and generally accents.
--                     This also affects UNIQUE constraints, preventing duplicates
--                     that differ only in case or accents. (COMPROBADO QUE FUNCIONA)
-- =====================================================================
-- SET NAMES: ensures client-server communication uses utf8mb4.
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
-- Almacena códigos postales y su municipio.
-- La pareja codigo + municipio es UK.
-- =====================================================
CREATE TABLE `CODIGO_POSTAL` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(10) NOT NULL,
  `municipio` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_codigo_postal_codigo_municipio` (`codigo`, `municipio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- CLIENTE (JAVIER RODRÍGUEZ)
-- Un cliente puede tener varios coches.
-- El email es obligatorio y único. Todo cliente tiene código postal.
-- =====================================================
CREATE TABLE `CLIENTE` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(20) NULL,
  `email` VARCHAR(150) NOT NULL,
  `id_cp` INT NOT NULL,
  -- atributo para implementar el borrado lógico
  `deleted_at` DATETIME NULL DEFAULT NULL,
  `imagen` MEDIUMBLOB NULL, -- imagen del cliente (opcional)
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cliente_email` (`email`),
  -- Index en id_cp para optimizar las búsquedas y JOINs por código postal.
  KEY `idx_cliente_id_cp` (`id_cp`),
  -- Index opcional para borraado lógico. Podría crearse como index compuesto 
  -- junto con the de id_cp, pero no sé si el responsable de esta tabla lo usará.
  KEY `idx_cliente_deleted_at` (`deleted_at`),
  -- Ref: CLIENTE.id_cp > CODIGO_POSTAL.id
  CONSTRAINT `fk_cliente_codigo_postal` FOREIGN KEY (`id_cp`) REFERENCES `CODIGO_POSTAL` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- COCHE (JOSÉ MANUEL PAYÁN)
-- Cada coche pertenece a un único cliente.
-- La matrícula es única.
-- =====================================================
CREATE TABLE `COCHE` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `marca` VARCHAR(100) NOT NULL,
  `modelo` VARCHAR(100) NOT NULL,
  `matricula` VARCHAR(15) NOT NULL,
  `anio` INT NOT NULL,
  `id_cliente` INT NOT NULL,
  -- atributo para implementar el borrado lógico
  `deleted_at` DATETIME NULL DEFAULT NULL,
  `imagen` MEDIUMBLOB NULL, -- imagen del coche (opcional)
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coche_matricula` (`matricula`),

  -- [ONLY FOR SOFT DELETE] Handle UNIQUE constraint collision (keeping active plates strictly unique)
  -- Standard approach: Change UNIQUE(matricula) to UNIQUE(matricula, deleted_at)
  -- MySQL allows multiple NULL values in UNIQUE indexes, so active cars remain strictly unique.
  -- UNIQUE KEY `uq_coche_matricula_deleted` (`matricula`, `deleted_at`),

  KEY `idx_coche_id_cliente` (`id_cliente`),
  -- Index para filtrar los coche activos de forma óptima si se usa borrado lógico
  KEY `idx_coche_deleted_at` (`deleted_at`),
  -- Ref: COCHE.id_cliente > CLIENTE.id
  CONSTRAINT `fk_coche_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- RESERVA (CARMEN DÍAZ)
-- Cada reserva pertenece a un coche.
-- Un coche no puede tener una reserva un mismo día en una misma franja horaria.
--
-- INDEPENDENCIA Y TOLERANCIA AL FALLO:
-- - Esta tabla NO aplica BORRADO LÓGICO; las reservas quedan visibles por razones histórico-financieras.
-- - Se apoya en atributos snapshot (snapshot_cliente_email, snapshot_coche_matricula) de manera que 
--   el historial de reservas se mantenga completo incluso si en las tablas CLIENTE/COCHE se hace un hard delete, 
--   un soft delete (borrado lógico), o una purga de registros por RGPD (protección de datos).
-- - Si la tabla padre COCHE sufre un borrado físico (hard delete), id_coche pasa a NULL 
--   (ON DELETE SET NULL), dejando el registro de la reserva completamente intacto.
-- =====================================================
CREATE TABLE `RESERVA` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `dia` DATE NOT NULL,
  `franja_horaria` VARCHAR(10) NOT NULL, -- CHECK: MANANA o TARDE
  `id_coche` INT NULL, -- Se establece a NULL si el coche padre sufre borrado físico

  -- Snapshots históricos: guardan la información del cliente y del vehículo en el momento de crear la reserva
  -- para mantener la integridad de los datos financieros aunque se eliminen los registros originales.
  -- editar_reserva.php tendrá estos campos readonly
  `snapshot_cliente_email` VARCHAR(150) NOT NULL,
  `snapshot_coche_matricula` VARCHAR(15) NOT NULL,

  -- CK de 1 a 4: menor el número, mayor la prioridad
  `prioridad_reserva` INT NOT NULL DEFAULT 4,
  `esta_confirmada` BOOLEAN NOT NULL DEFAULT FALSE,

  -- El orden no se puede modificar aquí: siguen la clave primaria, índices, restricciones y clave foránea.
  PRIMARY KEY (`id`),
  -- Un coche no puede tener dos reservas en la misma franja el mismo día (sirve también a la FK id_coche)
  UNIQUE KEY `uq_reserva_coche_dia_franja` (`id_coche`, `dia`, `franja_horaria`),
  CONSTRAINT `chk_reserva_franja_horaria` CHECK (`franja_horaria` IN ('MANANA', 'TARDE')),
  CONSTRAINT `chk_reserva_prioridad` CHECK (`prioridad_reserva` BETWEEN 1 AND 4),
  -- Relación con COCHE: ON DELETE SET NULL permite conservar el historial si se borra el vehículo.
  CONSTRAINT `fk_reserva_coche` FOREIGN KEY (`id_coche`) REFERENCES `COCHE` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SERVICIO (MOISÉS OROZCO)
-- Los servicios se describen de forma individual para cada reserva.
-- No provienen de un catálogo predefinido.
-- Cada reserva puede contener múltiples servicios.
-- Cada servicio pertenece exactamente a una reserva.
--
-- tiempo_estimado se almacena en minutos.
-- precio_base es el precio estimado en euros (10,2).
--
-- CICLO DE VIDA (LIFECYCLE) & DEPENDENCIA:
-- - Borrado lógico NO se aplica en esta tabla.
-- - Existe una dependencia existencial estricta con RESERVA: si una reserva se borra,
--   todos sus servicios asociados se eliminan automáticamente en cascada (ON DELETE CASCADE).
-- =====================================================
CREATE TABLE `SERVICIO` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descripcion` TEXT NOT NULL,
  `tiempo_estimado` INT NOT NULL,
  `garantia` VARCHAR(255) NULL,
  `precio_base` DECIMAL(10,2) NOT NULL,
  `id_reserva` INT NOT NULL,
  `imagen` MEDIUMBLOB NULL, -- Imagen opcional del servicio

  -- El orden no se puede modificar aquí: siguen la clave primaria, índices y clave foránea.
  PRIMARY KEY (`id`),
  KEY `idx_servicio_id_reserva` (`id_reserva`),
  -- Relación con RESERVA: Una reserva tiene varios servicios. Si la reserva se borra, sus servicios mueren con ella.
  CONSTRAINT `fk_servicio_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `RESERVA` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;