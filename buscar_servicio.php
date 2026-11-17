<?php
require_once("config.php");

$conexion = obtenerConexion();

$idSeleccionado = isset($_POST["id_servicio"]) ? (int)$_POST["id_servicio"] : 0;

$sql = "SELECT id,
               DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada,
               descripcion
        FROM SERVICIO
        ORDER BY id DESC";

$resultado = mysqli_query($conexion, $sql);
$hayServicios = ($resultado && mysqli_num_rows($resultado) > 0);

$optionsServicios = "";
if ($hayServicios) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $texto = "#" . $fila["id"] . " · " . $fila["fecha_formateada"] . " · " . $fila["descripcion"];
        $selected = ((int)$fila["id"] === $idSeleccionado) ? "selected" : "";
        $optionsServicios .= "<option value='" . (int)$fila["id"] . "' $selected>" . htmlspecialchars($texto) . "</option>";
    }
}

cerrarConexion($conexion);

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-wrench-adjustable" aria-hidden="true"></i>
        <span>Buscar por servicio</span>
    </h2>

    <?php if (!$hayServicios): ?>
        <div class="alert alert-warning" role="alert">
            No existen servicios registrados en la base de datos.
            <a href="alta_servicio.php" class="alert-link">Da de alta un servicio aquí</a>.
        </div>
    <?php else: ?>
        <form action="proceso_buscar_servicio.php" name="frmBuscarServicio" id="frmBuscarServicio" method="POST">
            <fieldset>
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-7 col-lg-6">
                        <label for="id_servicio" class="form-label mb-1">Seleccione el servicio</label>
                        <select name="id_servicio" id="id_servicio" class="form-select" required>
                            <option value="" <?php echo ($idSeleccionado === 0) ? "selected" : ""; ?> disabled>-- Seleccione servicio --</option>
                            <?php echo $optionsServicios; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-secondary btn-hero px-4 fw-semibold w-100">
                            <i class="bi bi-search me-1" aria-hidden="true"></i>
                            Buscar
                        </button>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php endif; ?>  
</div>
<?php
if (!isset($_POST["id_servicio"])) {
    include_once("pie.html");
}
?>