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