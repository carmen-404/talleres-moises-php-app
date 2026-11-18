<?php
require_once("config.php");

$conexion = obtenerConexion();

$fechaInicio = "";
$fechaFin    = "";
$franja      = "";
$prioridad   = "";
$confirmada  = "";

if (isset($_GET["txtFechaInicio"])) {
    $fechaInicio = $_GET["txtFechaInicio"];
}
if (isset($_GET["txtFechaFin"])) {
    $fechaFin = $_GET["txtFechaFin"];
}
if (isset($_GET["lstFranja"])) {
    $franja = $_GET["lstFranja"];
}
if (isset($_GET["lstPrioridad"])) {
    $prioridad = $_GET["lstPrioridad"];
}
if (isset($_GET["lstConfirmada"])) {
    $confirmada = $_GET["lstConfirmada"];
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
if ($franja != "") {
    $franjaEsc = mysqli_real_escape_string($conexion, $franja);
    $sql .= " AND r.franja_horaria = '$franjaEsc'";
}
if ($prioridad != "") {
    $prioridadInt = intval($prioridad);
    $sql .= " AND r.prioridad_reserva = $prioridadInt";
}
if ($confirmada != "") {
    $confirmadaInt = intval($confirmada);
    $sql .= " AND r.esta_confirmada = $confirmadaInt";
}

$sql .= " ORDER BY r.dia ASC, r.franja_horaria ASC;";

$resultado = mysqli_query($conexion, $sql);
$total = mysqli_num_rows($resultado);

$filas = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $ruta = "uploads/reservas/" . $fila["id"];
    if (file_exists($ruta)) {
        $imagen = "<img src='" . $ruta . "?v=" . filemtime($ruta) . "' alt='Imagen de la reserva' class='rounded border' style='width:70px; height:70px; object-fit:cover;'>";
    } else {
        $imagen = "<div class='rounded border bg-light text-muted d-flex align-items-center justify-content-center' style='width:70px; height:70px;'><i class='bi bi-image fs-4'></i></div>";
    }

    $vehiculoCliente = htmlspecialchars($fila["snapshot_coche_matricula"] . " - " . $fila["snapshot_cliente_email"]);
    $franjaTexto = ($fila["franja_horaria"] === "MANANA") ? "Mañana" : "Tarde";

    if ($fila["esta_confirmada"] == 1) {
        $badge = "<span class='badge text-bg-success'>Sí</span>";
    } else {
        $badge = "<span class='badge text-bg-secondary'>No</span>";
    }

    $tiempo = intval($fila["tiempo_total"]) . " min";
    $precio = number_format((float)$fila["precio_total"], 2, ",", ".") . " €";

    $filas .= "<tr>";
    $filas .= "<td>" . $imagen . "</td>";
    $filas .= "<td class='fw-semibold'>" . htmlspecialchars($fila["dia"]) . "</td>";
    $filas .= "<td>" . $franjaTexto . "</td>";
    $filas .= "<td>" . $vehiculoCliente . "</td>";
    $filas .= "<td>" . intval($fila["prioridad_reserva"]) . "</td>";
    $filas .= "<td>" . $badge . "</td>";
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

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-funnel" aria-hidden="true"></i>
        <span>Listado filtrado de reservas</span>
    </h2>

    <form action="listado_reservas_filtro.php" name="frmFiltroReservas" id="frmFiltroReservas" method="get">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="txtFechaInicio">Fecha desde</label>
                        <input id="txtFechaInicio" name="txtFechaInicio" class="form-control" type="date"
                            value="<?php echo htmlspecialchars($fechaInicio); ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="txtFechaFin">Fecha hasta</label>
                        <input id="txtFechaFin" name="txtFechaFin" class="form-control" type="date"
                            value="<?php echo htmlspecialchars($fechaFin); ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="lstFranja">Franja horaria</label>
                        <select name="lstFranja" id="lstFranja" class="form-select">
                            <option value="">-- Todas --</option>
                            <option value="MANANA" <?php if ($franja === "MANANA") {
                                                        echo "selected";
                                                    } ?>>Mañana</option>
                            <option value="TARDE" <?php if ($franja === "TARDE") {
                                                        echo "selected";
                                                    } ?>>Tarde</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label" for="lstPrioridad">Prioridad</label>
                        <select name="lstPrioridad" id="lstPrioridad" class="form-select">
                            <option value="">-- Todas --</option>
                            <option value="1" <?php if ($prioridad === "1") {
                                                    echo "selected";
                                                } ?>>1 - Urgente</option>
                            <option value="2" <?php if ($prioridad === "2") {
                                                    echo "selected";
                                                } ?>>2 - Alta</option>
                            <option value="3" <?php if ($prioridad === "3") {
                                                    echo "selected";
                                                } ?>>3 - Media</option>
                            <option value="4" <?php if ($prioridad === "4") {
                                                    echo "selected";
                                                } ?>>4 - Normal</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label" for="lstConfirmada">¿Está confirmada?</label>
                        <select name="lstConfirmada" id="lstConfirmada" class="form-select">
                            <option value="">-- Todas --</option>
                            <option value="1" <?php if ($confirmada === "1") {
                                                    echo "selected";
                                                } ?>>Sí</option>
                            <option value="0" <?php if ($confirmada === "0") {
                                                    echo "selected";
                                                } ?>>No</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-12 col-lg-auto">
                <button type="submit" id="btnFiltrar" name="btnFiltrar" class="btn btn-secondary btn-lg px-4 fw-semibold w-100">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                    Filtrar
                </button>
            </div>
            <div class="col-12 col-lg-auto">
                <a href="listado_reservas_filtro.php" class="btn btn-outline-danger btn-lg px-4 fw-semibold w-100">
                    <i class="bi bi-x-lg me-1" aria-hidden="true"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>

    <?php if ($total == 0) { ?>
        <div class="alert alert-info">No hay reservas que cumplan los filtros.</div>
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
</div>
<?php include_once("pie.html"); ?>