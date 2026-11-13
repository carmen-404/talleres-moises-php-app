<?php
require_once("funcionesBD.php");

$conexion = obtenerConexion();

$sql = "SELECT c.id, c.matricula, c.marca, c.modelo, c.anio, c.fecha_entrada, c.reparar,
               cl.nombre_completo
        FROM COCHE c
        LEFT JOIN CLIENTE cl ON c.id_cliente = cl.id
        WHERE c.deleted_at IS NULL
        ORDER BY c.fecha_entrada DESC;";

$resultado = mysqli_query($conexion, $sql);

$filas = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $ruta = "uploads/coches/" . $fila["id"];
    if (file_exists($ruta)) {
        $imagen = "<img src='" . $ruta . "' alt='Imagen del coche' class='rounded border'
                        style='width:70px; height:70px; object-fit:cover;'>";
    } else {
        $imagen = "<div class='rounded border bg-light text-muted d-flex align-items-center justify-content-center'
                        style='width:70px; height:70px;'>
                        <i class='bi bi-image fs-4'></i>
                   </div>";
    }

    if ($fila["nombre_completo"] == null) {
        $cliente = "<span class='text-muted'>Sin cliente</span>";
    } else {
        $cliente = htmlspecialchars($fila["nombre_completo"]);
    }

    if ($fila["reparar"] == 1) {
        $reparar = "<span class='badge text-bg-warning'>Sí</span>";
    } else {
        $reparar = "<span class='badge text-bg-secondary'>No</span>";
    }

    $filas .= "<tr>";
    $filas .= "<td>" . $imagen . "</td>";
    $filas .= "<td>" . htmlspecialchars($fila["matricula"]) . "</td>";
    $filas .= "<td>" . htmlspecialchars($fila["marca"]) . "</td>";
    $filas .= "<td>" . htmlspecialchars($fila["modelo"]) . "</td>";
    $filas .= "<td>" . $fila["anio"] . "</td>";
    $filas .= "<td>" . $fila["fecha_entrada"] . "</td>";
    $filas .= "<td>" . $reparar . "</td>";
    $filas .= "<td>" . $cliente . "</td>";
    $filas .= "<td>";
    $filas .= "<a href='editar_coche.php?id=" . $fila["id"] . "' class='btn btn-sm btn-outline-primary me-1'><i class='bi bi-pencil'></i></a>";
    $filas .= "<a href='proceso_borrar_coche.php?id=" . $fila["id"] . "' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('¿Seguro que quieres borrar este coche?');\"><i class='bi bi-trash'></i></a>";
    $filas .= "</td>";
    $filas .= "</tr>";
}


include_once("cabecera.html");
?>

<div class="container mt-4" id="listados">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-car-front" aria-hidden="true"></i>
        <span>Listado de coches</span>
    </h2>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Matrícula</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Fecha de entrada</th>
                    <th>Reparar</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $filas; ?>
            </tbody>
        </table>
    </div>

    <a href="alta_coche.php" class="btn btn-secondary px-4 fw-semibold">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
        Nuevo coche
    </a>
</div>
</body>

</html>