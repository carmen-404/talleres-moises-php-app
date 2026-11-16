<?php
require_once("config.php");

$conexion = obtenerConexion();

$esEdicion = (isset($_POST["id_servicio"]) && $_POST["id_servicio"] != "");
$idServicio = $esEdicion ? (int)$_POST["id_servicio"] : 0;

$idReserva      = (int)$_POST["id_reserva"];
$descripcion    = mysqli_real_escape_string($conexion, trim($_POST["descripcion"]));
$tiempoEstimado = (int)$_POST["tiempo_estimado"];
$precioBase     = (float)$_POST["precio_base"];

if (isset($_POST["garantia"]) && trim($_POST["garantia"]) != "") {
    $garantia = "'" . mysqli_real_escape_string($conexion, trim($_POST["garantia"])) . "'";
} else {
    $garantia = "NULL";
}

$hayImagenNueva = false;
$imagenBinaria  = null;

if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
    $datosArchivo  = file_get_contents($_FILES["imagen"]["tmp_name"]);
    $imagenBinaria = mysqli_real_escape_string($conexion, $datosArchivo);
    $hayImagenNueva = true;
}
if ($esEdicion) {
    if ($hayImagenNueva) {
        $sql = "UPDATE SERVICIO
                SET id_reserva = $idReserva,
                    descripcion = '$descripcion',
                    tiempo_estimado = $tiempoEstimado,
                    precio_base = $precioBase,
                    garantia = $garantia,
                    imagen = '$imagenBinaria'
                WHERE id = $idServicio";
    } else {
        // Si no sube imagen nueva, conservamos la que ya tenía
        $sql = "UPDATE SERVICIO
                SET id_reserva = $idReserva,
                    descripcion = '$descripcion',
                    tiempo_estimado = $tiempoEstimado,
                    precio_base = $precioBase,
                    garantia = $garantia
                WHERE id = $idServicio";
    }
} else {
    $valorImagen = $hayImagenNueva ? "'$imagenBinaria'" : "NULL";

    $sql = "INSERT INTO SERVICIO (id_reserva, descripcion, tiempo_estimado, precio_base, garantia, imagen)
            VALUES ($idReserva, '$descripcion', $tiempoEstimado, $precioBase, $garantia, $valorImagen)";
}

$exito = mysqli_query($conexion, $sql);
$errorSql = mysqli_error($conexion);

cerrarConexion($conexion);

include_once("cabecera.html");
?>

<div class="container mt-4">
    <?php if ($exito): ?>
        <div class="alert alert-success shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo $esEdicion ? "Servicio actualizado correctamente." : "Servicio registrado correctamente."; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Error al <?php echo $esEdicion ? "actualizar" : "registrar"; ?> el servicio: <?php echo htmlspecialchars($errorSql); ?>
        </div>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-3">
        <a href="alta_servicio.php" class="btn btn-secondary btn-hero px-4 fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Nuevo servicio
        </a>
        <a href="buscar_servicio.php" class="btn btn-outline-primary btn-hero px-4 fw-semibold">
            <i class="bi bi-search me-1"></i> Buscar servicio
        </a>
        <a href="listado_servicios.php" class="btn btn-outline-primary btn-hero px-4 fw-semibold">
            <i class="bi bi-list-ul me-1"></i> Ver listado
        </a>
    </div>
</div>

<?php include_once("pie.html"); ?>