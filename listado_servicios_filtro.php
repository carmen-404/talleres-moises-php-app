<?php
require_once("config.php");

$conexion = obtenerConexion();

// 1. Cargar reservas para el desplegable del filtro
$sqlReservas = "SELECT r.id,
                       DATE_FORMAT(r.dia, '%d/%m/%Y') AS fecha,
                       r.franja_horaria,
                       COALESCE(co.matricula, r.snapshot_coche_matricula) AS matricula
                FROM RESERVA r
                LEFT JOIN COCHE co ON r.id_coche = co.id
                ORDER BY r.dia DESC, r.id DESC";
$resReservas = mysqli_query($conexion, $sqlReservas);

// Recoger filtros enviados por POST
$esConsulta        = ($_SERVER["REQUEST_METHOD"] === "POST");
$filtroReserva     = isset($_POST["id_reserva"]) ? $_POST["id_reserva"] : "";
$filtroDescripcion = isset($_POST["descripcion"]) ? trim($_POST["descripcion"]) : "";
$filtroFechaDesde  = isset($_POST["fecha_desde"]) ? $_POST["fecha_desde"] : "";
$filtroFechaHasta  = isset($_POST["fecha_hasta"]) ? $_POST["fecha_hasta"] : "";
$filtroTerminada   = isset($_POST["terminada"]) ? $_POST["terminada"] : "";

$optionsReservas = "";
if ($resReservas && mysqli_num_rows($resReservas) > 0) {
    while ($fila = mysqli_fetch_assoc($resReservas)) {
        $franja = ($fila["franja_horaria"] === "MANANA") ? "Mañana" : "Tarde";
        $texto = "#" . $fila["id"] . " · " . $fila["fecha"] . " (" . $franja . ") · " . $fila["matricula"];
        $selected = ($filtroReserva !== "" && (int)$filtroReserva === (int)$fila["id"]) ? "selected" : "";
        $optionsReservas .= "<option value='" . (int)$fila["id"] . "' $selected>" . htmlspecialchars($texto) . "</option>";
    }
}

// 2. Si se ha enviado el formulario, construir y ejecutar la consulta parametrizada
$resultadoServicios = null;
$totalRegistros = 0;

if ($esConsulta) {
    $condiciones = [];

    if ($filtroReserva !== "") {
        $idReserva = (int)$filtroReserva;
        $condiciones[] = "s.id_reserva = $idReserva";
    }

    if ($filtroDescripcion !== "") {
        $descEscapada = mysqli_real_escape_string($conexion, $filtroDescripcion);
        $condiciones[] = "s.descripcion LIKE '%$descEscapada%'";
    }

    if ($filtroFechaDesde !== "") {
        $desdeEscapada = mysqli_real_escape_string($conexion, $filtroFechaDesde);
        $condiciones[] = "s.fecha >= '$desdeEscapada'";
    }

    if ($filtroFechaHasta !== "") {
        $hastaEscapada = mysqli_real_escape_string($conexion, $filtroFechaHasta);
        $condiciones[] = "s.fecha <= '$hastaEscapada'";
    }

    if ($filtroTerminada === "0" || $filtroTerminada === "1") {
        $terminadaInt = (int)$filtroTerminada;
        $condiciones[] = "s.terminada = $terminadaInt";
    }

    $whereSql = (count($condiciones) > 0) ? "WHERE " . implode(" AND ", $condiciones) : "";

    $sqlServicios = "SELECT s.id,
                            DATE_FORMAT(s.fecha, '%d/%m/%Y') AS fecha_formateada,
                            s.descripcion,
                            s.tiempo_estimado,
                            s.garantia,
                            s.precio_base,
                            s.id_reserva,
                            s.terminada,
                            s.imagen,
                            COALESCE(co.matricula, r.snapshot_coche_matricula) AS matricula
                     FROM SERVICIO s
                     INNER JOIN RESERVA r ON s.id_reserva = r.id
                     LEFT JOIN COCHE co ON r.id_coche = co.id
                     $whereSql
                     ORDER BY s.fecha DESC, s.id DESC";

    $resultadoServicios = mysqli_query($conexion, $sqlServicios);
    $totalRegistros = ($resultadoServicios) ? mysqli_num_rows($resultadoServicios) : 0;
}

include_once("cabecera.html");
?>

<div class="container mt-4 mb-5" id="formularios">
    <h2 class="text-primary fw-normal mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-funnel" aria-hidden="true"></i>
        <span>Listado parametrizado de servicios</span>
    </h2>

    <!-- Formulario de filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="listado_servicios_filtro.php" method="POST" name="frmListadoServiciosFiltro" id="frmListadoServiciosFiltro">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="id_reserva" class="form-label mb-1">Reserva</label>
                        <select name="id_reserva" id="id_reserva" class="form-select">
                            <option value="">-- Todas las reservas --</option>
                            <?php echo $optionsReservas; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="descripcion" class="form-label mb-1">Descripción contiene</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                               placeholder="Ej. Aceite, frenos..." value="<?php echo htmlspecialchars($filtroDescripcion); ?>">
                    </div>

                    <div class="col-6 col-md-2 col-lg-2">
                        <label for="fecha_desde" class="form-label mb-1">Desde fecha</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde"
                               value="<?php echo htmlspecialchars($filtroFechaDesde); ?>">
                    </div>

                    <div class="col-6 col-md-2 col-lg-2">
                        <label for="fecha_hasta" class="form-label mb-1">Hasta fecha</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta"
                               value="<?php echo htmlspecialchars($filtroFechaHasta); ?>">
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="terminada" class="form-label mb-1">Estado</label>
                        <select name="terminada" id="terminada" class="form-select">
                            <option value="" <?php echo ($filtroTerminada === "") ? "selected" : ""; ?>>Todos</option>
                            <option value="1" <?php echo ($filtroTerminada === "1") ? "selected" : ""; ?>>Completados</option>
                            <option value="0" <?php echo ($filtroTerminada === "0") ? "selected" : ""; ?>>Pendientes</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mt-3">
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-secondary btn-hero px-4 fw-semibold w-100">
                            <i class="bi bi-search me-1" aria-hidden="true"></i> Consultar
                        </button>
                    </div>
                    <div class="col-12 col-md-auto">
                        <a href="listado_servicios_filtro.php" class="btn btn-outline-danger btn-hero px-4 fw-semibold w-100">
                            <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i> Limpiar filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados de la consulta parametrizada -->
    <?php if ($esConsulta): ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted">
                Resultados encontrados: <strong><?php echo $totalRegistros; ?></strong> servicio(s)
            </span>
        </div>

        <?php if ($totalRegistros === 0): ?>
            <div class="alert alert-info shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                No se encontraron servicios con los criterios seleccionados.
            </div>
        <?php else: ?>
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Imagen</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Reserva / Matrícula</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Tiempo</th>
                            <th scope="col">Precio base</th>
                            <th scope="col">Garantía</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        while ($fila = mysqli_fetch_assoc($resultadoServicios)):
                            $garantia = ($fila["garantia"] !== null && $fila["garantia"] !== "")
                                ? htmlspecialchars($fila["garantia"])
                                : "<span class='text-muted'>Sin garantía</span>";

                            $estadoBadge = ((int)$fila["terminada"] === 1)
                                ? "<span class='badge bg-success'>Completado</span>"
                                : "<span class='badge bg-warning text-dark'>Pendiente</span>";

                            $imgHtml = "<span class='text-muted small'>Sin imagen</span>";
                            if (!empty($fila["imagen"])) {
                                $mime = $finfo->buffer($fila["imagen"]) ?: "image/jpeg";
                                $base64 = base64_encode($fila["imagen"]);
                                $imgHtml = "<img src='data:" . htmlspecialchars($mime) . ";base64,$base64' alt='Servicio #" . (int)$fila["id"] . "' class='img-thumbnail' style='width: 65px; height: 65px; object-fit: cover;'>";
                            }
                        ?>
                            <tr>
                                <th scope="row">#<?php echo (int)$fila["id"]; ?></th>
                                <td><?php echo $imgHtml; ?></td>
                                <td><?php echo htmlspecialchars($fila["fecha_formateada"]); ?></td>
                                <td>
                                    #<?php echo (int)$fila["id_reserva"]; ?> ·
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($fila["matricula"]); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($fila["descripcion"]); ?></td>
                                <td><?php echo (int)$fila["tiempo_estimado"]; ?> min</td>
                                <td class="fw-semibold"><?php echo number_format((float)$fila["precio_base"], 2, ",", "."); ?> €</td>
                                <td><?php echo $garantia; ?></td>
                                <td><?php echo $estadoBadge; ?></td>
                                <td class="text-center text-nowrap">
                                    <div class="d-flex justify-content-center gap-2">
                                        <form action="alta_servicio.php" method="POST" class="d-inline">
                                            <input type="hidden" name="id_servicio" value="<?php echo (int)$fila["id"]; ?>">
                                            <button type="submit" class="btn btn-sm btn-warning fw-semibold">
                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                            </button>
                                        </form>
                                        <form action="proceso_borrar_servicio.php" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');">
                                            <input type="hidden" name="id_servicio" value="<?php echo (int)$fila["id"]; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                                                <i class="bi bi-trash me-1"></i> Borrar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
cerrarConexion($conexion);
include_once("pie.html");
?>