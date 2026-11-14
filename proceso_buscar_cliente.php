<?php
require_once("config.php");
$conexion = obtenerConexion();

$nombre_cliente = isset($_GET['txtNombreCliente']) ? trim($_GET['txtNombreCliente']) : "";

$sql = "SELECT c.*, p.municipio AS municipiodesc 
        FROM cliente c 
        LEFT JOIN CODIGO_POSTAL p ON c.idprovincia = p.id 
        WHERE c.nombre LIKE '%$nombre_cliente%';";

$resultado = mysqli_query($conexion, $sql);

if ($resultado && mysqli_num_rows($resultado) > 0) { 
    $mensaje = "<div class='container mt-5'>";
    $mensaje .= "<h2 class='text-center mb-4 text-primary fw-bold'>Resultado de la búsqueda</h2>";
    $mensaje .= "<div class='table-responsive'>";
    $mensaje .= "<table class='table table-striped table-hover align-middle shadow-sm'>";
    $mensaje .= "<thead class='table-dark'><tr><th>ID</th><th>NOMBRE</th><th>APELLIDOS</th><th>TELÉFONO</th><th>MUNICIPIO</th><th>ACCIÓN</th></tr></thead>";
    $mensaje .= "<tbody>";
    
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $mensaje .= "<tr>";
        $mensaje .= "<td>" . $fila['idcliente'] . "</td>";
        $mensaje .= "<td>" . $fila['nombre'] . "</td>";
        $mensaje .= "<td>" . $fila['apellidos'] . "</td>";
        $mensaje .= "<td>" . $fila['telefono'] . "</td>";
        $mensaje .= "<td>" . $fila['municipiodesc'] . "</td>";

        $mensaje .= "<td class='d-flex gap-2'>";
        

        $mensaje .= "<form action='editar_cliente.php' method='post' class='m-0'>";
        $mensaje .= "<input type='hidden' name='cliente' value='" . htmlspecialchars(json_encode($fila), ENT_QUOTES) . "' />";
        $mensaje .= "<button type='submit' name='Editar' class='btn btn-primary btn-sm'><i class='bi bi-pencil-square'></i></button>";
        $mensaje .= "</form>";


        $mensaje .= "<form action='proceso_borrar_cliente.php' method='post' class='m-0'>";
        $mensaje .= "<input type='hidden' name='idcliente' value='" . $fila['idcliente'] . "' />";
        $mensaje .= "<button type='submit' name='btnBorrar' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i></button>";
        $mensaje .= "</form>";

        $mensaje .= "</td>";
        $mensaje .= "</tr>";
    }
    
    $mensaje .= "</tbody></table></div></div>"; 
} else { 
    $mensaje = "<div class='container mt-5 text-center'>";
    $mensaje .= "<div class='alert alert-warning shadow-sm p-4 d-inline-block' role='alert'>";
    $mensaje .= "<h4 class='alert-heading fw-bold'>Cliente no encontrado</h4>";
    $mensaje .= "<p class='mb-0'>No hay ningún cliente registrado con el nombre <strong>" . htmlspecialchars($nombre_cliente) . "</strong>.</p>";
    $mensaje .= "</div></div>";
}

include_once("cabecera.html");
echo $mensaje;
?>

</body>
</html>
