<?php
$cliente = json_decode($_POST['cliente'], true);

require_once("config.php");
$conexion = obtenerConexion();

$sql = "SELECT idprovincia, provincia FROM provincias;";
$resultado = mysqli_query($conexion, $sql);

$options = "";
while ($fila = mysqli_fetch_assoc($resultado)) {
    if ($fila['idprovincia'] == $cliente['idprovincia']) {
        $options .= " <option selected value='" . $fila["idprovincia"] . "'>" . $fila["provincia"] . "</option>";
    } else {
        $options .= " <option value='" . $fila["idprovincia"] . "'>" . $fila["provincia"] . "</option>";
    }
}
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

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-car-front-fill"></i> Coches
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="alta_coche.php"><i class="bi bi-plus-circle me-2"></i>Alta de coches</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="buscar_coche.php"><i class="bi bi-search me-2"></i>Buscar un coche</a></li>
                            <li><a class="dropdown-item" href="listado_coches.php"><i class="bi bi-list-ul me-2"></i>Listado de coches</a></li>
                            <li><a class="dropdown-item" href="listado_coches_filtro.php"><i class="bi bi-funnel me-2"></i>Listado filtrado de coches</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-calendar-check"></i> Reservas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="alta_reserva.php"><i class="bi bi-plus-circle me-2"></i>Alta de reservas</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="buscar_reserva.php"><i class="bi bi-search me-2"></i>Buscar una reserva</a></li>
                            <li><a class="dropdown-item" href="listado_reservas.php"><i class="bi bi-list-ul me-2"></i>Listado de reservas</a></li>
                            <li><a class="dropdown-item" href="listado_reservas_filtro.php"><i class="bi bi-funnel me-2"></i>Listado filtrado de reservas</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-wrench-adjustable"></i> Servicios
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="alta_servicio.php"><i class="bi bi-plus-circle me-2"></i>Alta de servicios</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="buscar_servicio.php"><i class="bi bi-search me-2"></i>Buscar un servicio</a></li>
                            <li><a class="dropdown-item" href="listado_servicios.php"><i class="bi bi-list-ul me-2"></i>Listado de servicios</a></li>
                            <li><a class="dropdown-item" href="listado_servicios_filtro.php"><i class="bi bi-funnel me-2"></i>Listado filtrado de servicios</a></li>
                        </ul>
                    </li>
                </ul>

                <a class="btn btn-secondary ms-lg-3 d-none d-lg-inline-block" href="alta_reserva.php">Nueva reserva</a>
            </div>
        </div>
    </nav>

<div class="container" id="formularios">
    <div class="row mt-4">
        <form class="form-horizontal" action="proceso_modificar_cliente.php" name="frmModificarCliente" id="frmModificarCliente" method="post">
            <fieldset>
                <legend>Modificación de cliente</legend>
                
                <div class="form-group mb-3">
                    <label class="col-xs-4 control-label" for="txtNombre">Nombre</label>
                    <div class="col-xs-4">
                        <input value="<?php echo $cliente['nombre']; ?>" id="txtNombre" name="txtNombre" placeholder="Nombre del cliente" class="form-control input-md" type="text">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="col-xs-4 control-label" for="txtApellidos">Apellidos</label>
                    <div class="col-xs-4">
                        <input value="<?php echo $cliente['apellidos']; ?>" id="txtApellidos" name="txtApellidos" placeholder="Apellidos" class="form-control input-md" type="text">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="col-xs-4 control-label" for="txtTelefono">Teléfono</label>
                    <div class="col-xs-4">
                        <input value="<?php echo $cliente['telefono']; ?>" id="txtTelefono" name="txtTelefono" placeholder="Teléfono" class="form-control input-md" type="text">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="col-xs-4 control-label" for="lstProvincia">Provincia</label>
                    <div class="col-xs-4">
                        <select name="lstProvincia" id="lstProvincia" class="form-select" aria-label="Default select example">
                            <?php echo $options; ?>
                        </select>
                    </div>
                </div>

                <input value="<?php echo $cliente['idcliente']; ?>" type="hidden" name="idcliente" id="idcliente" />

                <div class="form-group">
                    <label class="col-xs-4 control-label" for="btnAceptarModificarCliente"></label>
                    <div class="col-xs-4">
                        <input type="submit" id="btnAceptarModificarCliente" name="btnAceptarModificarCliente" class="btn btn-primary" value="Aceptar" />
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

</body>
</html>