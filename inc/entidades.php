<?php
// DEFINICIÓN DE LAS TABLAS CON CRUD. Aquí está todo lo que cambia de una tabla a otra;
// el "motor" genérico está en funcionesBD.php. El esquema real está en sql/backup.sql.
// (CODIGO_POSTAL no tiene CRUD: solo alimenta el desplegable de clientes.)

// Desplegables cargados desde la base de datos: "valor" = id, "texto" = lo que ve el usuario
$OPCIONES = array(
    "codigo_postal" => "SELECT id AS valor, CONCAT(codigo, ' · ', municipio) AS texto FROM CODIGO_POSTAL ORDER BY codigo, municipio",
    "clientes" => "SELECT id AS valor, nombre_completo AS texto FROM CLIENTE ORDER BY nombre_completo",
    "coches" => "SELECT co.id AS valor, CONCAT(co.matricula, ' · ', co.marca, ' ', co.modelo, ' (', cl.nombre_completo, ')') AS texto
                 FROM COCHE co INNER JOIN CLIENTE cl ON cl.id = co.id_cliente ORDER BY co.matricula",
    "reservas" => "SELECT r.id AS valor, CONCAT('#', r.id, ' · ', DATE_FORMAT(r.dia, '%d/%m/%Y'), ' ', r.franja_horaria, ' · ', co.matricula) AS texto
                   FROM RESERVA r INNER JOIN COCHE co ON co.id = r.id_coche ORDER BY r.dia DESC, r.id DESC"
);

$FRANJAS = array("MANANA" => "Mañana", "TARDE" => "Tarde");

$ENTIDADES = array(

"clientes" => array(
    "tabla" => "CLIENTE", "slug" => "cliente", "singular" => "cliente", "titulo" => "Clientes",
    "genero" => "m", "etiquetaNuevo" => "Nuevo cliente", "icono" => "bi-people-fill",
    // En los listados NO se selecciona el BLOB de la imagen: solo si existe (tiene_imagen)
    "select" => "SELECT c.id, c.nombre_completo, c.telefono, c.email, c.id_cp,
                        CONCAT(cp.codigo, ' · ', cp.municipio) AS cp_texto, (c.imagen IS NOT NULL) AS tiene_imagen
                 FROM CLIENTE c INNER JOIN CODIGO_POSTAL cp ON cp.id = c.id_cp",
    "orden" => "c.nombre_completo",
    "busqueda" => "c.nombre_completo", "busquedaEtiqueta" => "Nombre del cliente (o parte del nombre)",
    "columnaTexto" => "nombre_completo", "nombreRegistro" => "nombre_completo",
    "columnas" => array(
        array("clave" => "id", "etiqueta" => "ID", "formato" => "id"),
        array("clave" => "tiene_imagen", "etiqueta" => "", "formato" => "imagen"),
        array("clave" => "nombre_completo", "etiqueta" => "Nombre", "formato" => "texto", "fuerte" => true),
        array("clave" => "telefono", "etiqueta" => "Teléfono", "formato" => "texto", "movil" => false),
        array("clave" => "email", "etiqueta" => "Correo electrónico", "formato" => "texto", "movil" => false),
        array("clave" => "cp_texto", "etiqueta" => "C. P. · Municipio", "formato" => "texto")
    ),
    "campos" => array(
        "nombre_completo" => array("etiqueta" => "Nombre completo", "tipo" => "s", "control" => "text", "max" => 150, "obligatorio" => true),
        "telefono" => array("etiqueta" => "Teléfono", "tipo" => "s", "control" => "tel", "max" => 20,
                            "patron" => "[0-9 +]{9,20}", "errorPatron" => "Solo números, espacios y + (mínimo 9 caracteres)."),
        "email" => array("etiqueta" => "Correo electrónico", "tipo" => "s", "control" => "email", "max" => 150, "obligatorio" => true, "formato" => "email"),
        "id_cp" => array("etiqueta" => "Código postal · municipio", "tipo" => "i", "control" => "select", "fuente" => "codigo_postal", "obligatorio" => true, "min" => 1)
    ),
    "filtros" => array(
        "email" => array("etiqueta" => "Correo contiene", "control" => "text", "col" => "c.email", "op" => "like"),
        "telefono" => array("etiqueta" => "Teléfono contiene", "control" => "text", "col" => "c.telefono", "op" => "like"),
        "id_cp" => array("etiqueta" => "Código postal", "control" => "select", "fuente" => "codigo_postal", "col" => "c.id_cp", "op" => "eq", "tipo" => "i")
    ),
    "relacionados" => array(
        array("Coches", "bi-car-front", "listado_coches_filtro.php?id_cliente={id}&btnFiltrar=1"),
        array("Añadir coche", "bi-plus-circle", "alta_coche.php?id_cliente={id}")
    ),
    "msgDuplicado" => "Ya existe un cliente con ese correo electrónico.",
    "msgHijos" => "No se puede borrar el cliente porque tiene coches asociados. Borra o reasigna sus coches primero."
),

"coches" => array(
    "tabla" => "COCHE", "slug" => "coche", "singular" => "coche", "titulo" => "Coches",
    "genero" => "m", "etiquetaNuevo" => "Nuevo coche", "icono" => "bi-car-front-fill",
    "select" => "SELECT co.id, co.marca, co.modelo, co.matricula, co.anio, co.id_cliente,
                        cl.nombre_completo AS cliente, (co.imagen IS NOT NULL) AS tiene_imagen
                 FROM COCHE co INNER JOIN CLIENTE cl ON cl.id = co.id_cliente",
    "orden" => "co.matricula",
    "busqueda" => "co.matricula", "busquedaEtiqueta" => "Matrícula (o parte de la matrícula)",
    "columnaTexto" => "matricula", "nombreRegistro" => "matricula",
    "columnas" => array(
        array("clave" => "id", "etiqueta" => "ID", "formato" => "id"),
        array("clave" => "tiene_imagen", "etiqueta" => "", "formato" => "imagen"),
        array("clave" => "matricula", "etiqueta" => "Matrícula", "formato" => "texto", "fuerte" => true),
        array("clave" => "marca", "etiqueta" => "Marca", "formato" => "texto"),
        array("clave" => "modelo", "etiqueta" => "Modelo", "formato" => "texto"),
        array("clave" => "anio", "etiqueta" => "Año", "formato" => "texto", "movil" => false),
        array("clave" => "cliente", "etiqueta" => "Propietario", "formato" => "texto")
    ),
    "campos" => array(
        "id_cliente" => array("etiqueta" => "Propietario (cliente)", "tipo" => "i", "control" => "select", "fuente" => "clientes", "obligatorio" => true, "min" => 1),
        "marca" => array("etiqueta" => "Marca", "tipo" => "s", "control" => "text", "max" => 100, "obligatorio" => true),
        "modelo" => array("etiqueta" => "Modelo", "tipo" => "s", "control" => "text", "max" => 100, "obligatorio" => true),
        "matricula" => array("etiqueta" => "Matrícula", "tipo" => "s", "control" => "text", "max" => 15, "obligatorio" => true, "mayusculas" => true),
        "anio" => array("etiqueta" => "Año", "tipo" => "i", "control" => "number", "min" => 1950, "maxv" => (int)date("Y") + 1,
                        "step" => 1, "obligatorio" => true, "defecto" => (int)date("Y"))
    ),
    "filtros" => array(
        "marca" => array("etiqueta" => "Marca contiene", "control" => "text", "col" => "co.marca", "op" => "like"),
        "anio_desde" => array("etiqueta" => "Año desde", "control" => "number", "step" => 1, "col" => "co.anio", "op" => "gte", "tipo" => "i"),
        "anio_hasta" => array("etiqueta" => "Año hasta", "control" => "number", "step" => 1, "col" => "co.anio", "op" => "lte", "tipo" => "i"),
        "id_cliente" => array("etiqueta" => "Propietario", "control" => "select", "fuente" => "clientes", "col" => "co.id_cliente", "op" => "eq", "tipo" => "i")
    ),
    "relacionados" => array(
        array("Reservas", "bi-calendar-check", "listado_reservas_filtro.php?id_coche={id}&btnFiltrar=1"),
        array("Añadir reserva", "bi-plus-circle", "alta_reserva.php?id_coche={id}")
    ),
    "msgDuplicado" => "Ya existe un coche con esa matrícula.",
    "msgHijos" => "No se puede borrar el coche porque tiene reservas asociadas. Borra sus reservas primero."
),

"reservas" => array(
    "tabla" => "RESERVA", "slug" => "reserva", "singular" => "reserva", "titulo" => "Reservas",
    "genero" => "f", "etiquetaNuevo" => "Nueva reserva", "icono" => "bi-calendar-check",
    "select" => "SELECT r.id, r.dia, r.franja_horaria, r.tiempo_calculado, r.precio_calculado, r.id_coche,
                        CONCAT(co.matricula, ' · ', co.marca, ' ', co.modelo) AS coche_texto,
                        cl.nombre_completo AS cliente, (r.imagen IS NOT NULL) AS tiene_imagen
                 FROM RESERVA r
                 INNER JOIN COCHE co ON co.id = r.id_coche
                 INNER JOIN CLIENTE cl ON cl.id = co.id_cliente",
    "orden" => "r.dia DESC, FIELD(r.franja_horaria, 'MANANA', 'TARDE'), r.id",
    "busqueda" => "co.matricula", "busquedaEtiqueta" => "Matrícula del coche (o parte de la matrícula)",
    "columnaTexto" => "coche_texto",
    "columnas" => array(
        array("clave" => "id", "etiqueta" => "ID", "formato" => "id"),
        array("clave" => "tiene_imagen", "etiqueta" => "", "formato" => "imagen"),
        array("clave" => "dia", "etiqueta" => "Día", "formato" => "fecha", "fuerte" => true),
        array("clave" => "franja_horaria", "etiqueta" => "Franja", "formato" => "franja"),
        array("clave" => "coche_texto", "etiqueta" => "Coche", "formato" => "texto"),
        array("clave" => "cliente", "etiqueta" => "Cliente", "formato" => "texto", "movil" => false),
        array("clave" => "tiempo_calculado", "etiqueta" => "Tiempo estimado", "formato" => "minutos", "movil" => false),
        array("clave" => "precio_calculado", "etiqueta" => "Precio estimado", "formato" => "moneda")
    ),
    // tiempo_calculado y precio_calculado NO están aquí: no se escriben a mano, se calculan a partir de los servicios
    "campos" => array(
        "id_coche" => array("etiqueta" => "Coche", "tipo" => "i", "control" => "select", "fuente" => "coches", "obligatorio" => true, "min" => 1),
        "dia" => array("etiqueta" => "Día", "tipo" => "date", "control" => "date", "obligatorio" => true, "defecto" => "hoy"),
        "franja_horaria" => array("etiqueta" => "Franja horaria", "tipo" => "e", "control" => "select", "valores" => $FRANJAS, "obligatorio" => true, "defecto" => "MANANA")
    ),
    "insertarExtra" => array("tiempo_calculado" => array("i", 0), "precio_calculado" => array("d", 0.0)),
    "informativos" => array(
        "tiempo_calculado" => array("etiqueta" => "Tiempo estimado", "formato" => "minutos"),
        "precio_calculado" => array("etiqueta" => "Precio estimado", "formato" => "moneda")
    ),
    "ayuda" => "El tiempo y el precio estimados se calculan solos a partir de los servicios de la reserva (son una promesa al cliente y no se editan a mano). Crea la reserva y añade después sus servicios.",
    "filtros" => array(
        "dia_desde" => array("etiqueta" => "Día desde", "control" => "date", "col" => "r.dia", "op" => "gte", "tipo" => "s"),
        "dia_hasta" => array("etiqueta" => "Día hasta", "control" => "date", "col" => "r.dia", "op" => "lte", "tipo" => "s"),
        "franja_horaria" => array("etiqueta" => "Franja horaria", "control" => "select", "valores" => $FRANJAS, "col" => "r.franja_horaria", "op" => "eq", "tipo" => "s"),
        "id_coche" => array("etiqueta" => "Coche", "control" => "select", "fuente" => "coches", "col" => "r.id_coche", "op" => "eq", "tipo" => "i"),
        "precio_min" => array("etiqueta" => "Precio estimado mínimo (€)", "control" => "number", "step" => "0.01", "col" => "r.precio_calculado", "op" => "gte", "tipo" => "d"),
        "precio_max" => array("etiqueta" => "Precio estimado máximo (€)", "control" => "number", "step" => "0.01", "col" => "r.precio_calculado", "op" => "lte", "tipo" => "d")
    ),
    "relacionados" => array(
        array("Servicios", "bi-wrench-adjustable", "listado_servicios_filtro.php?id_reserva={id}&btnFiltrar=1"),
        array("Añadir servicio", "bi-plus-circle", "alta_servicio.php?id_reserva={id}")
    ),
    "msgDuplicado" => "Ese coche ya tiene una reserva en ese día y franja horaria.",
    "msgHijos" => "No se puede borrar la reserva porque tiene servicios asociados. Borra sus servicios primero."
),

"servicios" => array(
    "tabla" => "SERVICIO", "slug" => "servicio", "singular" => "servicio", "titulo" => "Servicios",
    "genero" => "m", "etiquetaNuevo" => "Nuevo servicio", "icono" => "bi-wrench-adjustable",
    "select" => "SELECT s.id, s.descripcion, s.tiempo_estimado, s.garantia, s.precio_base, s.id_reserva,
                        CONCAT('#', r.id, ' · ', DATE_FORMAT(r.dia, '%d/%m/%Y'), ' ', r.franja_horaria, ' · ', co.matricula) AS reserva_texto,
                        (s.imagen IS NOT NULL) AS tiene_imagen
                 FROM SERVICIO s
                 INNER JOIN RESERVA r ON r.id = s.id_reserva
                 INNER JOIN COCHE co ON co.id = r.id_coche",
    "orden" => "s.id_reserva, s.id",
    "busqueda" => "s.descripcion", "busquedaEtiqueta" => "Descripción del servicio (o parte)",
    "columnaTexto" => "descripcion", "nombreRegistro" => "descripcion",
    "columnas" => array(
        array("clave" => "id", "etiqueta" => "ID", "formato" => "id"),
        array("clave" => "tiene_imagen", "etiqueta" => "", "formato" => "imagen"),
        array("clave" => "descripcion", "etiqueta" => "Descripción", "formato" => "largo", "fuerte" => true),
        array("clave" => "tiempo_estimado", "etiqueta" => "Tiempo estimado", "formato" => "minutos", "movil" => false),
        array("clave" => "garantia", "etiqueta" => "Garantía", "formato" => "texto", "movil" => false),
        array("clave" => "precio_base", "etiqueta" => "Precio base", "formato" => "moneda"),
        array("clave" => "reserva_texto", "etiqueta" => "Reserva", "formato" => "texto", "movil" => false)
    ),
    "campos" => array(
        "id_reserva" => array("etiqueta" => "Reserva", "tipo" => "i", "control" => "select", "fuente" => "reservas", "obligatorio" => true, "min" => 1),
        "descripcion" => array("etiqueta" => "Descripción del servicio", "tipo" => "t", "control" => "textarea", "obligatorio" => true),
        "tiempo_estimado" => array("etiqueta" => "Tiempo estimado (minutos)", "tipo" => "i", "control" => "number", "min" => 0, "step" => 1, "obligatorio" => true, "defecto" => 30),
        "precio_base" => array("etiqueta" => "Precio base (€)", "tipo" => "d", "control" => "number", "min" => 0, "step" => "0.01", "obligatorio" => true, "defecto" => "0.00"),
        "garantia" => array("etiqueta" => "Garantía", "tipo" => "s", "control" => "text", "max" => 255)
    ),
    // Al guardar o borrar un servicio se recalcula el tiempo y el precio estimados de su reserva
    "recalcula" => "id_reserva",
    "ayuda" => "Al guardar el servicio, el tiempo y el precio estimados de la reserva se recalculan automáticamente.",
    "filtros" => array(
        "garantia" => array("etiqueta" => "Garantía contiene", "control" => "text", "col" => "s.garantia", "op" => "like"),
        "precio_min" => array("etiqueta" => "Precio base mínimo (€)", "control" => "number", "step" => "0.01", "col" => "s.precio_base", "op" => "gte", "tipo" => "d"),
        "precio_max" => array("etiqueta" => "Precio base máximo (€)", "control" => "number", "step" => "0.01", "col" => "s.precio_base", "op" => "lte", "tipo" => "d"),
        "id_reserva" => array("etiqueta" => "Reserva", "control" => "select", "fuente" => "reservas", "col" => "s.id_reserva", "op" => "eq", "tipo" => "i")
    ),
    "relacionados" => array(),
    "msgDuplicado" => "Ya existe un servicio igual.",
    "msgHijos" => "No se puede borrar el servicio."
)

);
