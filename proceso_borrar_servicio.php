<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["id_servicio"])) {
    header("Location: buscar_servicio.php");
    exit;
}

require_once("config.php");

$conexion = obtenerConexion();

$idServicio = (int)$_POST["id_servicio"];

$sql = "DELETE FROM SERVICIO WHERE id = $idServicio";
$queryOk = mysqli_query($conexion, $sql);
$filasAfectadas = mysqli_affected_rows($conexion);
$errorSql = mysqli_error($conexion);

cerrarConexion($conexion);

include_once("cabecera.html");
?>

<div class="container mt-4">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-trash" aria-hidden="true"></i>
        <span>Eliminar servicio</span>
    </h2>

    <?php if ($queryOk && $filasAfectadas > 0): ?>
        <div class="alert alert-success shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            El servicio <strong>#<?php echo $idServicio; ?></strong> se ha eliminado correctamente.
        </div>
    <?php elseif ($queryOk && $filasAfectadas === 0): ?>
        <div class="alert alert-warning shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            No existe ningún servicio con el identificador <strong>#<?php echo $idServicio; ?></strong>.
        </div>
    <?php else: ?>
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Error al eliminar el servicio: <?php echo htmlspecialchars($errorSql); ?>
        </div>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-3">
        <a href="buscar_servicio.php" class="btn btn-secondary btn-hero px-4 fw-semibold">
            <i class="bi bi-search me-1"></i> Buscar servicio
        </a>
        <a href="listado_servicios.php" class="btn btn-outline-primary btn-hero px-4 fw-semibold">
            <i class="bi bi-list-ul me-1"></i> Ver listado
        </a>
    </div>
</div>

<?php include_once("pie.html"); ?>