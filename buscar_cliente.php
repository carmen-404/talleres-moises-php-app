<?php
include_once("cabecera.html");
?>

<div class="container py-5" id="formularios">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Tarjeta Principal de Búsqueda -->
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="bg-primary bg-gradient p-4 text-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white text-primary p-3 rounded-circle shadow-sm fs-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1 fs-3 text-white">Buscador de Clientes</h2>
                            <p class="mb-0 text-white-50">Localice rápidamente a cualquier cliente registrado en el sistema</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form action="proceso_buscar_cliente.php" name="frmBuscarCliente" id="frmBuscarCliente" method="get">
                        <div class="bg-white p-4 rounded-4 shadow-sm border-0 mb-3">
                            <label class="form-label fw-semibold text-secondary mb-3" for="txtBuscar">Criterio de búsqueda</label>
                            
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-lines-fill"></i></span>
                                <input id="txtBuscar" name="txtBuscar" class="form-control border-start-0 ps-0" type="text"
                                       placeholder="Escribe nombre, email, teléfono o municipio..." autocomplete="off" required>
                            </div>
                            <div class="form-text text-muted mt-2">
                                <i class="bi bi-info-circle me-1"></i> Puedes buscar por cualquier coincidencia parcial o total.
                            </div>
                        </div>

                        <div class="d-grid pt-2">
                            <button type="submit" id="btnBuscar" class="btn btn-primary btn-lg fw-semibold shadow-sm py-3">
                                <i class="bi bi-search me-2" aria-hidden="true"></i>
                                Realizar búsqueda
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once("pie.html"); ?>