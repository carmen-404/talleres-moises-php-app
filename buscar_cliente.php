<?php
require_once("config.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Talleres Moises · Gestión</title>
  <link rel="icon" type="image/svg+xml" href="img/favicon/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <link href="css/admin.css" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="img/logo/logo.svg" alt="Talleres Moises" height="156">
            </a>

            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false"
                aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house-fill"></i> Inicio</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people-fill"></i> Clientes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="alta_cliente.php"><i class="bi bi-plus-circle me-2"></i>Alta de clientes</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="buscar_cliente.php"><i class="bi bi-search me-2"></i>Buscar un cliente</a></li>
                            <li><a class="dropdown-item" href="listado_clientes.php"><i class="bi bi-list-ul me-2"></i>Listado de clientes</a></li>
                            <li><a class="dropdown-item" href="listado_clientes_filtro.php"><i class="bi bi-funnel me-2"></i>Listado filtrado de clientes</a></li>
                        </ul>
                    </li>
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