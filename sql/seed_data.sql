-- =====================================================================
-- TALLERES MOISÉS · LIMPIEZA Y REINSERCIÓN EN ORDEN INVERSO
-- Borrado manual sin deshabilitar claves foráneas.
-- =====================================================================
USE `talleres_moises`;

-- 1. Borrar hijas primero (respetando FKs)
DELETE FROM `SERVICIO`;
DELETE FROM `RESERVA`;
DELETE FROM `COCHE`;
DELETE FROM `CLIENTE`;
DELETE FROM `CODIGO_POSTAL`;

-- 2. Opcional: Reiniciar los contadores AUTO_INCREMENT a 1
ALTER TABLE `SERVICIO` AUTO_INCREMENT = 1;
ALTER TABLE `RESERVA` AUTO_INCREMENT = 1;
ALTER TABLE `COCHE` AUTO_INCREMENT = 1;
ALTER TABLE `CLIENTE` AUTO_INCREMENT = 1;
ALTER TABLE `CODIGO_POSTAL` AUTO_INCREMENT = 1;

-- =====================================================================
-- INSERCIÓN EN ORDEN DE DEPENDENCIAS (PADRES PRIMERO)
-- =====================================================================

-- 1. CODIGO_POSTAL
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

-- 2. CLIENTE
INSERT INTO `CLIENTE` (`id`, `nombre_completo`, `telefono`, `email`, `id_cp`, `deleted_at`) VALUES
(1, 'Antonio García Ruiz', '654123987', 'antonio.garcia@example.com', 1, NULL),
(2, 'María Fernández López', '611223344', 'maria.fernandez@example.com', 2, NULL),
(3, 'José Manuel Sánchez Pérez', '622334455', 'josem.sanchez@example.com', 6, NULL),
(4, 'Laura Jiménez Castro', '633445566', 'laura.jimenez@example.com', 3, NULL),
(5, 'Francisco Romero Díaz', '644556677', 'fran.romero@example.com', 7, NULL),
(6, 'Carmen Navarro Gil', NULL, 'carmen.navarro@example.com', 8, NULL),
(7, 'Pedro Molina Ortega', '666778899', 'pedro.molina@example.com', 4, NULL),
(8, 'Lucía Herrera Vega', '677889900', 'lucia.herrera@example.com', 9, NULL),
(9, 'Javier Domínguez Mora', '688990011', 'javier.dominguez@example.com', 10, NULL),
(10, 'Rocío Castillo León', '699001122', 'rocio.castillo@example.com', 5, NULL),
(11, 'Manuel Ortiz Prieto', '610111213', 'manuel.ortiz@example.com', 11, NULL),
(12, 'Ana Belén Cruz Soto', '620212223', 'anabelen.cruz@example.com', 12, '2026-10-15 11:30:00');

-- 3. COCHE
INSERT INTO `COCHE` (`id`, `fecha_entrada`, `reparar`, `marca`, `modelo`, `matricula`, `anio`, `id_cliente`, `deleted_at`) VALUES
(1,  '2026-11-02', 0, 'Seat',       'León',    '4521KJH', 2018, 1,  NULL),
(2,  '2026-11-03', 0, 'Renault',    'Clio',    '7733LMN', 2020, 2,  NULL),
(3,  '2026-11-04', 1, 'Volkswagen', 'Golf',    '1289GTR', 2015, 3,  NULL),
(4,  '2026-11-04', 1, 'Ford',       'Focus',   '9087HBC', 2017, 4,  NULL),
(5,  '2026-11-05', 0, 'Toyota',     'Corolla', '3345MPR', 2021, 5,  NULL),
(6,  '2026-11-05', 0, 'Peugeot',    '208',     '6612KLD', 2019, 6,  NULL),
(7,  '2026-11-06', 0, 'Citroën',    'C3',      '2290JRT', 2016, 7,  NULL),
(8,  '2026-11-06', 0, 'Opel',       'Corsa',   '8854LCV', 2022, 8,  NULL),
(9,  '2026-11-09', 1, 'Seat',       'Ibiza',   '5501HNM', 2014, 9,  NULL),
(10, '2026-11-09', 0, 'Dacia',      'Sandero', '7788MZX', 2023, 10, NULL),
(11, '2026-11-10', 1, 'BMW',        'Serie 1', '1024KFG', 2019, 11, NULL),
(12, '2026-10-05', 0, 'Audi',       'A3',      '6639LBN', 2020, 12, '2026-10-15 11:30:00'),
(13, '2026-11-12', 0, 'Seat',       'Arona',   '4410MKT', 2021, 1,  NULL),
(14, '2026-11-12', 1, 'Renault',    'Megane',  '2877HJP', 2013, 3,  NULL);

-- 4. RESERVA
INSERT INTO `RESERVA` (`id`, `dia`, `franja_horaria`, `id_coche`, `snapshot_cliente_email`, `snapshot_coche_matricula`, `prioridad_reserva`, `esta_confirmada`) VALUES
(1,  '2026-11-09', 'MANANA', 1,  'antonio.garcia@example.com',   '4521KJH', 2, TRUE),
(2,  '2026-11-09', 'TARDE',  2,  'maria.fernandez@example.com',  '7733LMN', 3, TRUE),
(3,  '2026-11-10', 'MANANA', 3,  'josem.sanchez@example.com',    '1289GTR', 1, TRUE),
(4,  '2026-11-10', 'TARDE',  4,  'laura.jimenez@example.com',    '9087HBC', 4, TRUE),
(5,  '2026-11-11', 'MANANA', 5,  'fran.romero@example.com',      '3345MPR', 3, TRUE),
(6,  '2026-11-11', 'TARDE',  6,  'carmen.navarro@example.com',   '6612KLD', 2, TRUE),
(7,  '2026-11-12', 'MANANA', 7,  'pedro.molina@example.com',     '2290JRT', 4, TRUE),
(8,  '2026-11-12', 'TARDE',  8,  'lucia.herrera@example.com',    '8854LCV', 3, FALSE),
(9,  '2026-11-13', 'MANANA', 9,  'javier.dominguez@example.com', '5501HNM', 1, TRUE),
(10, '2026-11-13', 'TARDE',  10, 'rocio.castillo@example.com',   '7788MZX', 2, FALSE),
(11, '2026-11-14', 'MANANA', 11, 'manuel.ortiz@example.com',     '1024KFG', 2, TRUE),
(12, '2026-11-16', 'MANANA', 12, 'anabelen.cruz@example.com',    '6639LBN', 1, TRUE),
(13, '2026-11-16', 'TARDE',  13, 'antonio.garcia@example.com',   '4410MKT', 4, FALSE);

-- 5. SERVICIO
INSERT INTO `SERVICIO` (`id`, `descripcion`, `tiempo_estimado`, `garantia`, `precio_base`, `id_reserva`) VALUES
(1,  'Cambio de aceite y filtro',              45,  '6 meses',            65.00,  1),
(2,  'Revisión de frenos',                     60,  '1 año',              40.00,  1),
(3,  'Sustitución de batería',                 30,  '2 años',             85.00,  2),
(4,  'Cambio de kit de distribución',          240, '2 años',             420.00, 3),
(5,  'Diagnosis electrónica',                  60,  NULL,                 45.00,  4),
(6,  'Cambio de bujías',                       50,  '1 año',              60.00,  4),
(7,  'Alineación y equilibrado de ruedas',     60,  '6 meses',            55.00,  5),
(8,  'Cambio de dos neumáticos',               40,  'Oficial fabricante', 140.00, 6),
(9,  'Revisión pre-ITV',                       45,  NULL,                 30.00,  7),
(10, 'Recarga de aire acondicionado',          60,  '1 año',              75.00,  8),
(11, 'Cambio de embrague',                     300, '2 años',             650.00, 9),
(12, 'Mantenimiento oficial 10.000 km',        90,  '1 año',              120.00, 10),
(13, 'Cambio de pastillas y discos delanteros', 90,  '2 años',             180.00, 11),
(14, 'Reparación de la suspensión',            120, '1 año',              260.00, 12),
(15, 'Limpieza de inyectores',                 60,  NULL,                 70.00,  13);