<?php
require_once("config.php");

// Recoger el id enviado por el formulario (POST)
$id = 0;
if (isset($_POST["id"])) {
    $id = intval($_POST["id"]);
}

// Si no hay un id válido, volver al listado
if ($id <= 0) {
    header("Location: listado_coches.php");
    exit;
}

$conexion = obtenerConexion();

// Borrado lógico: se pone la fecha en deleted_at, la fila no se elimina
$sql = "UPDATE COCHE SET deleted_at = NOW() WHERE id = $id AND deleted_at IS NULL;";

// config.php activa las excepciones, por eso try/catch
try {
    mysqli_query($conexion, $sql);
    // Filas modificadas (0 = no existe o ya estaba borrado)
    $filasBorradas = mysqli_affected_rows($conexion);
} catch (mysqli_sql_exception $e) {
    cerrarConexion($conexion);
    die("Error: no se ha podido borrar el coche. <a href='listado_coches.php'>Volver al listado</a>");
}

cerrarConexion($conexion);

if ($filasBorradas == 0) {
    die("No se ha encontrado el coche (quizá ya estaba borrado). <a href='listado_coches.php'>Volver al listado</a>");
}

// Volver al listado (la imagen no se borra, por si se recupera el coche)
header("Location: listado_coches.php");
exit;
?>
