<?php
require_once("config.php");

// Muestra el error y para (con enlace para volver al formulario)
function terminar($mensaje, $id) {
    die($mensaje . " <a href='editar_coche.php?id=" . $id . "'>Volver</a>");
}

// Solo se entra desde el formulario (POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: listado_coches.php");
    exit;
}

// Recoger los datos del formulario (?? "" = vacío si no llega el campo)
$id = intval($_POST["hidId"] ?? 0);
$matricula = trim($_POST["txtMatricula"] ?? "");
$marca = trim($_POST["txtMarca"] ?? "");
$modelo = trim($_POST["txtModelo"] ?? "");
$anio = intval($_POST["txtAnio"] ?? 0);
$fechaEntrada = trim($_POST["txtFechaEntrada"] ?? "");
$idCliente = intval($_POST["lstCliente"] ?? 0);
$reparar = isset($_POST["chkReparar"]) ? 1 : 0; // el checkbox sin marcar no se envía

if ($id <= 0) {
    header("Location: listado_coches.php");
    exit;
}

// Validar los datos (el required del HTML no basta)
if ($matricula == "" || $marca == "" || $modelo == "") {
    terminar("Error: la matrícula, la marca y el modelo son obligatorios.", $id);
}
if (mb_strlen($matricula) > 15 || mb_strlen($marca) > 100 || mb_strlen($modelo) > 100) {
    terminar("Error: la matrícula (máx. 15) o la marca/modelo (máx. 100) son demasiado largos.", $id);
}
if ($anio < 1900 || $anio > intval(date("Y"))) {
    terminar("Error: el año debe estar entre 1900 y " . date("Y") . ".", $id);
}
// La fecha tiene que existir de verdad
$fecha = DateTime::createFromFormat("Y-m-d", $fechaEntrada);
if (!$fecha || $fecha->format("Y-m-d") != $fechaEntrada) {
    terminar("Error: la fecha de entrada no es válida.", $id);
}
if ($idCliente <= 0) {
    terminar("Error: hay que seleccionar un cliente.", $id);
}

$conexion = obtenerConexion();

// Comprobar que el coche existe y no está borrado
// (con affected_rows no vale: da 0 si se guarda sin cambiar nada)
$stmt = mysqli_prepare($conexion, "SELECT id FROM COCHE WHERE id = ? AND deleted_at IS NULL;");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$coche = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($coche == null) {
    die("Error: el coche no existe o está borrado. <a href='listado_coches.php'>Volver al listado</a>");
}

// ---------- Imagen (opcional) ----------
// Si no se sube ninguna se queda la que ya tenía
$image = null;
if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] != UPLOAD_ERR_NO_FILE) {

    if ($_FILES["imagen"]["error"] != UPLOAD_ERR_OK) {
        terminar("Error: ha fallado la subida de la imagen.", $id);
    }

    // Máximo 2 MB
    if ($_FILES["imagen"]["size"] > 2 * 1024 * 1024) {
        terminar("Error: la imagen supera los 2 MB.", $id);
    }

    // Recuperar el tipo de imagen
    $info = @getimagesize($_FILES["imagen"]["tmp_name"]);
    if ($info == false) {
        terminar("Error: el archivo no es una imagen.", $id);
    }
    $type = $info[2];

    switch ($type) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($_FILES["imagen"]["tmp_name"]);
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($_FILES["imagen"]["tmp_name"]);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($_FILES["imagen"]["tmp_name"]);
            break;
    }
    if ($image === false || $image === null) {
        terminar("Error: tipo de imagen no soportado (solo GIF, JPEG o PNG).", $id);
    }
}

// Actualizar el coche (los ? se rellenan con bind_param: s = texto, i = entero)
// Orden: matrícula, marca, modelo, año, fecha, reparar, cliente, id
try {
    $sql = "UPDATE COCHE
            SET matricula = ?, marca = ?, modelo = ?, anio = ?, fecha_entrada = ?, reparar = ?, id_cliente = ?
            WHERE id = ?;";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssisiii", $matricula, $marca, $modelo, $anio, $fechaEntrada, $reparar, $idCliente, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // 1062 = matrícula repetida, 1452 = el cliente no existe
    if ($e->getCode() == 1062) {
        terminar("Error: ya existe otro coche con esa matrícula (puede estar borrado).", $id);
    }
    if ($e->getCode() == 1452) {
        terminar("Error: el cliente seleccionado no existe.", $id);
    }
    terminar("Error: no se ha podido modificar el coche.", $id);
}

// Guardar la imagen nueva con el id del coche como nombre (sustituye a la anterior)
if ($image !== null) {
    // Carpeta uploads/coches (se crea si no existe)
    $dir = "uploads/coches";
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    switch ($type) {
        case IMAGETYPE_GIF:
            imagegif($image, $dir . "/" . $id);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($image, $dir . "/" . $id, 100);
            break;
        case IMAGETYPE_PNG:
            imagepng($image, $dir . "/" . $id);
            break;
    }
}

cerrarConexion($conexion);

// Volver al listado
header("Location: listado_coches.php");
exit;
?>
