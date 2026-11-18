<?php
require_once("config.php");

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: listado_clientes.php");
    exit;
}

$conexion = obtenerConexion();

$stmt = mysqli_prepare($conexion, "SELECT id, nombre_completo, telefono, email, id_cp
                                   FROM CLIENTE WHERE id = ? AND deleted_at IS NULL;");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$cliente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($cliente == null) {
    header("Location: listado_clientes.php");
    exit;
}

$resultado = mysqli_query($conexion, "SELECT id, codigo, municipio FROM CODIGO_POSTAL ORDER BY codigo, municipio;");
$options = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    $selected = ($fila["id"] == $cliente["id_cp"]) ? " selected" : "";
    $options .= " <option value='" . $fila["id"] . "'" . $selected . ">" . htmlspecialchars($fila["codigo"] . " - " . $fila["municipio"]) . "</option>";
}

cerrarConexion($conexion);

include_once("cabecera.html");
?>

<div class="container py-5" id="formularios">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="bg-primary bg-gradient p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white text-primary p-3 rounded-circle shadow-sm fs-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-person-gear" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold mb-1 fs-3 text-white">Editar Perfil de Cliente</h2>
                                <p class="mb-0 text-white-50">Actualice la información de contacto o ubicación del cliente</p>
                            </div>
                        </div>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-semibold shadow-sm">
                            Ficha #<?php echo $cliente['id']; ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form action="proceso_modificar_cliente.php" name="frmModificarCliente" id="frmModificarCliente" method="post">
                        <input type="hidden" name="hidId" value="<?php echo $cliente['id']; ?>">

                        <div class="bg-white p-4 rounded-4 shadow-sm border-0 mb-4">
                            <h5 class="text-primary fw-semibold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-card-heading"></i>
                                <span>Datos de Identificación</span>
                            </h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary" for="txtNombreCompleto">Nombre completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                        <input id="txtNombreCompleto" name="txtNombreCompleto" class="form-control border-start-0 ps-0" type="text" maxlength="150"
                                               value="<?php echo htmlspecialchars($cliente['nombre_completo']); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-4 shadow-sm border-0 mb-4">
                            <h5 class="text-primary fw-semibold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt"></i>
                                <span>Contacto y Localización</span>
                            </h5>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-secondary" for="txtEmail">Correo electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                        <input id="txtEmail" name="txtEmail" class="form-control border-start-0 ps-0" type="email" maxlength="150"
                                               value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-secondary" for="txtTelefono">Teléfono <span class="text-muted fw-normal font-monospace small">(Opcional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                        <input id="txtTelefono" name="txtTelefono" class="form-control border-start-0 ps-0" type="text" maxlength="20"
                                               value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary" for="lstCodigoPostal">Código postal y municipio</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-map text-muted"></i></span>
                                        <select name="lstCodigoPostal" id="lstCodigoPostal" class="form-select border-start-0 ps-0" required>
                                            <?php echo $options; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-3 pt-2">
                            <button type="submit" id="btnAceptarModificarCliente" name="btnAceptarModificarCliente" class="btn btn-primary btn-lg px-4 fw-semibold flex-grow-1 shadow-sm">
                                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
                                Guardar cambios
                            </button>
                            <a href="listado_clientes.php" class="btn btn-outline-danger btn-lg px-4 fw-semibold d-flex align-items-center justify-content-center">
                                <i class="bi bi-x-circle me-2" aria-hidden="true"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once("pie.html"); ?>