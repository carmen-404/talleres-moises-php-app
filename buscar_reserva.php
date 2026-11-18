<?php
include_once("cabecera.html");
?>

<div class="container mt-4" id="formularios">
    <h2 class="text-primary fw-normal mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-search" aria-hidden="true"></i>
        <span>Buscar reserva</span>
    </h2>

    <div class="card mb-4">
        <div class="card-body">
            <form action="proceso_buscar_reserva.php" name="frmBuscarReserva" id="frmBuscarReserva" method="get">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="txtFechaInicio">Fecha desde</label>
                        <input id="txtFechaInicio" name="txtFechaInicio" class="form-control" type="date">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="txtFechaFin">Fecha hasta</label>
                        <input id="txtFechaFin" name="txtFechaFin" class="form-control" type="date">
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" id="btnBuscar" class="btn btn-secondary btn-lg px-4 fw-semibold w-100">
                            <i class="bi bi-search me-1" aria-hidden="true"></i>
                            Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include_once("pie.html"); ?>
