<?php
require_once("config.php");

$conexion = obtenerConexion();

$sql = "SELECT s.id,
               DATE_FORMAT(s.fecha, '%d/%m/%Y') AS fecha_formateada,
               s.descripcion,
               s.tiempo_estimado,
               s.garantia,
               s.precio_base,
               s.id_reserva,
               s.terminada,
               s.imagen,
               COALESCE(co.matricula, r.snapshot_coche_matricula) AS matricula
        FROM SERVICIO s
        INNER JOIN RESERVA r ON s.id_reserva = r.id
        LEFT JOIN COCHE co ON r.id_coche = co.id
        ORDER BY s.fecha DESC, s.id DESC";

$resultado = mysqli_query($conexion, $sql);
$totalRegistros = ($resultado) ? mysqli_num_rows($resultado) : 0;

include_once("cabecera.html");
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-primary fw-normal mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-list-ul" aria-hidden="true"></i>
            <span>Listado de servicios</span>
        </h2>
        <span class="text-muted">
            Total: <strong><?php echo $totalRegistros; ?></strong> servicio(s)
        </span>
    </div>

    <?php if ($totalRegistros === 0): ?>
        <div class="alert alert-warning shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No existen servicios registrados en la base de datos.
            <a href="alta_servicio.php" class="alert-link">Da de alta un servicio aquí</a>.
        </div>
    <?php else: ?>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Reserva / Matrícula</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Tiempo</th>
                        <th scope="col">Precio base</th>
                        <th scope="col">Garantía</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    while ($fila = mysqli_fetch_assoc($resultado)):
                        $garantia = ($fila["garantia"] !== null && $fila["garantia"] !== "")
                            ? htmlspecialchars($fila["garantia"])
                            : "<span class='text-muted'>Sin garantía</span>";

                        $estadoBadge = ((int)$fila["terminada"] === 1)
                            ? "<span class='badge bg-success'>Completado</span>"
                            : "<span class='badge bg-warning text-dark'>Pendiente</span>";

                        $imgHtml = "<span class='text-muted small'>Sin imagen</span>";
                        if (!empty($fila["imagen"])) {
                            $mime = $finfo->buffer($fila["imagen"]) ?: "image/jpeg";
                            $base64 = base64_encode($fila["imagen"]);
                            $imgHtml = "<img src='data:" . htmlspecialchars($mime) . ";base64,$base64' alt='Servicio #" . (int)$fila["id"] . "' class='img-thumbnail' style='width: 65px; height: 65px; object-fit: cover;'>";
                        }
                    ?>
                        <tr>
                            <th scope="row">#<?php echo (int)$fila["id"]; ?></th>
                            <td><?php echo $imgHtml; ?></td>
                            <td><?php echo htmlspecialchars($fila["fecha_formateada"]); ?></td>
                            <td>
                                #<?php echo (int)$fila["id_reserva"]; ?> ·
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($fila["matricula"]); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($fila["descripcion"]); ?></td>
                            <td><?php echo (int)$fila["tiempo_estimado"]; ?> min</td>
                            <td class="fw-semibold"><?php echo number_format((float)$fila["precio_base"], 2, ",", "."); ?> €</td>
                            <td><?php echo $garantia; ?></td>
                            <td><?php echo $estadoBadge; ?></td>
                            <td class="text-center text-nowrap">
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="alta_servicio.php" method="POST" class="d-inline">
                                        <input type="hidden" name="id_servicio" value="<?php echo (int)$fila["id"]; ?>">
                                        <button type="submit" class="btn btn-sm btn-warning fw-semibold">
                                            <i class="bi bi-pencil-square me-1"></i> Editar
                                        </button>
                                    </form>
                                    <form action="proceso_borrar_servicio.php" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');">
                                        <input type="hidden" name="id_servicio" value="<?php echo (int)$fila["id"]; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                                            <i class="bi bi-trash me-1"></i> Borrar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
cerrarConexion($conexion);
include_once("pie.html");
?>