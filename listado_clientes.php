<?php
require_once("config.php");
mostrarListado("clientes");

$conexion = obtenerConexion();

$sql = "SELECT c.*, p.provincia AS provinciadesc FROM cliente c LEFT JOIN provincias p ON c.idprovincia = p.idprovincia ORDER BY c.idcliente ASC;";

$resultado = mysqli_query($conexion, $sql);

$mensaje = "<h2 class='text-center mb-4'>Listado de clientes</h2>";
$mensaje .= "<table class='table table-striped table-hover align-middle'>";
$mensaje .= "<thead class='table-dark'><tr><th>ID</th><th>NOMBRE</th><th>APELLIDOS</th><th>TELÉFONO</th><th>PROVINCIA</th><th>ACCIÓN</th></tr></thead>";
$mensaje .= "<tbody>";

while ($fila = mysqli_fetch_assoc($resultado)) {
    $mensaje .= "<tr><td>" . $fila['idcliente'] . "</td>";
    $mensaje .= "<td>" . $fila['nombre'] . "</td>";
    $mensaje .= "<td>" . $fila['apellidos'] . "</td>";
    $mensaje .= "<td>" . $fila['telefono'] . "</td>";
    $mensaje .= "<td>" . $fila['provinciadesc'] . "</td>";

    $mensaje .= "<td><form class='d-inline me-1' action='editar_cliente.php' method='post'>";
    $mensaje .= "<input type='hidden' name='cliente' value='" . htmlspecialchars(json_encode($fila), ENT_QUOTES) . "' />";
    $mensaje .= "<button name='Editar' class='btn btn-primary btn-sm'><i class='bi bi-pencil-square'></i></button></form>";

    $mensaje .= "<form class='d-inline' action='proceso_borrar_cliente.php' method='post'>";
    $mensaje .= "<input type='hidden' name='idcliente' value='" . $fila['idcliente'] . "' />";
    $mensaje .= "<button name='Borrar' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i></button></form>";

    $mensaje .= "</td></tr>";
}

$mensaje .= "</tbody></table>";

include_once("cabecera.html");

echo "<div class='container mt-4'>" . $mensaje . "</div>";
?>
