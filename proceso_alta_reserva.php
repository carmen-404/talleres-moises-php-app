<?php
require_once("config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: alta_reserva.php");
    exit;
}

$conexion = obtenerConexion();

$idCoche    = intval($_POST["lstCoche"] ?? 0);
$dia        = mysqli_real_escape_string($conexion, $_POST["txtDia"] ?? "");
$franja     = mysqli_real_escape_string($conexion, $_POST["lstFranja"] ?? "");
$prioridad  = intval($_POST["lstPrioridad"] ?? 4);
$confirmada = isset($_POST["chkConfirmada"]) ? 1 : 0;

$sqlSnapshot = "SELECT c.matricula, cl.email
                FROM COCHE c
                INNER JOIN CLIENTE cl ON c.id_cliente = cl.id
                WHERE c.id = $idCoche;";
$resSnapshot  = mysqli_query($conexion, $sqlSnapshot);
$filaSnapshot = mysqli_fetch_assoc($resSnapshot);

if ($filaSnapshot == null) {
    cerrarConexion($conexion);
    header("Location: alta_reserva.php?tipo=danger&mensaje=" . urlencode("El vehículo o cliente seleccionado ya no existe."));
    exit;
}

$snapshotMatricula = mysqli_real_escape_string($conexion, $filaSnapshot["matricula"]);
$snapshotEmail     = mysqli_real_escape_string($conexion, $filaSnapshot["email"]);

$sql = "INSERT INTO RESERVA (dia, franja_horaria, id_coche, snapshot_cliente_email, snapshot_coche_matricula, prioridad_reserva, esta_confirmada)
        VALUES ('$dia', '$franja', $idCoche, '$snapshotEmail', '$snapshotMatricula', $prioridad, $confirmada);";

try {
    $resultado = mysqli_query($conexion, $sql);
    if (!$resultado) {
        $msg = (mysqli_errno($conexion) == 1062)
            ? "Ya existe una reserva para ese vehículo en ese día y franja horaria."
            : "Error al registrar la reserva: " . mysqli_error($conexion);
        cerrarConexion($conexion);
        header("Location: alta_reserva.php?tipo=danger&mensaje=" . urlencode($msg));
        exit;
    }

    $idReserva = mysqli_insert_id($conexion);

    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
        if (!is_dir("uploads/reservas")) {
            mkdir("uploads/reservas", 0775, true);
        }
        move_uploaded_file($_FILES["imagen"]["tmp_name"], "uploads/reservas/" . $idReserva);
    }

    cerrarConexion($conexion);
    header("Location: listado_reservas.php");
    exit;

} catch (mysqli_sql_exception $e) {
    $msg = ($e->getCode() == 1062)
        ? "Ya existe una reserva para ese vehículo en ese día y franja horaria."
        : "Error al registrar la reserva: " . $e->getMessage();
    cerrarConexion($conexion);
    header("Location: alta_reserva.php?tipo=danger&mensaje=" . urlencode($msg));
    exit;
}
