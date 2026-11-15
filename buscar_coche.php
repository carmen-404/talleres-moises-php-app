<?php
// Cabecera HTML que incluye navbar
include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-search" aria-hidden="true"></i>
        <span>Buscar coche</span>
    </h2>

    <div class="card mb-4">
        <div class="card-body">
            <form action="proceso_buscar_coche.php" name="frmBuscarCoche" id="frmBuscarCoche" method="get">
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input id="txtBuscar" name="txtBuscar" class="form-control" type="text"
                           placeholder="Matrícula, marca, modelo o cliente...">
                    <button type="submit" id="btnBuscar" class="btn btn-secondary px-4 fw-semibold">Buscar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include_once("pie.html"); ?>

