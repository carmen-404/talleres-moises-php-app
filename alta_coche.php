<?php
require_once("funcionesBD.php");

$conexion = obtenerConexion();

$sql = "SELECT id, nombre_completo FROM CLIENTE WHERE deleted_at IS NULL ORDER BY nombre_completo;";

$resultado = mysqli_query($conexion, $sql);

$options = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $options .= " <option value='" . $fila["id"] . "'>" . htmlspecialchars($fila["nombre_completo"]) . "</option>";
}

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-car-front" aria-hidden="true"></i>
        <span>Alta de coche</span>
    </h2>

    <form action="proceso_alta_coche.php" name="frmAltaCoche" id="frmAltaCoche" method="post" enctype="multipart/form-data">

        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-car-front me-1" aria-hidden="true"></i>
                Datos del vehículo
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtMatricula">Matrícula</label>
                        <input id="txtMatricula" name="txtMatricula" class="form-control" type="text" maxlength="15" placeholder="Ej: 1234ABC" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtAnio">Año</label>
                        <input id="txtAnio" name="txtAnio" class="form-control" type="number" min="1900" max="<?php echo date('Y'); ?>" placeholder="Año de matriculación" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtMarca">Marca</label>
                        <input id="txtMarca" name="txtMarca" class="form-control" type="text" maxlength="100" placeholder="Marca del coche" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtModelo">Modelo</label>
                        <input id="txtModelo" name="txtModelo" class="form-control" type="text" maxlength="100" placeholder="Modelo del coche" required>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-wrench-adjustable me-1" aria-hidden="true"></i>
                Entrada en el taller
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="txtFechaEntrada">Fecha de entrada</label>
                        <input id="txtFechaEntrada" name="txtFechaEntrada" class="form-control" type="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label" for="lstCliente">Cliente</label>
                        <select name="lstCliente" id="lstCliente" class="form-select" required>
                            <option value="">-- Seleccione cliente --</option>
                            <?php echo $options; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="chkReparar" id="chkReparar" value="1">
                            <label class="form-check-label" for="chkReparar">
                                Hay que repararlo
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
                <label class="form-label" for="imagen">Imagen del coche (opcional, máx. 2 MB)</label>
                <input id="imagen" name="imagen" class="form-control" type="file" accept="image/*">
            </div>
        </div>

        
        <div class="row g-2 mb-4">
            <div class="col-12 col-md-auto">
                <button
                    type="submit"
                    id="btnAceptarAltaCoche"
                    name="btnAceptarAltaCoche"
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
    </form>
</div>
</body>

</html>