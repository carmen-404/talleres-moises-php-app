<?php
require_once("config.php");

$conexion = obtenerConexion();

// Sin filtrar por deleted_at: independiente del tipo de borrado de COCHE
$sql = "SELECT c.id, c.matricula, c.marca, c.modelo, cl.email
        FROM COCHE c
        INNER JOIN CLIENTE cl ON c.id_cliente = cl.id
        ORDER BY c.matricula ASC;";

$resultado = mysqli_query($conexion, $sql);

$options = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $textoOpcion = htmlspecialchars($fila["matricula"] . " - " . $fila["email"]);
    $options .= " <option value='" . $fila["id"] . "'>" . $textoOpcion . "</option>";
}

// Cabecera HTML que incluye navbar
include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-calendar-check" aria-hidden="true"></i>
        <span>Alta de reserva</span>
    </h2>

    <form action="proceso_alta_reserva.php" name="frmAltaReserva" id="frmAltaReserva" method="post">
        <fieldset>
            <legend class="mb-3">Alta de reserva</legend>

            <div class="row g-3">
                <!-- Columna Izquierda -->
                <div class="col-12 col-lg-6">
                    <!-- Desplegable Matrícula -->
                    <div class="mb-3">
                        <label class="form-label" for="lstCoche">Matrícula</label>
                        <select name="lstCoche" id="lstCoche" class="form-select" required>
                            <option value="">-- Seleccione matrícula --</option>
                            <?php echo $options; ?>
                        </select>
                    </div>

                    <!-- Date input (dia) -->
                    <div class="mb-3">
                        <label class="form-label" for="txtDia">Día de la reserva</label>
                        <input id="txtDia" name="txtDia" class="form-control" type="date" required>
                    </div>

                    <!-- Select Franja Horaria -->
                    <div class="mb-3">
                        <label class="form-label" for="lstFranja">Franja horaria</label>
                        <select name="lstFranja" id="lstFranja" class="form-select" required>
                            <option value="MANANA">Mañana</option>
                            <option value="TARDE">Tarde</option>
                        </select>
                    </div>
                </div>

                <!-- Columna Derecha -->
                <div class="col-12 col-lg-6">
                    <!-- Select Prioridad -->
                    <div class="mb-3">
                        <label class="form-label" for="lstPrioridad">Prioridad (1 = Máxima, 4 = Mínima)</label>
                        <select name="lstPrioridad" id="lstPrioridad" class="form-select" required>
                            <option value="1">1 - Urgente</option>
                            <option value="2">2 - Alta</option>
                            <option value="3">3 - Media</option>
                            <option value="4" selected>4 - Normal</option>
                        </select>
                    </div>

                    <!-- Checkbox Confirmada -->
                    <div class="mb-3">
                        <label class="form-label d-block" for="chkConfirmada">Estado</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="chkConfirmada" id="chkConfirmada" value="1">
                            <label class="form-check-label" for="chkConfirmada">
                                Reserva confirmada
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="row g-2 mt-3">
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
                        Reset
                    </button>
                </div>
            </div>
        </fieldset>
    </form>
</div>
</body>

</html>