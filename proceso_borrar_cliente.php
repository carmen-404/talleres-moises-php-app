<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$idcliente = $_POST['idcliente'];

$sql = "DELETE FROM cliente WHERE idcliente = $idcliente;";

$resultado = mysqli_query($conexion, $sql);

if (mysqli_errno($conexion) != 0) {
    $numerror = mysqli_errno($conexion);
    $descrerror = mysqli_error($conexion);
    $mensaje = "<h2 class='text-center mt-5 text-danger fw-bold'>Se ha producido un error número $numerror: $descrerror</h2>";
} else {
    $mensaje = "<h2 class='text-center mt-5 text-success fw-bold'>Cliente borrado correctamente</h2>";
}

header("refresh:5;url=index.php");

include_once("cabecera.html");

echo "<div class='container mt-5'>" . $mensaje . "</div>";
?>

</body>
</html>