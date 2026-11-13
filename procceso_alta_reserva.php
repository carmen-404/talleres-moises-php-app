<?php
require_once("config.php");
$conexion = obtenerConexion();

$mensaje = "";
$tipoAlerta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idCoche    = intval($_POST['lstCoche']);
    $dia        = mysqli_real_escape_string($conexion, $_POST['txtDia']);
    $franja     = mysqli_real_escape_string($conexion, $_POST['lstFranja']);
    $prioridad  = intval($_POST['lstPrioridad']);
    $confirmada = isset($_POST['chkConfirmada']) ? 1 : 0;

    // 1. Obtener snapshots de matrícula y email desde COCHE y CLIENTE
    $sqlSnapshot = "SELECT c.matricula, cl.email 
                    FROM COCHE c 
                    INNER JOIN CLIENTE cl ON c.id_cliente = cl.id 
                    WHERE c.id = $idCoche;";

    $resSnapshot  = mysqli_query($conexion, $sqlSnapshot);
    $filaSnapshot = mysqli_fetch_assoc($resSnapshot);

    if (!$filaSnapshot) {
        $tipoAlerta = "danger";
        $mensaje = "Error: El vehículo o cliente seleccionado ya no existe.";
    } else {
        $snapshotMatricula = mysqli_real_escape_string($conexion, $filaSnapshot['matricula']);
        $snapshotEmail     = mysqli_real_escape_string($conexion, $filaSnapshot['email']);

        // 2. Insertar la reserva con los snapshots congelados
        $sqlInsert = "INSERT INTO `RESERVA` (`id`, `dia`, `franja_horaria`, `id_coche`, `snapshot_cliente_email`, `snapshot_coche_matricula`, `prioridad_reserva`, `esta_confirmada`) 
                      VALUES (NULL, '$dia', '$franja', $idCoche, '$snapshotEmail', '$snapshotMatricula', $prioridad, $confirmada);";

        try {
            $resultado = mysqli_query($conexion, $sqlInsert);
            if ($resultado) {
                $tipoAlerta = "success";
                $mensaje = "Reserva registrada correctamente.";
            } else {
                $tipoAlerta = "danger";
                $mensaje = (mysqli_errno($conexion) == 1062)
                    ? "Ya existe una reserva para ese vehículo en ese día y franja horaria."
                    : "Error al registrar la reserva: " . mysqli_error($conexion);
            }
        } catch (mysqli_sql_exception $e) {
            $tipoAlerta = "danger";
            $mensaje = ($e->getCode() == 1062)
                ? "Ya existe una reserva para ese vehículo en ese día y franja horaria."
                : "Error al registrar la reserva: " . $e->getMessage();
        }
    }
}

// Volver a pintar cabecera + mensaje + formulario sin JS
include_once("alta_reserva.php");
?>