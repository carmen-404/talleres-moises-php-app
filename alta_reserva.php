<?php
require_once("config.php");

$conexion = obtenerConexion();

$sql = "SELECT c.id, c.matricula, cl.email
        FROM COCHE c
        INNER JOIN CLIENTE cl ON c.id_cliente = cl.id
        WHERE c.deleted_at IS NULL AND cl.deleted_at IS NULL
        ORDER BY c.matricula ASC;";

$resultado = mysqli_query($conexion, $sql);

$options = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $textoOpcion = htmlspecialchars($fila["matricula"] . " - " . $fila["email"]);
    $options .= " <option value='" . $fila["id"] . "'>" . $textoOpcion . "</option>";
}

cerrarConexion($conexion);

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-calendar-check" aria-hidden="true"></i>
        <span>Alta de reserva</span>
    </h2>

    <?php if (isset($_GET["mensaje"])) { ?>
        <div class="alert alert-<?php echo htmlspecialchars($_GET["tipo"] ?? "info"); ?> mb-4" role="alert">
            <?php echo htmlspecialchars($_GET["mensaje"]); ?>
        </div>
    <?php } ?>

    <form action="proceso_alta_reserva.php" name="frmAltaReserva" id="frmAltaReserva" method="post" enctype="multipart/form-data">

        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-calendar-check me-1" aria-hidden="true"></i>
                Datos de la reserva
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="lstCoche">Matrícula</label>
                        <select name="lstCoche" id="lstCoche" class="form-select" required>
                            <option value="">-- Seleccione matrícula --</option>
                            <?php echo $options; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtDia">Día de la reserva</label>
                        <input id="txtDia" name="txtDia" class="form-control" type="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="lstFranja">Franja horaria</label>
                        <select name="lstFranja" id="lstFranja" class="form-select" required>
                            <option value="MANANA">Mañana</option>
                            <option value="TARDE">Tarde</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="lstPrioridad">Prioridad (1 = Máxima, 4 = Mínima)</label>
                        <select name="lstPrioridad" id="lstPrioridad" class="form-select" required>
                            <option value="1">1 - Urgente</option>
                            <option value="2">2 - Alta</option>
                            <option value="3">3 - Media</option>
                            <option value="4" selected>4 - Normal</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="chkConfirmada" id="chkConfirmada" value="1">
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
                <label class="form-label" for="imagen">Imagen de la reserva (opcional, máx. 2 MB)</label>
                <input id="imagen" name="imagen" class="form-control" type="file" accept="image/*">
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-12 col-md-auto">
                <button
                    type="submit"
                    id="btnAceptarAltaReserva"
                    name="btnAceptarAltaReserva"
                    class="btn btn-secondary btn-lg btn-hero px-4 fw-semibold w-100">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                    Aceptar
                </button>
            </div>

            <div class="col-12 col-md-auto">
                <button
                    type="reset"
                    class="btn btn-outline-danger btn-lg btn-hero px-4 fw-semibold w-100">
                    <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>
                    Reiniciar
                </button>
            </div>
        </div>
    </form>
</div>
<?php include_once("pie.html"); ?>
