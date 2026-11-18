<?php
require_once("config.php");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: alta_cliente.php");
    exit;
}

$nombre = trim($_POST["txtNombreCompleto"] ?? "");
$email = trim($_POST["txtEmail"] ?? "");
$telefono = trim($_POST["txtTelefono"] ?? "");
$municipio = trim($_POST["txtMunicipio"] ?? "");

if ($nombre == "" || $email == "" || $municipio == "") {
    die("Error: el nombre, el email y el municipio son obligatorios. <a href='alta_cliente.php'>Volver</a>");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: el email no es válido. <a href='alta_cliente.php'>Volver</a>");
}
if (mb_strlen($nombre) > 150 || mb_strlen($email) > 150 || mb_strlen($telefono) > 20 || mb_strlen($municipio) > 100) {
    die("Error: algún campo es demasiado largo. <a href='alta_cliente.php'>Volver</a>");
}

$telefonoBD = ($telefono == "") ? null : $telefono;
$conexion = obtenerConexion();


$stmtCp = mysqli_prepare($conexion, "SELECT id FROM CODIGO_POSTAL WHERE municipio = ? LIMIT 1;");
mysqli_stmt_bind_param($stmtCp, "s", $municipio);
mysqli_stmt_execute($stmtCp);
$resCp = mysqli_stmt_get_result($stmtCp);
$filaCp = mysqli_fetch_assoc($resCp);
mysqli_stmt_close($stmtCp);

if ($filaCp) {

    $idCp = $filaCp["id"];
} else {

    $stmtInsCp = mysqli_prepare($conexion, "INSERT INTO CODIGO_POSTAL (codigo, municipio) VALUES ('00000', ?);");
    mysqli_stmt_bind_param($stmtInsCp, "s", $municipio);
    mysqli_stmt_execute($stmtInsCp);
    $idCp = mysqli_insert_id($conexion);
    mysqli_stmt_close($stmtInsCp);
}


$stmt = mysqli_prepare($conexion, "SELECT id, deleted_at FROM CLIENTE WHERE email = ?;");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$existente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

try {
    if ($existente == null) {
        $stmt = mysqli_prepare($conexion, "INSERT INTO CLIENTE (nombre_completo, telefono, email, id_cp) VALUES (?, ?, ?, ?);");
        mysqli_stmt_bind_param($stmt, "sssi", $nombre, $telefonoBD, $email, $idCp);
    } elseif ($existente["deleted_at"] == null) {
        cerrarConexion($conexion);
        die("Error: ya existe un cliente con ese email. <a href='alta_cliente.php'>Volver</a>");
    } else {
        $stmt = mysqli_prepare($conexion, "UPDATE CLIENTE SET deleted_at = NULL, nombre_completo = ?, telefono = ?, id_cp = ? WHERE id = ?;");
        mysqli_stmt_bind_param($stmt, "ssii", $nombre, $telefonoBD, $idCp, $existente["id"]);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    cerrarConexion($conexion);
    die("Error detallado de MySQL: " . htmlspecialchars($e->getMessage()) . " <a href='alta_cliente.php'>Volver</a>");
}

cerrarConexion($conexion);

header("Location: listado_clientes.php");
exit;
?>