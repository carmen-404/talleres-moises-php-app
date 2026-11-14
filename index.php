<!-- Esta página para el landpage ha sido generada con AI -->
<?php
require_once("config.php");

$conexion = obtenerConexion();

// Tarjetas del panel: tabla, título, icono y página de su listado
$tarjetas = array(
    array("CLIENTE", "Clientes", "bi-people-fill", "listado_clientes.php"),
    array("COCHE", "Coches", "bi-car-front-fill", "listado_coches.php"),
    array("RESERVA", "Reservas", "bi-calendar-check", "listado_reservas.php"),
    array("SERVICIO", "Servicios", "bi-wrench-adjustable", "listado_servicios.php")
);

$error = "";
$cuentas = array();
$proximas = array();

try {
    // Número de registros de cada tabla
    foreach ($tarjetas as $t) {
        $resultado = mysqli_query($conexion, "SELECT COUNT(*) AS n FROM " . $t[0]);
        $fila = mysqli_fetch_assoc($resultado);
        $cuentas[$t[0]] = $fila["n"];
    }

    // Próximas reservas (desde hoy), las 6 más cercanas
    $sql = "SELECT r.id, r.dia, r.franja_horaria, co.matricula, CONCAT(co.marca, ' ', co.modelo) AS vehiculo,
                   cl.nombre_completo AS cliente
            FROM RESERVA r
            INNER JOIN COCHE co ON co.id = r.id_coche
            INNER JOIN CLIENTE cl ON cl.id = co.id_cliente
            WHERE r.dia >= CURDATE()
            ORDER BY r.dia, FIELD(r.franja_horaria, 'MANANA', 'TARDE')
            LIMIT 6";
    $resultado = mysqli_query($conexion, $sql);
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $proximas[] = $fila;
    }
} catch (mysqli_sql_exception $e) {
    $error = $e->getMessage();
}

cerrarConexion($conexion);

// Cabecera HTML que incluye navbar
include_once("cabecera.html");
?>

  <main class="container py-4">
    <?php if ($error != "") { ?>
      <div class="alert alert-danger">Error de base de datos: <?php echo htmlspecialchars($error); ?> ¿Has importado sql/backup.sql?</div>
    <?php } ?>

    <h1 class="h2 text-primary mb-4"><i class="bi bi-house-fill me-2"></i>Panel de gestión</h1>

    <div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
      <?php foreach ($tarjetas as $t) { ?>
      <div class="col">
        <a class="text-decoration-none" href="<?php echo $t[3]; ?>">
          <div class="card card-porque h-100 text-center shadow-sm border-0"><div class="card-body">
            <i class="bi <?php echo $t[2]; ?> display-5 text-primary"></i>
            <div class="display-6 fw-bold text-dark"><?php echo isset($cuentas[$t[0]]) ? $cuentas[$t[0]] : 0; ?></div>
            <div class="text-body-secondary"><?php echo $t[1]; ?></div>
          </div></div>
        </a>
      </div>
      <?php } ?>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white"><i class="bi bi-calendar-week me-2"></i>Próximas reservas</div>
      <?php if (count($proximas) == 0) { ?>
        <div class="card-body"><div class="alert alert-info mb-0">No hay reservas próximas.</div></div>
      <?php } else { ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($proximas as $p) { ?>
          <li class="list-group-item py-3">
            <i class="bi bi-calendar-day text-warning me-2"></i><?php echo date("d/m/Y", strtotime($p["dia"])); ?>
            <?php if ($p["franja_horaria"] == "MANANA") { ?>
              <span class="badge badge-manana">Mañana</span>
            <?php } else { ?>
              <span class="badge badge-tarde">Tarde</span>
            <?php } ?>
            · <strong><?php echo htmlspecialchars($p["matricula"]); ?></strong> <?php echo htmlspecialchars($p["vehiculo"]); ?>
            <span class="text-body-secondary">(<?php echo htmlspecialchars($p["cliente"]); ?>)</span>
          </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
  </main>

<?php include_once("pie.html"); ?>