<?php
require_once("config.php");

$id = 0;
if (isset($_POST["id"])) {
    $id = intval($_POST["id"]);
}

if ($id <= 0) {
    header("Location: listado_reservas.php");
    exit;
}

$conexion = obtenerConexion();

mysqli_query($conexion, "DELETE FROM RESERVA WHERE id = $id;");

$ruta = "uploads/reservas/" . $id;
if (file_exists($ruta)) {
    unlink($ruta);
}

cerrarConexion($conexion);

header("Location: listado_reservas.php");
exit;
