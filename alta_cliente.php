<?php
include_once("cabecera.html");
?>

<div class="container py-5" id="formularios">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="bg-primary bg-gradient p-4 text-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white text-primary p-3 rounded-circle shadow-sm fs-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1 fs-3 text-white">Nuevo Cliente</h2>
                            <p class="mb-0 text-white-50">Complete los datos personales para registrar al cliente en el sistema</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form action="proceso_alta_cliente.php" name="frmAltaCliente" id="frmAltaCliente" method="post">
                        
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
                                        <input id="txtNombreCompleto" name="txtNombreCompleto" class="form-control border-start-0 ps-0" type="text" maxlength="150" placeholder="Nombre y apellidos" required>
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
                                        <input id="txtEmail" name="txtEmail" class="form-control border-start-0 ps-0" type="email" maxlength="150" placeholder="correo@ejemplo.com" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold text-secondary" for="txtTelefono">Teléfono <span class="text-muted fw-normal font-monospace small">(Opcional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                        <input id="txtTelefono" name="txtTelefono" class="form-control border-start-0 ps-0" type="text" maxlength="20" placeholder="Ej: 600000000">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary" for="txtMunicipio">Municipio</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-map text-muted"></i></span>
                                        <input id="txtMunicipio" name="txtMunicipio" class="form-control border-start-0 ps-0" type="text" maxlength="100" placeholder="Escribe el municipio..." required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-3 pt-2">
                            <button type="submit" id="btnAceptarAltaCliente" name="btnAceptarAltaCliente" class="btn btn-primary btn-lg px-4 fw-semibold flex-grow-1 shadow-sm">
                                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
                                Registrar cliente
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg px-4 fw-semibold">
                                <i class="bi bi-arrow-counterclockwise me-2" aria-hidden="true"></i>
                                Reiniciar
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once("pie.html"); ?>