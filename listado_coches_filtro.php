<?php
require_once("config.php");

$conexion = obtenerConexion();

// Recoger los filtros (vacío si no se han rellenado)
$marca = "";
$idCliente = "";
$reparar = "";
$anioMin = "";
$anioMax = "";

if (isset($_GET["txtMarca"])) {
    $marca = $_GET["txtMarca"];
}
if (isset($_GET["lstCliente"])) {
    $idCliente = $_GET["lstCliente"];
}
if (isset($_GET["lstReparar"])) {
    $reparar = $_GET["lstReparar"];
}
if (isset($_GET["txtAnioMin"])) {
    $anioMin = $_GET["txtAnioMin"];
}
if (isset($_GET["txtAnioMax"])) {
    $anioMax = $_GET["txtAnioMax"];
}

// No validamos, suponemos que la entrada de datos es correcta
// Solo se añade la condición de los filtros que se han rellenado
$sql = "SELECT c.id, c.matricula, c.marca, c.modelo, c.anio, c.fecha_entrada, c.reparar,
               cl.nombre_completo
        FROM COCHE c
        LEFT JOIN CLIENTE cl ON c.id_cliente = cl.id
        WHERE c.deleted_at IS NULL";

if ($marca != "") {
    $sql .= " AND c.marca LIKE '%$marca%'";
}
if ($idCliente != "") {
    $sql .= " AND c.id_cliente = $idCliente";
}
if ($reparar != "") {
    $sql .= " AND c.reparar = $reparar";
}
if ($anioMin != "") {
    $sql .= " AND c.anio >= $anioMin";
}
if ($anioMax != "") {
    $sql .= " AND c.anio <= $anioMax";
}

$sql .= " ORDER BY c.matricula;";

$resultado = mysqli_query($conexion, $sql);
$total = mysqli_num_rows($resultado);

$filas = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $ruta = "uploads/coches/" . $fila["id"];
    if (file_exists($ruta)) {
        $imagen = "<img src='" . $ruta . "' alt='Imagen del coche' class='rounded border' style='width:70px; height:70px; object-fit:cover;'>";
    } else {
        $imagen = "<div class='rounded border bg-light text-muted d-flex align-items-center justify-content-center' style='width:70px; height:70px;'><i class='bi bi-image fs-4'></i></div>";
    }

    if ($fila["nombre_completo"] == null) {
        $cliente = "<span class='text-muted'>Sin cliente</span>";
    } else {
        $cliente = htmlspecialchars($fila["nombre_completo"]);
    }

    if ($fila["reparar"] == 1) {
        $badge = "<span class='badge text-bg-warning'>Sí</span>";
    } else {
        $badge = "<span class='badge text-bg-secondary'>No</span>";
    }

    $filas .= "<tr>";
    $filas .= "<td>" . $imagen . "</td>";
    $filas .= "<td class='fw-semibold'>" . htmlspecialchars($fila["matricula"]) . "</td>";
    $filas .= "<td>" . htmlspecialchars($fila["marca"]) . "</td>";
    $filas .= "<td>" . htmlspecialchars($fila["modelo"]) . "</td>";
    $filas .= "<td>" . $fila["anio"] . "</td>";
    $filas .= "<td>" . $fila["fecha_entrada"] . "</td>";
    $filas .= "<td>" . $badge . "</td>";
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

// Desplegable de clientes cargado desde la base de datos
$sqlClientes = "SELECT id, nombre_completo FROM CLIENTE WHERE deleted_at IS NULL ORDER BY nombre_completo;";
$resultadoClientes = mysqli_query($conexion, $sqlClientes);

$options = "";
while ($c = mysqli_fetch_assoc($resultadoClientes)) {
    $selected = "";
    if ($c["id"] == $idCliente) {
        $selected = " selected";
    }
    $options .= "<option value='" . $c["id"] . "'" . $selected . ">" . htmlspecialchars($c["nombre_completo"]) . "</option>";
}

include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-funnel" aria-hidden="true"></i>
        <span>Listado filtrado de coches</span>
    </h2>

    <!-- Filtros -->
    <form action="listado_coches_filtro.php" name="frmFiltroCoches" id="frmFiltroCoches" method="get">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="txtMarca">Marca</label>
                        <input id="txtMarca" name="txtMarca" class="form-control" type="text" maxlength="100"
                               placeholder="Marca (o parte de la marca)" value="<?php echo htmlspecialchars($marca); ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="lstCliente">Cliente</label>
                        <select name="lstCliente" id="lstCliente" class="form-select">
                            <option value="">-- Todos los clientes --</option>
                            <?php echo $options; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="lstReparar">¿Hay que repararlo?</label>
                        <select name="lstReparar" id="lstReparar" class="form-select">
                            <option value="">-- Todos --</option>
                            <option value="1" <?php if ($reparar === "1") { echo "selected"; } ?>>Sí</option>
                            <option value="0" <?php if ($reparar === "0") { echo "selected"; } ?>>No</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label" for="txtAnioMin">Año desde</label>
                        <input id="txtAnioMin" name="txtAnioMin" class="form-control" type="number" min="1900" max="<?php echo date('Y'); ?>"
                               placeholder="Año mínimo" value="<?php echo htmlspecialchars($anioMin); ?>">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label" for="txtAnioMax">Año hasta</label>
                        <input id="txtAnioMax" name="txtAnioMax" class="form-control" type="number" min="1900" max="<?php echo date('Y'); ?>"
                               placeholder="Año máximo" value="<?php echo htmlspecialchars($anioMax); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-12 col-md-auto">
                <button type="submit" id="btnFiltrar" name="btnFiltrar" class="btn btn-secondary btn-lg px-4 fw-semibold w-100">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                    Filtrar
                </button>
            </div>
            <div class="col-12 col-md-auto">
                <a href="listado_coches_filtro.php" class="btn btn-outline-danger btn-lg px-4 fw-semibold w-100">
                    <i class="bi bi-x-lg me-1" aria-hidden="true"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>

    <!-- Resultados -->
    <?php if ($total == 0) { ?>
        <div class="alert alert-info">No hay coches que cumplan los filtros.</div>
    <?php } else { ?>
        <p class="text-muted"><?php echo $total; ?> coche(s) encontrado(s)</p>

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
    <?php } ?>
</div>
<?php include_once("pie.html"); ?>

