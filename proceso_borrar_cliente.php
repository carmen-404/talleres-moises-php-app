<?php
require_once("config.php");

$id = intval($_POST["id"] ?? 0);

if ($id <= 0) {
    header("Location: listado_clientes.php");
    exit;
}

$conexion = obtenerConexion();

// Borrado lógico: se pone la fecha en deleted_at, la fila no se elimina
$sql = "UPDATE CLIENTE SET deleted_at = NOW() WHERE id = $id AND deleted_at IS NULL;";

try {
    mysqli_query($conexion, $sql);
    $filasBorradas = mysqli_affected_rows($conexion);
} catch (mysqli_sql_exception $e) {
    cerrarConexion($conexion);
    die("Error: no se ha podido borrar el cliente. <a href='listado_clientes.php'>Volver al listado</a>");
}

cerrarConexion($conexion);

if ($filasBorradas == 0) {
    die("No se ha encontrado el cliente (quizá ya estaba borrado). <a href='listado_clientes.php'>Volver al listado</a>");
}

header("Location: listado_clientes.php");
exit;
?>
