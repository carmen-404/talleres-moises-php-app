<?php
require_once("funcionesBD.php");

include_once("cabecera.html");
?>

<div class="container d-flex justify-content-center align-items-center mt-5">
    <div class="card shadow-sm p-4 w-100" style="max-width: 500px;">
        <form action="proceso_buscar_cliente.php" name="frmBuscarCliente" id="frmBuscarCliente" method="get">
            <fieldset>
                <legend class="mb-4 text-center fw-bold text-primary">Buscar un cliente</legend>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold" for="txtNombreCliente">Nombre</label>
                    <input id="txtNombreCliente" name="txtNombreCliente" placeholder="Nombre del cliente" class="form-control" type="text" required>
                </div>
                
                <div class="d-grid">
                    <input type="submit" id="btnAceptarBuscarCliente" name="btnAceptarBuscarCliente" class="btn btn-primary btn-block" value="Aceptar" />
                </div>
            </fieldset>
        </form>
    </div>
</div>

</body>
</html>