<?php
require_once("config.php");

$id = 0;
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
}

if ($id <= 0) {
    header("Location: listado_reservas.php");
    exit;
}

$conexion = obtenerConexion();

$stmt = mysqli_prepare($conexion, "SELECT id, dia, franja_horaria, snapshot_cliente_email, snapshot_coche_matricula, prioridad_reserva, esta_confirmada
                                   FROM RESERVA WHERE id = ?;");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$reserva = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

cerrarConexion($conexion);

if ($reserva == null) {
    header("Location: listado_reservas.php");
    exit;
}

$snapshotJunto = $reserva["snapshot_coche_matricula"] . " - " . $reserva["snapshot_cliente_email"];

$ruta = "uploads/reservas/" . $id;
$imagenActual = "";
if (file_exists($ruta)) {
    $imagenActual = $ruta . "?v=" . filemtime($ruta);
}

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-pencil-square" aria-hidden="true"></i>
        <span>Editar reserva</span>
    </h2>

    <?php if (isset($_GET["mensaje"])) { ?>
        <div class="alert alert-<?php echo htmlspecialchars($_GET["tipo"] ?? "danger"); ?> mb-4" role="alert">
            <?php echo htmlspecialchars($_GET["mensaje"]); ?>
        </div>
    <?php } ?>

    <form action="proceso_modificar_reserva.php" name="frmEditarReserva" id="frmEditarReserva" method="post" enctype="multipart/form-data">

        <input type="hidden" name="hidId" value="<?php echo $reserva['id']; ?>">

        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-calendar-check me-1" aria-hidden="true"></i>
                Datos de la reserva
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtSnapshotVehiculoCliente">Matrícula</label>
                        <input id="txtSnapshotVehiculoCliente" class="form-control" type="text"
                               value="<?php echo htmlspecialchars($snapshotJunto); ?>" readonly>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtDia">Día de la reserva</label>
                        <input id="txtDia" name="txtDia" class="form-control" type="date"
                               value="<?php echo $reserva['dia']; ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="lstFranja">Franja horaria</label>
                        <select name="lstFranja" id="lstFranja" class="form-select" required>
                            <option value="MANANA" <?php if ($reserva['franja_horaria'] == 'MANANA') { echo "selected"; } ?>>Mañana</option>
                            <option value="TARDE" <?php if ($reserva['franja_horaria'] == 'TARDE') { echo "selected"; } ?>>Tarde</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="lstPrioridad">Prioridad (1 = Máxima, 4 = Mínima)</label>
                        <select name="lstPrioridad" id="lstPrioridad" class="form-select" required>
                            <option value="1" <?php if ($reserva['prioridad_reserva'] == 1) { echo "selected"; } ?>>1 - Urgente</option>
                            <option value="2" <?php if ($reserva['prioridad_reserva'] == 2) { echo "selected"; } ?>>2 - Alta</option>
                            <option value="3" <?php if ($reserva['prioridad_reserva'] == 3) { echo "selected"; } ?>>3 - Media</option>
                            <option value="4" <?php if ($reserva['prioridad_reserva'] == 4) { echo "selected"; } ?>>4 - Normal</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="chkConfirmada" id="chkConfirmada" value="1"
                                   <?php if ($reserva['esta_confirmada'] == 1) { echo "checked"; } ?>>
                            <label class="form-check-label" for="chkConfirmada">
                                Reserva confirmada
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-image me-1" aria-hidden="true"></i>
                Imagen
            </div>
            <div class="card-body">
                <?php if ($imagenActual != "") { ?>
                    <img src="<?php echo $imagenActual; ?>" alt="Imagen actual de la reserva" class="rounded border mb-3"
                         style="width:120px; height:120px; object-fit:cover;">
                <?php } ?>
                <label class="form-label d-block" for="imagen">
                    <?php echo ($imagenActual != "") ? "Cambiar imagen (opcional: si no eliges ninguna se conserva la actual, máx. 2 MB)" : "Imagen de la reserva (opcional, máx. 2 MB)"; ?>
                </label>
                <input id="imagen" name="imagen" class="form-control" type="file" accept="image/*">
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-12 col-md-auto">
                <button
                    type="submit"
                    id="btnAceptarEditarReserva"
                    name="btnAceptarEditarReserva"
                    class="btn btn-secondary btn-lg btn-hero px-4 fw-semibold w-100">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                    Guardar cambios
                </button>
            </div>

            <div class="col-12 col-md-auto">
                <a href="listado_reservas.php"
                   class="btn btn-outline-danger btn-lg btn-hero px-4 fw-semibold w-100">
                    <i class="bi bi-x-lg me-1" aria-hidden="true"></i>
                    Cancelar
                </a>
            </div>
        </div>
    </form>
</div>
<?php include_once("pie.html"); ?>
