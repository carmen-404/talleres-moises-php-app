<?php
include_once("buscar_servicio.php");

require_once("config.php");

$conexion = obtenerConexion();

$idServicio = (int)$_POST["id_servicio"];

$sql = "SELECT id, descripcion,
               tiempo_estimado,
               garantia,
               precio_base,
               id_reserva,
               imagen
        FROM SERVICIO
        WHERE id = $idServicio";

$resultado = mysqli_query($conexion, $sql);
$servicio = ($resultado && mysqli_num_rows($resultado) > 0) ? mysqli_fetch_assoc($resultado) : null;

cerrarConexion($conexion);
?>
<div class="container mt-4">
    <?php if (!$servicio): ?>
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No se ha encontrado ningún servicio con ese identificador.
        </div>
    <?php else: ?>
        <?php
            if ($servicio["garantia"] != null && $servicio["garantia"] != "") {
                $garantia = htmlspecialchars($servicio["garantia"]);
            } else {
                $garantia = "Sin garantía";
            }
        ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md-6">
                        <h4 class="card-title text-primary mb-3">
                            #<?php echo (int)$servicio["id"]; ?> · <?php echo htmlspecialchars($servicio["descripcion"]); ?>
                        </h4>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <strong>ID de reserva asociada:</strong>
                                #<?php echo (int)$servicio["id_reserva"]; ?>
                            </li>
                            <li class="list-group-item px-0">
                                <strong>Tiempo estimado:</strong>
                                <?php echo (int)$servicio["tiempo_estimado"]; ?> minutos
                            </li>
                            <li class="list-group-item px-0">
                                <strong>Precio base:</strong>
                                <?php echo number_format((float)$servicio["precio_base"], 2, ",", "."); ?> €
                            </li>
                            <li class="list-group-item px-0">
                                <strong>Garantía:</strong>
                                <?php echo $garantia; ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-12 col-md-4 text-center">
                        <?php if (!empty($servicio["imagen"])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($servicio["imagen"]); ?>"
                                alt="Imagen del servicio"
                                class="img-fluid rounded border shadow-sm"
                                style="max-height: 220px; object-fit: cover;">
                        <?php else: ?>
                            <div class="border rounded p-4 text-muted bg-light">
                                <i class="bi bi-image fs-1 d-block mb-2"></i>
                                Sin imagen adjunta
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="d-flex flex-column gap-2">
                            <form action="alta_servicio.php" method="POST">
                                <input type="hidden" name="id_servicio" value="<?php echo (int)$servicio["id"]; ?>">
                                <button type="submit" class="btn btn-warning fw-semibold w-100">
                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                </button>
                            </form>
                            <form action="proceso_borrar_servicio.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');">
                                <input type="hidden" name="id_servicio" value="<?php echo (int)$servicio["id"]; ?>">
                                <button type="submit" class="btn btn-danger fw-semibold w-100">
                                    <i class="bi bi-trash me-1"></i> Borrar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>
<?php include_once("pie.html"); ?>