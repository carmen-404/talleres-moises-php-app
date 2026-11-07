-- =====================================================================
-- TALLERES MOISÉS · PANEL DE GESTIÓN
-- Copia de seguridad: creación de la base de datos, tablas, relaciones y datos.
-- Basado en relational-model.dbml (los comentarios del modelo se conservan aquí).
--
-- Requiere MySQL 8.0.16+ o MariaDB 10.2.1+ para que la restricción CHECK se aplique.
-- Nombres de tabla en MAYÚSCULAS, igual que en el modelo (en Linux MySQL distingue mayúsculas).
-- Todas las claves primarias son "id INT AUTO_INCREMENT".
--
-- DIFERENCIA CON EL MODELO: la columna opcional image_path (VARCHAR) se ha sustituido por
-- imagen (MEDIUMBLOB): las imágenes se guardan dentro de la propia base de datos (máx. 2 MB por imagen,
-- límite aplicado por la aplicación). El tipo (JPG/PNG/WEBP) se detecta al servir la imagen.
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
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(10) NOT NULL,
  `municipio` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_codigo_postal_codigo_municipio` (`codigo`, `municipio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CLIENTE
-- A client can own multiple cars.
-- Email is mandatory and unique.
-- Every client must have a postal code.
-- (Un cliente puede tener varios coches. El email es obligatorio y único. Todo cliente tiene código postal.)
-- =====================================================
CREATE TABLE `CLIENTE` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(20) NULL,
  `email` VARCHAR(150) NOT NULL,
  `id_cp` INT NOT NULL,
  `imagen` MEDIUMBLOB NULL, -- Optional profile image (imagen de perfil opcional)
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cliente_email` (`email`),
  KEY `idx_cliente_id_cp` (`id_cp`),
  -- Ref: CLIENTE.id_cp > CODIGO_POSTAL.id
  -- One postal code / municipality combination can belong to multiple clients.
  CONSTRAINT `fk_cliente_codigo_postal` FOREIGN KEY (`id_cp`) REFERENCES `CODIGO_POSTAL` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- COCHE
-- Each car belongs to exactly one client.
-- Registration number (matricula) must be unique.
-- (Cada coche pertenece a un único cliente. La matrícula es única.)
-- =====================================================
CREATE TABLE `COCHE` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `marca` VARCHAR(100) NOT NULL,
  `modelo` VARCHAR(100) NOT NULL,
  `matricula` VARCHAR(15) NOT NULL,
  `anio` INT NOT NULL,
  `id_cliente` INT NOT NULL,
  `imagen` MEDIUMBLOB NULL, -- Optional vehicle image (imagen opcional del vehículo)
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coche_matricula` (`matricula`),
  KEY `idx_coche_id_cliente` (`id_cliente`),
  -- Ref: COCHE.id_cliente > CLIENTE.id
  -- One client can own multiple cars.
  CONSTRAINT `fk_coche_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- RESERVA
-- Each reservation belongs to one car.
-- A car cannot have two reservations in the same slot.
--
-- tiempo_calculado and precio_calculado are snapshots of the estimates agreed upon with the client.
-- This preserves the original estimate if service data is subsequently modified.
-- Time is stored in minutes; price in euros.
-- (En la aplicación NO se escriben a mano: se calculan sumando los servicios de la reserva
--  cada vez que se crea, modifica o borra un servicio. Al crear una reserva valen 0.)
--
-- franja_horaria accepts only MANANA or TARDE (CHECK implementado abajo).
-- =====================================================
CREATE TABLE `RESERVA` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `dia` DATE NOT NULL,
  `franja_horaria` VARCHAR(10) NOT NULL, -- CHECK: MANANA or TARDE
  `tiempo_calculado` INT NOT NULL,
  `precio_calculado` DECIMAL(10,2) NOT NULL,
  `id_coche` INT NOT NULL,
  `imagen` MEDIUMBLOB NULL, -- Optional reservation image (imagen opcional de la reserva)
  PRIMARY KEY (`id`),
  -- Un coche no puede tener dos reservas en la misma franja (este índice también sirve a la FK id_coche)
  UNIQUE KEY `uq_reserva_coche_dia_franja` (`id_coche`, `dia`, `franja_horaria`),
  CONSTRAINT `chk_reserva_franja_horaria` CHECK (`franja_horaria` IN ('MANANA', 'TARDE')),
  -- Ref: RESERVA.id_coche > COCHE.id
  -- One car can have multiple reservations.
  CONSTRAINT `fk_reserva_coche` FOREIGN KEY (`id_coche`) REFERENCES `COCHE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SERVICIO
-- Services are described individually for each reservation.
-- They are not predefined catalogue entries.
-- Each reservation can contain multiple services.
-- Every service belongs to exactly one reservation.
--
-- tiempo_estimado is stored in minutes.
-- precio_base is the estimated price in euros.
-- =====================================================
CREATE TABLE `SERVICIO` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descripcion` TEXT NOT NULL,
  `tiempo_estimado` INT NOT NULL,
  `garantia` VARCHAR(255) NULL,
  `precio_base` DECIMAL(10,2) NOT NULL,
  `id_reserva` INT NOT NULL,
  `imagen` MEDIUMBLOB NULL, -- Optional service image (imagen opcional del servicio)
  PRIMARY KEY (`id`),
  KEY `idx_servicio_id_reserva` (`id_reserva`),
  -- Ref: SERVICIO.id_reserva > RESERVA.id
  -- One reservation can contain multiple services.
  CONSTRAINT `fk_servicio_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `RESERVA` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- NOTAS PARA PULIR EL MODELO (no implementadas a propósito)
--
-- 1) Borrado: todas las claves foráneas usan el comportamiento por defecto (RESTRICT):
--    no se puede borrar un registro que tenga hijos (cliente con coches, coche con reservas,
--    reserva con servicios). La aplicación muestra el motivo. Alternativas: ON DELETE CASCADE.
--
-- 2) BORRADO LÓGICO (soft delete) - cómo implementarlo más adelante:
--    a) Añadir a cada tabla una columna  deleted_at DATETIME NULL  (NULL = activo).
--       (Alternativa: activo TINYINT(1) NOT NULL DEFAULT 1.)
--    b) En la aplicación, "borrar" pasa a ser
--          UPDATE CLIENTE SET deleted_at = NOW() WHERE id = ?;
--       y TODAS las consultas (listados, búsquedas, desplegables, recuentos) añaden
--          WHERE deleted_at IS NULL
--       Se puede centralizar creando vistas (p. ej. V_CLIENTE_ACTIVO) o añadiéndolo en
--       el "select" de inc/entidades.php.
--    c) Las restricciones UNIQUE (email, matrícula, reserva+día+franja) siguen ocupadas por
--       los registros "borrados". Opciones: reutilizar/restaurar el registro antiguo, o sustituir
--       el UNIQUE por una columna generada que solo valga mientras esté activo, p. ej.
--          email_activo VARCHAR(150) AS (IF(deleted_at IS NULL, email, NULL)) VIRTUAL,
--          UNIQUE KEY (email_activo)
--       (los NULL no chocan entre sí en un índice UNIQUE).
--    d) Las claves foráneas ya no protegen: la aplicación debe comprobar que no hay hijos
--       ACTIVOS antes de borrar, o borrar en cascada de forma lógica (en una transacción).
--    e) Añadir una pantalla de "papelera" para restaurar (deleted_at = NULL) y, si hace falta,
--       una tarea periódica que elimine definitivamente lo muy antiguo.
--    f) tiempo_calculado / precio_calculado deben sumar solo los servicios activos.
--
-- 3) Cálculo de la reserva: hoy se recalcula en PHP (recalcularReserva en funcionesBD.php).
--    Podría hacerse con triggers en SERVICIO, o "congelarse" cuando la reserva se confirme.
-- =====================================================================