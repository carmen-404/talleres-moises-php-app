<?php
require_once("config.php");

$conexion = obtenerConexion();

$fechaInicio = "";
if (isset($_GET["txtFechaInicio"])) {
    $fechaInicio = $_GET["txtFechaInicio"];
}

$fechaFin = "";
if (isset($_GET["txtFechaFin"])) {
    $fechaFin = $_GET["txtFechaFin"];
}

if ($fechaInicio != "" && $fechaFin != "" && $fechaInicio > $fechaFin) {
    $aux = $fechaInicio;
    $fechaInicio = $fechaFin;
    $fechaFin = $aux;
}

$sql = "SELECT r.id, r.dia, r.franja_horaria, r.snapshot_coche_matricula, r.snapshot_cliente_email,
               r.prioridad_reserva, r.esta_confirmada,
               (SELECT COALESCE(SUM(s.tiempo_estimado), 0)
                FROM SERVICIO s
                WHERE s.id_reserva = r.id) AS tiempo_total,
               (SELECT COALESCE(SUM(s.precio_base), 0)
                FROM SERVICIO s
                WHERE s.id_reserva = r.id) AS precio_total
        FROM RESERVA r
        WHERE 1=1";

if ($fechaInicio != "") {
    $fIniEsc = mysqli_real_escape_string($conexion, $fechaInicio);
    $sql .= " AND r.dia >= '$fIniEsc'";
}
if ($fechaFin != "") {
    $fFinEsc = mysqli_real_escape_string($conexion, $fechaFin);
    $sql .= " AND r.dia <= '$fFinEsc'";
}

$sql .= " ORDER BY r.dia ASC, r.franja_horaria ASC;";

$resultado = mysqli_query($conexion, $sql);
$total = mysqli_num_rows($resultado);

$filas = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $ruta = "uploads/reservas/" . $fila["id"];
    if (file_exists($ruta)) {
        $imagen = "<img src='" . $ruta . "?v=" . filemtime($ruta) . "' class='rounded border' style='width:70px; height:70px; object-fit:cover;'>";
    } else {
        $imagen = "<div class='rounded border bg-light text-muted d-flex align-items-center justify-content-center' style='width:70px; height:70px;'><i class='bi bi-image fs-4'></i></div>";
    }

    $vehiculoCliente = htmlspecialchars($fila["snapshot_coche_matricula"] . " - " . $fila["snapshot_cliente_email"]);
    $franja = ($fila["franja_horaria"] === "MANANA") ? "Mañana" : "Tarde";

    if ($fila["esta_confirmada"] == 1) {
        $confirmada = "<span class='badge text-bg-success'>Sí</span>";
    } else {
        $confirmada = "<span class='badge text-bg-secondary'>No</span>";
    }

    $tiempo = intval($fila["tiempo_total"]) . " min";
    $precio = number_format((float)$fila["precio_total"], 2, ",", ".") . " €";

    $filas .= "<tr>";
    $filas .= "<td>" . $imagen . "</td>";
    $filas .= "<td class='fw-semibold'>" . htmlspecialchars($fila["dia"]) . "</td>";
    $filas .= "<td>" . $franja . "</td>";
    $filas .= "<td>" . $vehiculoCliente . "</td>";
    $filas .= "<td>" . intval($fila["prioridad_reserva"]) . "</td>";
    $filas .= "<td>" . $confirmada . "</td>";
    $filas .= "<td>" . $tiempo . "</td>";
    $filas .= "<td>" . $precio . "</td>";
    $filas .= "<td class='text-nowrap'>";
    $filas .= "<div class='d-flex flex-column flex-lg-row gap-1'>";
    $filas .= "<a href='editar_reserva.php?id=" . $fila["id"] . "' class='btn btn-sm btn-outline-primary'><i class='bi bi-pencil'></i></a>";
    $filas .= "<form class='m-0' action='proceso_borrar_reserva.php' method='post' onsubmit=\"return confirm('¿Seguro que quieres borrar esta reserva?');\">";
    $filas .= "<input type='hidden' name='id' value='" . $fila["id"] . "'>";
    $filas .= "<button type='submit' class='btn btn-sm btn-outline-danger w-100'><i class='bi bi-trash'></i></button>";
    $filas .= "</form>";
    $filas .= "</div>";
    $filas .= "</td>";
    $filas .= "</tr>";
}

cerrarConexion($conexion);

if ($fechaInicio != "" && $fechaFin != "") {
    $busquedaTexto = "Desde $fechaInicio hasta $fechaFin";
} elseif ($fechaInicio != "") {
    $busquedaTexto = "Desde $fechaInicio";
} elseif ($fechaFin != "") {
    $busquedaTexto = "Hasta $fechaFin";
} else {
    $busquedaTexto = "Todas las fechas";
}

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-search" aria-hidden="true"></i>
        <span>Resultado de la búsqueda</span>
    </h2>

    <p class="text-muted">Búsqueda: «<?php echo htmlspecialchars($busquedaTexto); ?>»</p>

    <?php if ($total == 0) { ?>
        <div class="alert alert-info">No se han encontrado reservas.</div>
    <?php } else { ?>
        <p class="text-muted"><?php echo $total; ?> reserva(s) encontrada(s)</p>

        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Día</th>
                    <th>Franja</th>
                    <th>Matrícula - Cliente</th>
                    <th>Prioridad</th>
                    <th>Confirmada</th>
                    <th>Tiempo Est.</th>
                    <th>Precio Base</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $filas; ?>
            </tbody>
        </table>
    <?php } ?>

    <a href="buscar_reserva.php" class="btn btn-secondary px-4 fw-semibold">
        <i class="bi bi-search me-1" aria-hidden="true"></i>
        Nueva búsqueda
    </a>
</div>
<?php include_once("pie.html"); ?>
