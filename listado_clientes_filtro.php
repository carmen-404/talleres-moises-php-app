<?php
require_once("config.php");

$conexion = obtenerConexion();

$nombre = trim($_GET["txtNombre"] ?? "");
$idCp = intval($_GET["lstCodigoPostal"] ?? 0);
$telefono = $_GET["lstTelefono"] ?? "";

$sql = "SELECT c.id, c.nombre_completo, c.telefono, c.email, cp.codigo, cp.municipio
        FROM CLIENTE c
        LEFT JOIN CODIGO_POSTAL cp ON c.id_cp = cp.id
        WHERE c.deleted_at IS NULL";
$tipos = "";
$params = [];

if ($nombre != "") {
    $sql .= " AND c.nombre_completo LIKE ?";
    $tipos .= "s";
    $params[] = "%" . $nombre . "%";
}
if ($idCp > 0) {
    $sql .= " AND c.id_cp = ?";
    $tipos .= "i";
    $params[] = $idCp;
}
if ($telefono === "1") {
    $sql .= " AND c.telefono IS NOT NULL AND c.telefono <> ''";
} elseif ($telefono === "0") {
    $sql .= " AND (c.telefono IS NULL OR c.telefono = '')";
}
$sql .= " ORDER BY c.nombre_completo;";

$stmt = mysqli_prepare($conexion, $sql);
if ($tipos != "") {
    mysqli_stmt_bind_param($stmt, $tipos, ...$params);
}
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$total = mysqli_num_rows($resultado);

$filas = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    // Generar iniciales dinámicas para el avatar
    $partesNombre = explode(" ", trim($fila["nombre_completo"]));
    $iniciales = strtoupper(substr($partesNombre[0], 0, 1) . (isset($partesNombre[1]) ? substr($partesNombre[1], 0, 1) : ""));
    $avatar = "<div class='rounded-circle bg-primary bg-opacity-15 text-primary fw-bold d-flex align-items-center justify-content-center shadow-sm' style='width: 40px; height: 40px; font-size: 0.85rem;'>" . $iniciales . "</div>";

    if ($fila["telefono"] == null) {
        $tel = "<span class='text-muted fst-italic'>Sin teléfono</span>";
    } else {
        $tel = "<i class='bi bi-telephone text-muted me-1'></i>" . htmlspecialchars($fila["telefono"]);
    }

    $filas .= "<tr class='align-middle'>";
    $filas .= "<td class='ps-3'>" . $avatar . "</td>";
    $filas .= "<td class='fw-bold text-dark'>" . htmlspecialchars($fila["nombre_completo"]) . "</td>";
    $filas .= "<td class='text-secondary'><i class='bi bi-envelope text-muted me-1'></i>" . htmlspecialchars($fila["email"]) . "</td>";
    $filas .= "<td>" . $tel . "</td>";
    $filas .= "<td><span class='badge bg-light text-dark border px-2 py-1'><i class='bi bi-geo-alt text-danger me-1'></i>" . htmlspecialchars($fila["codigo"] . " - " . $fila["municipio"]) . "</span></td>";
    $filas .= "<td class='text-end pe-3'>";
    $filas .= "<a href='editar_cliente.php?id=" . $fila["id"] . "' class='btn btn-sm btn-outline-primary me-1 px-2 py-1 shadow-sm' title='Editar cliente'><i class='bi bi-pencil-square'></i></a>";
    $filas .= "<form class='d-inline' action='proceso_borrar_cliente.php' method='post' onsubmit=\"return confirm('¿Seguro que quieres borrar este cliente?');\">";
    $filas .= "<input type='hidden' name='id' value='" . $fila["id"] . "'>";
    $filas .= "<button type='submit' class='btn btn-sm btn-outline-danger px-2 py-1 shadow-sm' title='Eliminar cliente'><i class='bi bi-trash'></i></button>";
    $filas .= "</form>";
    $filas .= "</td>";
    $filas .= "</tr>";
}
mysqli_stmt_close($stmt);

// Desplegable de códigos postales
$resultadoCp = mysqli_query($conexion, "SELECT id, codigo, municipio FROM CODIGO_POSTAL ORDER BY codigo, municipio;");
$options = "";
while ($c = mysqli_fetch_assoc($resultadoCp)) {
    $selected = ($c["id"] == $idCp) ? " selected" : "";
    $options .= "<option value='" . $c["id"] . "'" . $selected . ">" . htmlspecialchars($c["codigo"] . " - " . $c["municipio"]) . "</option>";
}

cerrarConexion($conexion);

include_once("cabecera.html");
?>

<div class="container py-5" id="formularios">
    <!-- Encabezado superior -->
    <div class="bg-white p-4 rounded-4 shadow-sm border-0 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white p-3 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                <i class="bi bi-funnel-fill fs-4" aria-hidden="true"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-1 fs-3 text-primary">Filtrado de Clientes</h2>
                <p class="mb-0 text-muted">Busca y filtra el directorio de clientes de forma avanzada</p>
            </div>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
            <?php echo $total; ?> resultado(s)
        </span>
    </div>

    <!-- Formulario de Filtros -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-light py-3 px-4 border-bottom fw-semibold text-secondary">
            <i class="bi bi-sliders me-2"></i> Criterios de filtrado
        </div>
        <div class="card-body p-4 bg-white">
            <form action="listado_clientes_filtro.php" name="frmFiltroClientes" id="frmFiltroClientes" method="get">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary" for="txtNombre">Nombre</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input id="txtNombre" name="txtNombre" class="form-control border-start-0 ps-0" type="text" maxlength="150"
                                   placeholder="Nombre (o parte)" value="<?php echo htmlspecialchars($nombre); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary" for="lstCodigoPostal">Código postal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                            <select name="lstCodigoPostal" id="lstCodigoPostal" class="form-select border-start-0 ps-0">
                                <option value="">-- Todos los códigos postales --</option>
                                <?php echo $options; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary" for="lstTelefono">¿Tiene teléfono?</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                            <select name="lstTelefono" id="lstTelefono" class="form-select border-start-0 ps-0">
                                <option value="">-- Todos --</option>
                                <option value="1" <?php if ($telefono === "1") { echo "selected"; } ?>>Sí</option>
                                <option value="0" <?php if ($telefono === "0") { echo "selected"; } ?>>No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2 mt-4 pt-2 border-top">
                    <button type="submit" id="btnFiltrar" name="btnFiltrar" class="btn btn-primary px-4 fw-semibold shadow-sm">
                        <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                        Aplicar filtros
                    </button>
                    <a href="listado_clientes_filtro.php" class="btn btn-outline-secondary px-4 fw-semibold">
                        <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>
                        Limpiar filtros
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <?php if ($total == 0): ?>
        <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-4">
            <div class="mb-3 text-warning">
                <i class="bi bi-exclamation-circle display-4"></i>
            </div>
            <h4 class="fw-bold text-dark">No se encontraron registros</h4>
            <p class="text-muted mb-0">No hay ningún cliente que cumpla exactamente con los filtros seleccionados.</p>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive m-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark text-uppercase fs-7">
                        <tr>
                            <th class="ps-3 py-3" style="width: 60px;">Avatar</th>
                            <th class="py-3">Nombre completo</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Teléfono</th>
                            <th class="py-3">Código postal / Municipio</th>
                            <th class="text-end pe-3 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $filas; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once("pie.html"); ?>