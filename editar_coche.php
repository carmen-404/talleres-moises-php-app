<?php
require_once("config.php");

// Recoger el id de la URL
$id = 0;
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
}

// Si no hay un id válido, volver al listado
if ($id <= 0) {
    header("Location: listado_coches.php");
    exit;
}

$conexion = obtenerConexion();

// Cargar el coche (si existe y no está borrado)
$stmt = mysqli_prepare($conexion, "SELECT id, matricula, marca, modelo, anio, fecha_entrada, reparar, id_cliente
                                   FROM COCHE WHERE id = ? AND deleted_at IS NULL;");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$coche = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Si no existe, volver al listado
if ($coche == null) {
    header("Location: listado_coches.php");
    exit;
}

// Lista de clientes (el del coche sale seleccionado)
$sql = "SELECT id, nombre_completo FROM CLIENTE WHERE deleted_at IS NULL ORDER BY nombre_completo;";
$resultado = mysqli_query($conexion, $sql);

$options = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $selected = "";
    if ($fila["id"] == $coche["id_cliente"]) {
        $selected = " selected";
    }
    $options .= " <option value='" . $fila["id"] . "'" . $selected . ">" . htmlspecialchars($fila["nombre_completo"]) . "</option>";
}

cerrarConexion($conexion);

// Imagen actual del coche (si tiene)
// El ?v= con la fecha del archivo evita que el navegador enseñe la antigua guardada en caché
$ruta = "uploads/coches/" . $id;
$imagenActual = "";
if (file_exists($ruta)) {
    $imagenActual = $ruta . "?v=" . filemtime($ruta);
}

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-pencil-square" aria-hidden="true"></i>
        <span>Editar coche</span>
    </h2>

    <form action="proceso_modificar_coche.php" name="frmEditarCoche" id="frmEditarCoche" method="post" enctype="multipart/form-data">

        <!-- Id del coche -->
        <input type="hidden" name="hidId" value="<?php echo $coche['id']; ?>">

        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-car-front me-1" aria-hidden="true"></i>
                Datos del vehículo
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtMatricula">Matrícula</label>
                        <input id="txtMatricula" name="txtMatricula" class="form-control" type="text" maxlength="15" placeholder="Ej: 1234ABC"
                               value="<?php echo htmlspecialchars($coche['matricula']); ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtAnio">Año</label>
                        <input id="txtAnio" name="txtAnio" class="form-control" type="number" min="1900" max="<?php echo date('Y'); ?>" placeholder="Año de matriculación"
                               value="<?php echo $coche['anio']; ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtMarca">Marca</label>
                        <input id="txtMarca" name="txtMarca" class="form-control" type="text" maxlength="100" placeholder="Marca del coche"
                               value="<?php echo htmlspecialchars($coche['marca']); ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="txtModelo">Modelo</label>
                        <input id="txtModelo" name="txtModelo" class="form-control" type="text" maxlength="100" placeholder="Modelo del coche"
                               value="<?php echo htmlspecialchars($coche['modelo']); ?>" required>
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
                        <input id="txtFechaEntrada" name="txtFechaEntrada" class="form-control" type="date"
                               value="<?php echo $coche['fecha_entrada']; ?>" required>
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
                            <input class="form-check-input" type="checkbox" name="chkReparar" id="chkReparar" value="1"
                                   <?php if ($coche['reparar'] == 1) { echo "checked"; } ?>>
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
                <?php if ($imagenActual != "") { ?>
                    <img src="<?php echo $imagenActual; ?>" alt="Imagen actual del coche" class="rounded border mb-3"
                         style="width:120px; height:120px; object-fit:cover;">
                <?php } ?>
                <label class="form-label d-block" for="imagen">
                    <?php echo ($imagenActual != "") ? "Cambiar imagen (opcional: si no eliges ninguna se conserva la actual, máx. 2 MB)" : "Imagen del coche (opcional, máx. 2 MB)"; ?>
                </label>
                <input id="imagen" name="imagen" class="form-control" type="file" accept="image/*">
            </div>
        </div>


        <div class="row g-2 mb-4">
            <div class="col-12 col-md-auto">
                <button
                    type="submit"
                    id="btnAceptarEditarCoche"
                    name="btnAceptarEditarCoche"
                    class="btn btn-secondary btn-lg btn-hero px-4 fw-semibold w-100">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                    Guardar cambios
                </button>
            </div>

            <div class="col-12 col-md-auto">
                <a href="listado_coches.php"
                   class="btn btn-outline-danger btn-lg btn-hero px-4 fw-semibold w-100">
                    <i class="bi bi-x-lg me-1" aria-hidden="true"></i>
                    Cancelar
                </a>
            </div>
        </div>
    </form>
</div>
<?php include_once("pie.html"); ?>

