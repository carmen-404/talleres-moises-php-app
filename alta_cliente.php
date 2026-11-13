<?php
require_once("funcionesBD.php");

$conexion = obtenerConexion();

$sql = "SELECT id, codigo, municipio FROM CODIGO_POSTAL";

$resultado = mysqli_query($conexion, $sql);

$options = "";

while ($fila = mysqli_fetch_assoc($resultado)) {
    $options .= " <option value='" . $fila["id"] . "'>" . $fila["codigo"] . " - " . $fila["municipio"] . "</option>";
}

include_once("cabecera.html");
?>

<div class="container d-flex justify-content-center align-items-center mt-5">
    <div class="card shadow-sm p-4 w-100" style="max-width: 500px;">
        <form action="proceso_alta_cliente.php" name="frmAltaCliente" id="frmAltaCliente" method="post">
            <fieldset>
                <legend class="mb-4 text-center fw-bold text-primary">Alta de Cliente</legend>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="txtNombre">Nombre</label>
                    <input id="txtNombre" name="txtNombre" placeholder="Nombre del cliente" class="form-control" type="text" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="txtApellidos">Apellidos</label>
                    <input id="txtApellidos" name="txtApellidos" placeholder="Apellidos del cliente" class="form-control" type="text" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="txtTelefono">Teléfono</label>
                    <input id="txtTelefono" name="txtTelefono" placeholder="Teléfono" class="form-control" type="text">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="txtCodigoPostal">Código Postal o Municipio</label>
                    <input id="txtCodigoPostal" name="txtCodigoPostal" placeholder="Escribe el código postal o municipio" class="form-control" type="text">
                </div>

                <div class="d-grid">
                    <input type="submit" id="btnAceptarAltaCliente" name="btnAceptarAltaCliente" class="btn btn-primary btn-block" value="Dar de alta" />
                </div>
            </fieldset>
        </form>
    </div>
</div>

</body>
</html>