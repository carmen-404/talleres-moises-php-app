<?php
require_once("funcionesBD.php");
$conexion = bd();

$tablas = array("clientes" => "CLIENTE", "coches" => "COCHE", "reservas" => "RESERVA", "servicios" => "SERVICIO");
$cuentas = array();
foreach ($tablas as $clave => $tabla) {
    $f = consultar($conexion, "SELECT COUNT(*) AS n FROM " . $tabla);
    $cuentas[$clave] = $f ? (int)$f[0]["n"] : 0;
}
$proximas = consultar($conexion, "SELECT r.id, r.dia, r.franja_horaria, co.matricula, CONCAT(co.marca, ' ', co.modelo) AS vehiculo,
        cl.nombre_completo AS cliente
    FROM RESERVA r
    INNER JOIN COCHE co ON co.id = r.id_coche
    INNER JOIN CLIENTE cl ON cl.id = co.id_cliente
    WHERE r.dia >= CURDATE()
    ORDER BY r.dia, FIELD(r.franja_horaria, 'MANANA', 'TARDE') LIMIT 6");

include_once("cabecera.html");
?>

  <main class="container py-4">
    <?php echo flashHTML(); ?>
    <?php if ($errorBD !== "") { echo alertaHTML("danger", "Error de base de datos: " . $errorBD . " ¿Has importado sql/backup.sql?"); } ?>

    <h1 class="h2 text-primary mb-4"><i class="bi bi-house-fill me-2"></i>Panel de gestión</h1>

    <div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
      <?php foreach ($tablas as $clave => $tabla) { $def = $ENTIDADES[$clave]; ?>
      <div class="col">
        <a class="text-decoration-none" href="listado_<?php echo $clave; ?>.php">
          <div class="card card-porque h-100 text-center shadow-sm border-0"><div class="card-body">
            <i class="bi <?php echo $def["icono"]; ?> display-5 text-primary"></i>
            <div class="display-6 fw-bold text-dark"><?php echo $cuentas[$clave]; ?></div>
            <div class="text-body-secondary"><?php echo h($def["titulo"]); ?></div>
          </div></div>
        </a>
      </div>
      <?php } ?>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white"><i class="bi bi-calendar-week me-2"></i>Próximas reservas</div>
      <?php if (!$proximas) { ?>
        <div class="card-body"><?php echo alertaHTML("info", "No hay reservas próximas."); ?></div>
      <?php } else { ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($proximas as $p) { ?>
          <li class="list-group-item py-3">
            <i class="bi bi-calendar-day text-warning me-2"></i><?php echo fechaES($p["dia"]); ?>
            <?php echo badgeFranja($p["franja_horaria"]); ?>
            · <strong><?php echo h($p["matricula"]); ?></strong> <?php echo h($p["vehiculo"]); ?>
            <span class="text-body-secondary">(<?php echo h($p["cliente"]); ?>)</span>
          </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
  </main>

<?php include_once("pie.html"); ?>
