<?php
require_once("funcionesBD.php");

$conexion = obtenerConexion();

// Recoger los datos del formulario
$marca = mysqli_real_escape_string($conexion, $_POST["txtMarca"]);
$modelo = mysqli_real_escape_string($conexion, $_POST["txtModelo"]);
$matricula = mysqli_real_escape_string($conexion, $_POST["txtMatricula"]);
$anio = intval($_POST["txtAnio"]);
$fechaEntrada = mysqli_real_escape_string($conexion, $_POST["txtFechaEntrada"]);
$idCliente = intval($_POST["lstCliente"]);
$reparar = isset($_POST["chkReparar"]) ? 1 : 0;

// Comprobar si la matrícula ya existe (activa o borrada)
$sql = "SELECT id, deleted_at FROM COCHE WHERE matricula = '$matricula';";
$resultado = mysqli_query($conexion, $sql);
$existente = mysqli_fetch_assoc($resultado);

if ($existente == null) {
    $sql = "INSERT INTO COCHE (fecha_entrada, reparar, marca, modelo, matricula, anio, id_cliente)
            VALUES ('$fechaEntrada', $reparar, '$marca', '$modelo', '$matricula', $anio, $idCliente);";
} elseif ($existente["deleted_at"] == null) {
    echo "Error: ya existe un coche con esa matrícula. <a href='alta_coche.php'>Volver</a>";
    exit;
} else {
    $sql = "UPDATE COCHE SET deleted_at = NULL, fecha_entrada = '$fechaEntrada', reparar = $reparar,
            marca = '$marca', modelo = '$modelo', anio = $anio, id_cliente = $idCliente
            WHERE id = " . $existente["id"] . ";";
}

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    echo "Error: " . mysqli_error($conexion);
    exit;
}

// Id del coche (nuevo o restaurado)
if ($existente == null) {
    $idCoche = mysqli_insert_id($conexion);
} else {
    $idCoche = $existente["id"];
}

// ---------- Imagen (opcional) ----------
if ($_FILES["imagen"]["error"] != UPLOAD_ERR_NO_FILE) {

    if ($_FILES["imagen"]["error"] != UPLOAD_ERR_OK) {
        die("El coche se ha guardado, pero ha fallado la subida de la imagen. <a href='listado_coches.php'>Ir al listado</a>");
    }

    // Máximo 2 MB
    if ($_FILES["imagen"]["size"] > 2 * 1024 * 1024) {
        die("El coche se ha guardado, pero la imagen supera los 2 MB. <a href='listado_coches.php'>Ir al listado</a>");
    }

    // Recuperar el tipo de imagen
    $info = @getimagesize($_FILES["imagen"]["tmp_name"]);
    if ($info == false) {
        die("El coche se ha guardado, pero el archivo no es una imagen. <a href='listado_coches.php'>Ir al listado</a>");
    }
    $type = $info[2];

    $error = "El coche se ha guardado, pero el tipo de imagen no está soportado. <a href='listado_coches.php'>Ir al listado</a>";
    switch ($type) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($_FILES["imagen"]["tmp_name"]) or die($error);
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($_FILES["imagen"]["tmp_name"]) or die($error);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($_FILES["imagen"]["tmp_name"]) or die($error);
            break;
        default:
            die($error);
    }

    // Carpeta uploads/coches (se crea si no existe)
    $dir = "uploads/coches";
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    // Guardar la imagen con el id del coche como nombre
    switch ($type) {
        case IMAGETYPE_GIF:
            imagegif($image, $dir . "/" . $idCoche);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($image, $dir . "/" . $idCoche, 100);
            break;
        case IMAGETYPE_PNG:
            imagepng($image, $dir . "/" . $idCoche);
            break;
    }
    imagedestroy($image);
}

header("Location: listado_coches.php");
?>