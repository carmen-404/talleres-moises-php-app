<?php
require_once("config.php");

$conexion = obtenerConexion();

// Comprobar si venimos desde el botón "Editar"
$modoEdicion = false;
$servicioEditar = null;

if (isset($_POST["id_servicio"]) && $_POST["id_servicio"] != "") {
    $idEditar = (int)$_POST["id_servicio"];
    $sqlServicio = "SELECT * FROM SERVICIO WHERE id = $idEditar";
    $resServicio = mysqli_query($conexion, $sqlServicio);

    if ($resServicio && mysqli_num_rows($resServicio) > 0) {
        $modoEdicion = true;
        $servicioEditar = mysqli_fetch_assoc($resServicio);
    }
}

// Cargar reservas para el desplegable
$sql = "SELECT r.id,
               DATE_FORMAT(r.dia, '%d/%m/%Y') AS fecha,
               r.franja_horaria,
               COALESCE(co.matricula, r.snapshot_coche_matricula) AS matricula,
               COALESCE(cl.nombre_completo, r.snapshot_cliente_email) AS cliente
        FROM RESERVA r
        LEFT JOIN COCHE co ON r.id_coche = co.id
        LEFT JOIN CLIENTE cl ON co.id_cliente = cl.id
        ORDER BY r.dia DESC, FIELD(r.franja_horaria, 'MANANA', 'TARDE'), r.id DESC";

$resultado = mysqli_query($conexion, $sql);
$hayReservas = ($resultado && mysqli_num_rows($resultado) > 0);

$optionsReservas = "";
if ($hayReservas) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $franja = ($fila["franja_horaria"] === "MANANA") ? "Mañana" : "Tarde";
        $texto  = "#" . $fila["id"] . " · " . $fila["fecha"] . " (" . $franja . ") · "
                . $fila["matricula"] . " - " . $fila["cliente"];

        // Si estamos editando, marcar como selected la reserva que ya tenía este servicio
        $selected = ($modoEdicion && (int)$fila["id"] === (int)$servicioEditar["id_reserva"]) ? "selected" : "";

        $optionsReservas .= "<option value='" . (int)$fila["id"] . "' $selected>" . htmlspecialchars($texto) . "</option>";
    }
}

cerrarConexion($conexion);

// Preparar textos y valores según el modo
if ($modoEdicion) {
    $tituloPagina    = "Editar servicio #" . (int)$servicioEditar["id"];
    $iconoTitulo     = "bi-pencil-square";
    $actionForm      = "proceso_alta_servicio.php";
    $textoBoton      = "Guardar cambios";
    $valFecha        = htmlspecialchars($servicioEditar["fecha"]);
    $valDescripcion  = htmlspecialchars($servicioEditar["descripcion"]);
    $valTiempo       = (int)$servicioEditar["tiempo_estimado"];
    $valPrecio       = number_format((float)$servicioEditar["precio_base"], 2, ".", "");
    $valGarantia     = ($servicioEditar["garantia"] != null) ? htmlspecialchars($servicioEditar["garantia"]) : "";
    $valTerminada    = ((int)$servicioEditar["terminada"] === 1);

    // Autodetección del tipo MIME de la imagen actual si existe
    $mimeActual = "image/jpeg";
    if (!empty($servicioEditar["imagen"])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectado = $finfo->buffer($servicioEditar["imagen"]);
        if ($detectado) {
            $mimeActual = $detectado;
        }
    }
} else {
    $tituloPagina    = "Alta de servicio";
    $iconoTitulo     = "bi-wrench-adjustable";
    $actionForm      = "proceso_alta_servicio.php";
    $textoBoton      = "Aceptar";
    $valFecha        = date("Y-m-d");
    $valDescripcion  = "";
    $valTiempo       = "";
    $valPrecio       = "";
    $valGarantia     = "";
    $valTerminada    = false;
    $mimeActual      = "";
}

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-3 d-flex align-items-center gap-2">
        <i class="bi <?php echo $iconoTitulo; ?>" aria-hidden="true"></i>
        <span><?php echo $tituloPagina; ?></span>
    </h2>

    <?php if (!$hayReservas): ?>
        <div class="alert alert-warning" role="alert">
            No existen reservas registradas en la base de datos.
            <a href="alta_reserva.php" class="alert-link">Crea primero una reserva aquí</a> antes de añadir un servicio.
        </div>
    <?php else: ?>
        <form action="<?php echo $actionForm; ?>" name="frmAltaServicio" id="frmAltaServicio" method="POST" enctype="multipart/form-data">
            <?php if ($modoEdicion): ?>
                <input type="hidden" name="id_servicio" value="<?php echo (int)$servicioEditar["id"]; ?>">
            <?php endif; ?>

            <fieldset>
                <!-- Fila 1 -->
                <div class="row g-3 mb-2">
                    <div class="col-12 col-lg-6">
                        <label for="id_reserva" class="form-label mb-1">Reserva asociada</label>
                        <select name="id_reserva" id="id_reserva" class="form-select" required>
                            <option value="" <?php echo !$modoEdicion ? "selected" : ""; ?> disabled>-- Seleccione reserva --</option>
                            <?php echo $optionsReservas; ?>
                        </select>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="precio_base" class="form-label mb-1">Precio base (€)</label>
                        <input type="number" class="form-control" id="precio_base" name="precio_base" min="0" max="999999.99" step="0.01"
                               placeholder="0.00" value="<?php echo $valPrecio; ?>" required>
                    </div>
                </div>

                <!-- Fila 2 -->
                <div class="row g-3 mb-2">
                    <div class="col-12 col-lg-6">
                        <label for="fecha" class="form-label mb-1">Fecha del servicio</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"
                               value="<?php echo $valFecha; ?>" required>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="garantia" class="form-label mb-1">Garantía (opcional)</label>
                        <input type="text" class="form-control" id="garantia" name="garantia" maxlength="255"
                               placeholder="Ej. 6 meses, 1 año..." value="<?php echo $valGarantia; ?>">
                    </div>
                </div>

                <!-- Fila 3 -->
                <div class="row g-3 mb-2">
                    <div class="col-12 col-lg-6">
                        <label for="descripcion" class="form-label mb-1">Descripción del servicio</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="500"
                               placeholder="Ej. Cambio de aceite y filtro" value="<?php echo $valDescripcion; ?>" required>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="imagen" class="form-label mb-1">
                            <?php echo $modoEdicion ? "Nueva imagen (máx. 2 MB; dejar vacío para mantener la actual)" : "Imagen del servicio (opcional; JPG, PNG o WEBP, máx. 2 MB)"; ?>
                        </label>
                        <input type="file" class="form-control" id="imagen" name="imagen"
                               accept="image/jpeg,image/png,image/webp">

                        <?php if ($modoEdicion && !empty($servicioEditar["imagen"])): ?>
                            <div class="mt-2">
                                <small class="text-muted d-block mb-1">Imagen actual:</small>
                                <img src="data:<?php echo htmlspecialchars($mimeActual); ?>;base64,<?php echo base64_encode($servicioEditar["imagen"]); ?>"
                                     alt="Imagen actual" class="img-thumbnail" style="max-height: 90px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fila 4: Tiempo estimado y Servicio completado alineados -->
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-6">
                        <label for="tiempo_estimado" class="form-label mb-1">Tiempo estimado (minutos)</label>
                        <input type="number" class="form-control" id="tiempo_estimado" name="tiempo_estimado" min="1" max="10000" step="1"
                               placeholder="Ej. 45" value="<?php echo $valTiempo; ?>" required>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="terminada" name="terminada" value="1"
                                   <?php echo $valTerminada ? "checked" : ""; ?>>
                            <label class="form-check-label" for="terminada">Servicio completado</label>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="row g-2 mt-3">
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-secondary btn-lg btn-hero px-4 fw-semibold w-100">
                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                            <?php echo $textoBoton; ?>
                        </button>
                    </div>
                    <div class="col-12 col-md-auto">
                        <?php if ($modoEdicion): ?>
                            <a href="buscar_servicio.php" class="btn btn-outline-secondary btn-lg btn-hero px-4 fw-semibold w-100">
                                <i class="bi bi-x-lg me-1" aria-hidden="true"></i>
                                Cancelar
                            </a>
                        <?php else: ?>
                            <button type="reset" class="btn btn-outline-danger btn-lg btn-hero px-4 fw-semibold w-100">
                                <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>
                                Limpiar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php endif; ?>
</div>

<?php include_once("pie.html"); ?>