<?php
require_once("funcionesBD.php");

$conexion = obtenerConexion();

// Texto que se ha buscado (vacío si no se ha buscado nada)
$texto = "";
if (isset($_GET["txtBuscar"])) {
    $texto = $_GET["txtBuscar"];
}
$textoSql = mysqli_real_escape_string($conexion, $texto);

$sql = "SELECT c.id, c.matricula, c.marca, c.modelo, c.anio, c.fecha_entrada, c.reparar,
               cl.nombre_completo
        FROM COCHE c
        LEFT JOIN CLIENTE cl ON c.id_cliente = cl.id
        WHERE c.deleted_at IS NULL
          AND (c.matricula LIKE '%$textoSql%'
               OR c.marca LIKE '%$textoSql%'
               OR c.modelo LIKE '%$textoSql%'
               OR cl.nombre_completo LIKE '%$textoSql%')
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
    $filas .= "</tr>";
}

// Cabecera HTML que incluye navbar
include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-search" aria-hidden="true"></i>
        <span>Buscar coche</span>
    </h2>

    <!-- Buscador -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="buscar_coche.php" name="frmBuscarCoche" id="frmBuscarCoche" method="get">
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input id="txtBuscar" name="txtBuscar" class="form-control" type="text"
                           value="<?php echo htmlspecialchars($texto); ?>"
                           placeholder="Matrícula, marca, modelo o cliente...">
                    <button type="submit" id="btnBuscar" class="btn btn-secondary px-4 fw-semibold">Buscar</button>
                    <a href="buscar_coche.php" class="btn btn-outline-danger">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
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
                    </tr>
                </thead>
                <tbody>
                    <?php echo $filas; ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
</body>

</html>