<?php
require_once("config.php");
$conexion = obtenerConexion();

$nombre = $_POST['txtNombre'];
$apellidos = $_POST['txtApellidos'];
$telefono = $_POST['txtTelefono'];
$idprovincia = $_POST['lstProvincia'];

$sql = "INSERT INTO cliente(`idcliente`, `nombre`, `apellidos`, `telefono`, `idprovincia`) 
                VALUES (null, '" . $nombre . "', '" . $apellidos . "', '" . $telefono . "', $idprovincia);";

$resultado = mysqli_query($conexion, $sql);

if (mysqli_errno($conexion) != 0) {
    $numerror = mysqli_errno($conexion);
    $descrerror = mysqli_error($conexion);
    $mensaje = "<h2 class='text-center mt-5'>Se ha producido un error numero $numerror que corresponde a: $descrerror </h2>";
} else {
    $mensaje = "<h2 class='text-center mt-5'>Cliente insertado correctamente</h2>";
}

header("refresh:5;url=index.php");

include_once("cabecera.html");

echo "<div class='container'>" . $mensaje . "</div>";
?>
</body>
</html>
