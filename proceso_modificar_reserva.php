<?php
require_once("config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listado_reservas.php");
    exit;
}

$conexion = obtenerConexion();

$id         = intval($_POST["hidId"] ?? 0);
$dia        = mysqli_real_escape_string($conexion, $_POST["txtDia"] ?? "");
$franja     = mysqli_real_escape_string($conexion, $_POST["lstFranja"] ?? "");
$prioridad  = intval($_POST["lstPrioridad"] ?? 4);
$confirmada = isset($_POST["chkConfirmada"]) ? 1 : 0;

if ($id <= 0) {
    cerrarConexion($conexion);
    header("Location: listado_reservas.php");
    exit;
}

$sql = "UPDATE RESERVA
        SET dia = '$dia',
            franja_horaria = '$franja',
            prioridad_reserva = $prioridad,
            esta_confirmada = $confirmada
        WHERE id = $id;";

try {
    $resultado = mysqli_query($conexion, $sql);
    if (!$resultado) {
        $msg = (mysqli_errno($conexion) == 1062)
            ? "Ya existe una reserva para ese vehículo en ese día y franja horaria."
            : "Error al modificar la reserva: " . mysqli_error($conexion);
        cerrarConexion($conexion);
        header("Location: editar_reserva.php?id=$id&tipo=danger&mensaje=" . urlencode($msg));
        exit;
    }

    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
        if (!is_dir("uploads/reservas")) {
            mkdir("uploads/reservas", 0775, true);
        }
        move_uploaded_file($_FILES["imagen"]["tmp_name"], "uploads/reservas/" . $id);
    }

    cerrarConexion($conexion);
    header("Location: listado_reservas.php");
    exit;

} catch (mysqli_sql_exception $e) {
    $msg = ($e->getCode() == 1062)
        ? "Ya existe una reserva para ese vehículo en ese día y franja horaria."
        : "Error al modificar la reserva: " . $e->getMessage();
    cerrarConexion($conexion);
    header("Location: editar_reserva.php?id=$id&tipo=danger&mensaje=" . urlencode($msg));
    exit;
}
