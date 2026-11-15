<?php
require_once("config.php");

$conexion = obtenerConexion();

// Texto que se ha buscado (vacío si no se ha buscado nada)
$texto = "";
if (isset($_GET["txtBuscar"])) {
    $texto = $_GET["txtBuscar"];
}

$sql = "SELECT c.id, c.matricula, c.marca, c.modelo, c.anio, c.fecha_entrada, c.reparar,
               cl.nombre_completo
        FROM COCHE c
        LEFT JOIN CLIENTE cl ON c.id_cliente = cl.id
        WHERE c.deleted_at IS NULL
          AND (c.matricula LIKE '%$texto%'
               OR c.marca LIKE '%$texto%'
               OR c.modelo LIKE '%$texto%'
               OR cl.nombre_completo LIKE '%$texto%')
        ORDER BY c.matricula;";

$resultado = mysqli_query($conexion, $sql);
$total = mysqli_num_rows($resultado);

$filas = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    // Imagen en un cuadrado de 70x70
    $ruta = "uploads/coches/" . $fila["id"];
    if (file_exists($ruta)) {
        $imagen = "<img src='" . $ruta . "' class='rounded border' style='width:70px; height:70px; object-fit:cover;'>";
    } else {
        $imagen = "<div class='rounded border bg-light text-muted d-flex align-items-center justify-content-center' style='width:70px; height:70px;'><i class='bi bi-image fs-4'></i></div>";
    }

    // Cliente
    if ($fila["nombre_completo"] == null) {
        $cliente = "<span class='text-muted'>Sin cliente</span>";
    } else {
        $cliente = htmlspecialchars($fila["nombre_completo"]);
    }

    // Reparar
    if ($fila["reparar"] == 1) {
        $reparar = "<span class='badge text-bg-warning'>Sí</span>";
    } else {
        $reparar = "<span class='badge text-bg-secondary'>No</span>";
    }

    $filas .= "<tr>";
    $filas .= "<td>" . $imagen . "</td>";
    $filas .= "<td class='fw-semibold'>" . htmlspecialchars($fila["matricula"]) . "</td>";
    $filas .= "<td>" . htmlspecialchars($fila["marca"]) . " " . htmlspecialchars($fila["modelo"]) . "</td>";
    $filas .= "<td>" . $fila["anio"] . "</td>";
    $filas .= "<td>" . $fila["fecha_entrada"] . "</td>";
    $filas .= "<td>" . $reparar . "</td>";
    $filas .= "<td>" . $cliente . "</td>";
    $filas .= "<td>";
    $filas .= "<a href='editar_coche.php?id=" . $fila["id"] . "' class='btn btn-sm btn-outline-primary me-1'><i class='bi bi-pencil'></i></a>";
    $filas .= "<form class='d-inline' action='proceso_borrar_coche.php' method='post' onsubmit=\"return confirm('¿Seguro que quieres borrar este coche?');\">";
    $filas .= "<input type='hidden' name='id' value='" . $fila["id"] . "'>";
    $filas .= "<button type='submit' class='btn btn-sm btn-outline-danger'><i class='bi bi-trash'></i></button>";
    $filas .= "</form>";
    $filas .= "</td>";
    $filas .= "</tr>";
}

// Cabecera HTML que incluye navbar
include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-search" aria-hidden="true"></i>
        <span>Resultado de la búsqueda</span>
    </h2>

    <p class="text-muted">Búsqueda: «<?php echo htmlspecialchars($texto); ?>»</p>

    <?php if ($total == 0) { ?>
        <div class="alert alert-info">No se han encontrado coches.</div>
    <?php } else { ?>
        <p class="text-muted"><?php echo $total; ?> coche(s) encontrado(s)</p>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Matrícula</th>
                        <th>Coche</th>
                        <th>Año</th>
                        <th>Entrada</th>
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
    <?php } ?>

    <a href="buscar_coche.php" class="btn btn-secondary px-4 fw-semibold">
        <i class="bi bi-search me-1" aria-hidden="true"></i>
        Nueva búsqueda
    </a>
</div>
<?php include_once("pie.html"); ?>

