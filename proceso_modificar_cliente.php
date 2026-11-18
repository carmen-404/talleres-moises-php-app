<?php
require_once("config.php");

function terminar($mensaje, $id) {
    die($mensaje . " <a href='editar_cliente.php?id=" . $id . "'>Volver</a>");
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: listado_clientes.php");
    exit;
}

$id = intval($_POST["hidId"] ?? 0);
$nombre = trim($_POST["txtNombreCompleto"] ?? "");
$email = trim($_POST["txtEmail"] ?? "");
$telefono = trim($_POST["txtTelefono"] ?? "");
$municipio = trim($_POST["txtMunicipio"] ?? "");

if ($id <= 0) {
    header("Location: listado_clientes.php");
    exit;
}

if ($nombre == "" || $email == "" || $municipio == "") {
    terminar("Error: el nombre, el email y el municipio son obligatorios.", $id);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    terminar("Error: el email no es válido.", $id);
}
if (mb_strlen($nombre) > 150 || mb_strlen($email) > 150) {
    terminar("Error: el nombre o el email son demasiado largos (máx. 150).", $id);
}
if (mb_strlen($telefono) > 20) {
    terminar("Error: el teléfono es demasiado largo (máx. 20).", $id);
}
if (mb_strlen($municipio) > 100) {
    terminar("Error: el municipio es demasiado largo (máx. 100).", $id);
}
$telefonoBD = ($telefono == "") ? null : $telefono;

$conexion = obtenerConexion();

$stmt = mysqli_prepare($conexion, "SELECT id FROM CLIENTE WHERE id = ? AND deleted_at IS NULL;");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$cliente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($cliente == null) {
    die("Error: el cliente no existe o está borrado. <a href='listado_clientes.php'>Volver al listado</a>");
}

try {
    $sql = "UPDATE CLIENTE
            SET nombre_completo = ?, telefono = ?, email = ?, municipio = ?
            WHERE id = ?;";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $telefonoBD, $email, $municipio, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // 1062 = email repetido
    if ($e->getCode() == 1062) {
        terminar("Error: ya existe otro cliente con ese email (puede estar borrado).", $id);
    }
    terminar("Error: no se ha podido modificar el cliente.", $id);
}

cerrarConexion($conexion);

header("Location: listado_clientes.php");
exit;
?>