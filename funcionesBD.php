<?php
// FUNCIONES COMUNES: acceso a la BD, validación, mensajes y el "motor" del CRUD.
// Cada página (alta_cliente.php, listado_coches.php...) solo llama a UNA de las funciones del final:
//   mostrarFormulario · procesarGuardado · mostrarBuscar · procesarBuscar
//   mostrarListado · mostrarListadoFiltro · procesarBorrar
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/inc/entidades.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // solo se usa para los mensajes que se muestran tras redirigir
}

$errorBD = "";

/* ====================================================================== base de datos */
function bd() {
    static $conexion = null;
    if ($conexion === null) {
        $conexion = obtenerConexion();
    }
    return $conexion;
}

// SELECT preparado -> array de filas asociativas
function consultar($conexion, $sql, $tipos = "", $params = array()) {
    global $errorBD;
    try {
        $stmt = $conexion->prepare($sql);
        if ($tipos !== "") {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $filas = $res ? $res->fetch_all(MYSQLI_ASSOC) : array();
        $stmt->close();
        return $filas;
    } catch (mysqli_sql_exception $e) {
        $errorBD = $e->getMessage();
        return array();
    }
}

// INSERT / UPDATE / DELETE preparado -> array(ok, errno, mensaje, filas, id)
function ejecutar($conexion, $sql, $tipos = "", $params = array()) {
    try {
        $stmt = $conexion->prepare($sql);
        if ($tipos !== "") {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $r = array("ok" => true, "errno" => 0, "mensaje" => "", "filas" => $stmt->affected_rows, "id" => $stmt->insert_id);
        $stmt->close();
        return $r;
    } catch (mysqli_sql_exception $e) {
        return array("ok" => false, "errno" => (int)$e->getCode(), "mensaje" => $e->getMessage(), "filas" => 0, "id" => 0);
    }
}

function entidad($clave) {
    global $ENTIDADES;
    if (!isset($ENTIDADES[$clave])) {
        die("Entidad no válida.");
    }
    $def = $ENTIDADES[$clave];
    $def["clave"] = $clave;
    return $def;
}

/* ====================================================================== utilidades */
function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function mayus($s) { return function_exists("mb_strtoupper") ? mb_strtoupper($s, "UTF-8") : strtoupper($s); }
function longitud($s) { return function_exists("mb_strlen") ? mb_strlen($s, "UTF-8") : strlen($s); }

function fechaES($f) {
    if (!$f) { return ""; }
    $p = explode("-", $f);
    return $p[2] . "/" . $p[1] . "/" . $p[0];
}

function dineroES($v) {
    return number_format((float)$v, 2, ",", ".") . " €";
}

function minutosES($m) {
    $m = (int)$m;
    if ($m < 60) { return $m . " min"; }
    $h = intdiv($m, 60);
    $r = $m % 60;
    return $r === 0 ? $h . " h" : $h . " h " . $r . " min";
}

function badgeFranja($v) {
    return $v === "MANANA" ? '<span class="badge badge-manana">Mañana</span>' : '<span class="badge badge-tarde">Tarde</span>';
}

function alertaHTML($tipo, $texto, $cerrable = false) {
    $iconos = array("success" => "bi-check-circle-fill", "danger" => "bi-x-octagon-fill",
                    "warning" => "bi-exclamation-triangle-fill", "info" => "bi-info-circle-fill");
    return '<div class="alert alert-' . $tipo . ' d-flex align-items-start' . ($cerrable ? ' alert-dismissible fade show' : '') . '" role="alert">'
         . '<i class="bi ' . $iconos[$tipo] . ' me-2"></i><div>' . h($texto) . '</div>'
         . ($cerrable ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>' : '')
         . '</div>';
}

// Mensajes que sobreviven a una redirección (se guardan en la sesión y se muestran una vez)
function flash($tipo, $texto) {
    $_SESSION["flash"][] = array($tipo, $texto);
}

function flashHTML() {
    $html = "";
    if (!empty($_SESSION["flash"])) {
        foreach ($_SESSION["flash"] as $f) {
            $html .= alertaHTML($f[0], $f[1], true);
        }
        unset($_SESSION["flash"]);
    }
    return $html;
}

function redirigir($url) {
    header("Location: " . $url);
    exit;
}

// Solo se vuelve a páginas del propio proyecto (nombre.php?parametros)
function urlSegura($url, $porDefecto) {
    if (preg_match('/^[a-z_]+\.php(\?[^\s"\'<>]*)?$/', (string)$url)) {
        return $url;
    }
    return $porDefecto;
}

function paginaActual() {
    $q = $_SERVER["QUERY_STRING"] ?? "";
    return basename($_SERVER["SCRIPT_NAME"]) . ($q !== "" ? "?" . $q : "");
}

function abrirPagina() {
    include_once(__DIR__ . "/cabecera.html");
    echo "\n<main class=\"container py-4\">\n";
    echo flashHTML();
}

function cerrarPagina() {
    echo "\n</main>\n";
    include_once(__DIR__ . "/pie.html");
}

/* ====================================================================== validación */
// Devuelve array(valores, errores). Cada campo se valida según su "tipo" en inc/entidades.php
function validarCampos($def, $origen) {
    $valores = array();
    $errores = array();
    foreach ($def["campos"] as $nombre => $c) {
        $bruto = isset($origen[$nombre]) ? trim((string)$origen[$nombre]) : "";
        if ($bruto === "") {
            if (!empty($c["obligatorio"])) {
                $errores[$nombre] = "Este campo es obligatorio.";
            }
            $valores[$nombre] = null;
            continue;
        }
        switch ($c["tipo"]) {
            case "i":
                if (filter_var($bruto, FILTER_VALIDATE_INT) === false) { $errores[$nombre] = "Debe ser un número entero."; break; }
                $n = (int)$bruto;
                if (isset($c["min"]) && $n < $c["min"]) { $errores[$nombre] = "El valor mínimo es " . $c["min"] . "."; break; }
                if (isset($c["maxv"]) && $n > $c["maxv"]) { $errores[$nombre] = "El valor máximo es " . $c["maxv"] . "."; break; }
                $valores[$nombre] = $n;
                break;
            case "d":
                $bruto = str_replace(",", ".", $bruto);
                if (!is_numeric($bruto)) { $errores[$nombre] = "Debe ser un número."; break; }
                $n = round((float)$bruto, 2);
                if (isset($c["min"]) && $n < $c["min"]) { $errores[$nombre] = "El valor mínimo es " . $c["min"] . "."; break; }
                if ($n > 99999999.99) { $errores[$nombre] = "El importe es demasiado grande."; break; }
                $valores[$nombre] = $n;
                break;
            case "date":
                if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $bruto, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
                    $errores[$nombre] = "La fecha no es válida.";
                    break;
                }
                $valores[$nombre] = $bruto;
                break;
            case "e":
                if (!isset($c["valores"][$bruto])) { $errores[$nombre] = "Valor no permitido."; break; }
                $valores[$nombre] = $bruto;
                break;
            default: // "s" y "t" (texto)
                if (!empty($c["mayusculas"])) { $bruto = mayus($bruto); }
                if (isset($c["max"]) && longitud($bruto) > $c["max"]) { $errores[$nombre] = "Máximo " . $c["max"] . " caracteres."; break; }
                if (!empty($c["formato"]) && $c["formato"] === "email" && !filter_var($bruto, FILTER_VALIDATE_EMAIL)) {
                    $errores[$nombre] = "El correo electrónico no es válido.";
                    break;
                }
                if (!empty($c["patron"]) && !preg_match('/^(?:' . $c["patron"] . ')$/u', $bruto)) {
                    $errores[$nombre] = $c["errorPatron"] ?? "El formato no es válido.";
                    break;
                }
                $valores[$nombre] = $bruto;
        }
    }
    return array($valores, $errores);
}

// Tipos para bind_param según los campos de la entidad
function tiposBind($def) {
    $t = "";
    foreach ($def["campos"] as $c) {
        if ($c["tipo"] === "i") { $t .= "i"; }
        elseif ($c["tipo"] === "d") { $t .= "d"; }
        else { $t .= "s"; }
    }
    return $t;
}

// Imagen opcional (se guarda en la BD como BLOB). Devuelve array(contenido|null, error)
function leerImagenSubida() {
    if (!isset($_FILES["imagen"]) || $_FILES["imagen"]["error"] === UPLOAD_ERR_NO_FILE) {
        return array(null, "");
    }
    $f = $_FILES["imagen"];
    if ($f["error"] === UPLOAD_ERR_INI_SIZE || $f["error"] === UPLOAD_ERR_FORM_SIZE || $f["size"] > 2 * 1024 * 1024) {
        return array(null, "La imagen es demasiado grande (máximo 2 MB).");
    }
    if ($f["error"] !== UPLOAD_ERR_OK) {
        return array(null, "No se pudo subir la imagen (código " . $f["error"] . ").");
    }
    $info = @getimagesize($f["tmp_name"]);
    if (!$info || !in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP), true)) {
        return array(null, "Formato de imagen no permitido (usa JPG, PNG o WEBP).");
    }
    return array(file_get_contents($f["tmp_name"]), "");
}

/* ====================================================================== lógica de datos */
// Un registro (sin el BLOB) para rellenar el formulario de modificación
function cargarRegistro($def, $id) {
    $cols = array("id");
    foreach (array_keys($def["campos"]) as $n) { $cols[] = $n; }
    if (!empty($def["informativos"])) {
        foreach (array_keys($def["informativos"]) as $n) { $cols[] = $n; }
    }
    $filas = consultar(bd(), "SELECT " . implode(", ", $cols) . ", (imagen IS NOT NULL) AS tiene_imagen FROM " . $def["tabla"] . " WHERE id = ?", "i", array($id));
    return $filas ? $filas[0] : null;
}

// tiempo_calculado y precio_calculado de una reserva = suma de sus servicios
function recalcularReserva($idReserva) {
    return ejecutar(bd(), "UPDATE RESERVA SET
            tiempo_calculado = (SELECT COALESCE(SUM(tiempo_estimado), 0) FROM SERVICIO WHERE id_reserva = ?),
            precio_calculado = (SELECT COALESCE(SUM(precio_base), 0) FROM SERVICIO WHERE id_reserva = ?)
        WHERE id = ?", "iii", array($idReserva, $idReserva, $idReserva));
}

function mensajeErrorBD($def, $r) {
    switch ($r["errno"]) {
        case 1062: return $def["msgDuplicado"];
        case 1451: return $def["msgHijos"];
        case 1452: return "El registro relacionado que has elegido ya no existe.";
        case 3819:
        case 4025: return "Algún valor no cumple una restricción de la base de datos (revisa la franja horaria).";
        case 1406: return "Algún texto es demasiado largo para su campo.";
        default: return "Error de base de datos (código " . $r["errno"] . "): " . $r["mensaje"];
    }
}

function textoFila($def, $fila) {
    $t = "#" . $fila["id"];
    if (!empty($def["columnaTexto"]) && isset($fila[$def["columnaTexto"]])) {
        $v = (string)$fila[$def["columnaTexto"]];
        $t .= " · " . (longitud($v) > 50 ? substr($v, 0, 50) . "…" : $v);
    }
    return $t;
}

// WHERE dinámico del listado parametrizado
function construirFiltros($def, $origen) {
    $donde = array();
    $tipos = "";
    $params = array();
    $activos = 0;
    foreach ($def["filtros"] as $nombre => $f) {
        $v = trim((string)($origen[$nombre] ?? ""));
        if ($v === "") { continue; }
        $activos++;
        if ($f["op"] === "like") {
            $donde[] = $f["col"] . " LIKE ?";
            $tipos .= "s";
            $params[] = "%" . $v . "%";
            continue;
        }
        $operador = ($f["op"] === "gte") ? ">=" : (($f["op"] === "lte") ? "<=" : "=");
        $tipo = $f["tipo"] ?? "s";
        $donde[] = $f["col"] . " " . $operador . " ?";
        $tipos .= $tipo;
        if ($tipo === "i") { $params[] = (int)$v; }
        elseif ($tipo === "d") { $params[] = (float)str_replace(",", ".", $v); }
        else { $params[] = $v; }
    }
    return array($donde, $tipos, $params, $activos);
}

/* ====================================================================== piezas de HTML */
function celdaHTML($fila, $c) {
    $v = $fila[$c["clave"]] ?? null;
    $vacio = ($v === null || $v === "");
    $clase = (isset($c["movil"]) && $c["movil"] === false) ? ' class="d-none d-md-table-cell"' : "";
    $formato = $c["formato"];

    if ($formato === "imagen") {
        $html = !empty($fila["tiene_imagen"])
            ? '<img src="imagen.php?t=' . h($c["entidad"]) . '&amp;id=' . (int)$fila["id"] . '" class="miniatura" alt="Imagen">'
            : '<span class="miniatura miniatura-vacia"><i class="bi bi-image"></i></span>';
        return '<td' . $clase . '>' . $html . '</td>';
    }
    if ($vacio) {
        $html = '<span class="text-body-secondary">—</span>';
    } elseif ($formato === "id") {
        $html = '<span class="text-body-secondary">#' . h($v) . '</span>';
    } elseif ($formato === "moneda") {
        $html = dineroES($v);
    } elseif ($formato === "minutos") {
        $html = minutosES($v);
    } elseif ($formato === "fecha") {
        $html = fechaES($v);
    } elseif ($formato === "franja") {
        $html = badgeFranja($v);
    } elseif ($formato === "largo") {
        $html = '<div class="celda-larga" title="' . h($v) . '">' . h($v) . '</div>';
    } else {
        $html = h($v);
    }
    if (!empty($c["fuerte"]) && !$vacio) { $html = '<span class="fw-semibold">' . $html . '</span>'; }
    return '<td' . $clase . '>' . $html . '</td>';
}

function modalBorrarHTML($def, $fila, $volver) {
    $id = (int)$fila["id"];
    $idModal = "borrar-" . $def["clave"] . "-" . $id;
    $html = '<div class="modal fade" id="' . $idModal . '" tabindex="-1" aria-labelledby="' . $idModal . '-t" aria-hidden="true">'
        . '<div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="post" action="proceso_borrar_' . $def["slug"] . '.php">'
        . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="volver" value="' . h($volver) . '">'
        . '<div class="modal-header"><h2 class="modal-title h5 text-danger" id="' . $idModal . '-t"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar borrado</h2>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>'
        . '<div class="modal-body"><p class="mb-0">¿Seguro que quieres borrar ' . ($def["genero"] === "f" ? "la " : "el ") . h($def["singular"])
        . ' <strong>' . h(textoFila($def, $fila)) . '</strong>? Esta acción no se puede deshacer.</p></div>'
        . '<div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>'
        . '<button type="submit" class="btn btn-danger"><i class="bi bi-trash3 me-1"></i>Borrar</button></div>'
        . '</form></div></div>';
    return $html;
}

// Tabla de resultados (con botones Editar / Borrar y enlaces a registros relacionados)
function tablaHTML($clave, $filas) {
    global $errorBD;
    $def = entidad($clave);
    if ($errorBD !== "") {
        return alertaHTML("danger", "Error de base de datos: " . $errorBD);
    }
    if (!$filas) {
        return alertaHTML("info", "No hay registros que mostrar.");
    }
    $volver = urlSegura(paginaActual(), "listado_" . $clave . ".php");
    $html = '<p class="text-body-secondary small mb-2">' . count($filas) . ' registro(s)</p>'
          . '<div class="table-responsive shadow-sm rounded-3"><table class="table table-striped table-hover align-middle tabla-admin mb-0"><thead><tr>';
    foreach ($def["columnas"] as $c) {
        $clase = (isset($c["movil"]) && $c["movil"] === false) ? ' class="d-none d-md-table-cell"' : "";
        $html .= '<th' . $clase . '>' . h($c["etiqueta"]) . '</th>';
    }
    $html .= '<th class="text-end">Acciones</th></tr></thead><tbody>';
    foreach ($filas as $fila) {
        $html .= '<tr>';
        foreach ($def["columnas"] as $c) {
            $c["entidad"] = $clave;
            $html .= celdaHTML($fila, $c);
        }
        $id = (int)$fila["id"];
        $html .= '<td class="text-end text-nowrap">';
        foreach ($def["relacionados"] as $rel) {
            $html .= '<a class="btn btn-sm btn-outline-dark me-1" href="' . h(str_replace("{id}", (string)$id, $rel[2])) . '" title="' . h($rel[0]) . '">'
                   . '<i class="bi ' . $rel[1] . '"></i><span class="d-none d-xl-inline"> ' . h($rel[0]) . '</span></a>';
        }
        $html .= '<a class="btn btn-sm btn-outline-primary me-1" href="editar_' . $def["slug"] . '.php?id=' . $id . '" title="Editar">'
               . '<i class="bi bi-pencil-square"></i><span class="d-none d-xl-inline"> Editar</span></a>'
               . '<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#borrar-' . $clave . '-' . $id . '" title="Borrar">'
               . '<i class="bi bi-trash3"></i><span class="d-none d-xl-inline"> Borrar</span></button></td></tr>';
    }
    $html .= '</tbody></table></div>';
    foreach ($filas as $fila) {
        $html .= modalBorrarHTML($def, $fila, $volver);
    }
    return $html;
}

function cabeceraSeccion($def, $texto) {
    echo '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">'
       . '<h1 class="h2 text-primary mb-0"><i class="bi ' . $def["icono"] . ' me-2"></i>' . h($texto) . '</h1>'
       . '<a class="btn btn-secondary btn-hero px-4" href="alta_' . $def["slug"] . '.php"><i class="bi bi-plus-circle me-2"></i>' . h($def["etiquetaNuevo"]) . '</a></div>';
}

function listaOpciones($c) {
    global $OPCIONES;
    $lista = array();
    if (!empty($c["fuente"])) {
        return consultar(bd(), $OPCIONES[$c["fuente"]]);
    }
    if (!empty($c["valores"])) {
        foreach ($c["valores"] as $valor => $texto) {
            $lista[] = array("valor" => $valor, "texto" => $texto);
        }
    }
    return $lista;
}

// Control de formulario (campo de alta/edición o filtro) con etiqueta flotante de Bootstrap
function controlHTML($c, $valor, $error, $prefijo, $esFiltro) {
    $id = $prefijo . $c["nombre"];
    $texto = ($valor === null) ? "" : $valor;
    $obligatorio = (!$esFiltro && !empty($c["obligatorio"]));
    $req = $obligatorio ? " required" : "";
    $etiqueta = h($c["etiqueta"]) . ($obligatorio ? " *" : "");
    $clase = ($error !== "") ? " is-invalid" : "";

    if ($c["control"] === "select") {
        $campo = '<select class="form-select' . $clase . '" id="' . $id . '" name="' . $c["nombre"] . '"' . $req . '>'
               . '<option value="">' . ($esFiltro ? "Todos" : "— Selecciona —") . '</option>';
        foreach (listaOpciones($c) as $o) {
            $sel = ((string)$o["valor"] === (string)$texto) ? " selected" : "";
            $campo .= '<option value="' . h($o["valor"]) . '"' . $sel . '>' . h($o["texto"]) . '</option>';
        }
        $campo .= '</select>';
    } elseif ($c["control"] === "textarea") {
        $campo = '<textarea class="form-control' . $clase . '" id="' . $id . '" name="' . $c["nombre"] . '" placeholder="' . $etiqueta
               . '" style="height: 7rem"' . $req . '>' . h($texto) . '</textarea>';
    } else {
        $atributos = "";
        if ($c["control"] === "number") {
            if (isset($c["min"])) { $atributos .= ' min="' . $c["min"] . '"'; }
            if (isset($c["maxv"])) { $atributos .= ' max="' . $c["maxv"] . '"'; }
            $atributos .= ' step="' . ($c["step"] ?? 1) . '"';
        } else {
            if (isset($c["max"])) { $atributos .= ' maxlength="' . $c["max"] . '"'; }
            if (!empty($c["patron"])) { $atributos .= ' pattern="' . h($c["patron"]) . '"'; }
        }
        $campo = '<input type="' . $c["control"] . '" class="form-control' . $clase . '" id="' . $id . '" name="' . $c["nombre"]
               . '" placeholder="' . $etiqueta . '" value="' . h($texto) . '"' . $atributos . $req . '>';
    }
    $ancho = $esFiltro ? "col-md-4" : (($c["control"] === "textarea") ? "col-12" : "col-md-6");
    return '<div class="' . $ancho . '"><div class="form-floating">' . $campo . '<label for="' . $id . '">' . $etiqueta . '</label>'
         . '<div class="invalid-feedback">' . h($error) . '</div></div></div>';
}

// Formulario de alta / modificación dentro de un modal de Bootstrap (abierto desde PHP, sin JavaScript)
function paginaFormulario($clave, $modo, $datos, $errores = array(), $mensaje = "") {
    $def = entidad($clave);
    $editar = ($modo === "editar");
    $accion = ($editar ? "proceso_modificar_" : "proceso_alta_") . $def["slug"] . ".php";
    $volver = "listado_" . $clave . ".php";
    $titulo = $editar ? "Modificar " . $def["singular"] . " #" . (int)$datos["id"] : $def["etiquetaNuevo"];

    abrirPagina();
    echo '<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h2 text-primary mb-0"><i class="bi ' . $def["icono"]
       . ' me-2"></i>' . h($def["titulo"]) . '</h1><a class="btn btn-outline-primary" href="' . h($volver) . '"><i class="bi bi-arrow-left me-1"></i>Volver al listado</a></div>';

    echo '<div class="modal-backdrop fade show"></div>';
    echo '<div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="tituloModal"><div class="modal-dialog modal-lg modal-dialog-scrollable">';
    echo '<form class="modal-content" method="post" action="' . h($accion) . '" enctype="multipart/form-data">';
    echo '<div class="modal-header bg-primary text-white"><h2 class="modal-title h5" id="tituloModal">' . h($titulo) . '</h2>'
       . '<a href="' . h($volver) . '" class="btn-close btn-close-white" aria-label="Cerrar"></a></div>';
    echo '<div class="modal-body">';
    if ($mensaje !== "") { echo '<div class="mb-3">' . alertaHTML("danger", $mensaje) . '</div>'; }
    if ($editar) { echo '<input type="hidden" name="id" value="' . (int)$datos["id"] . '">'; }
    echo '<div class="row g-3">';
    if (!empty($def["ayuda"])) { echo '<div class="col-12">' . alertaHTML("info", $def["ayuda"]) . '</div>'; }
    if ($editar && !empty($def["informativos"])) {
        echo '<div class="col-12"><div class="border rounded-3 p-3 bg-light">';
        foreach ($def["informativos"] as $nombre => $i) {
            $v = $datos[$nombre] ?? 0;
            echo '<div><strong>' . h($i["etiqueta"]) . ':</strong> ' . ($i["formato"] === "moneda" ? dineroES($v) : minutosES($v)) . '</div>';
        }
        echo '</div></div>';
    }
    foreach ($def["campos"] as $nombre => $c) {
        $c["nombre"] = $nombre;
        echo controlHTML($c, $datos[$nombre] ?? "", $errores[$nombre] ?? "", "f_", false);
    }
    echo '<div class="col-12"><label class="form-label" for="f_imagen">Imagen (opcional · JPG, PNG o WEBP · máximo 2 MB)</label>'
       . '<input type="file" class="form-control' . (isset($errores["imagen"]) ? " is-invalid" : "") . '" id="f_imagen" name="imagen" accept="image/jpeg,image/png,image/webp">';
    if (isset($errores["imagen"])) { echo '<div class="invalid-feedback">' . h($errores["imagen"]) . '</div>'; }
    if ($editar && !empty($datos["tiene_imagen"])) {
        echo '<div class="mt-2 d-flex align-items-center gap-3"><img src="imagen.php?t=' . h($clave) . '&amp;id=' . (int)$datos["id"]
           . '" class="previsualizacion" alt="Imagen actual"><div class="form-check"><input class="form-check-input" type="checkbox" name="quitar_imagen" value="1" id="f_quitar">'
           . '<label class="form-check-label" for="f_quitar">Quitar la imagen actual</label></div></div>';
    }
    echo '</div></div><p class="text-body-secondary small mt-3 mb-0">Los campos marcados con * son obligatorios.</p></div>';
    echo '<div class="modal-footer"><a class="btn btn-outline-dark" href="' . h($volver) . '">Cancelar</a>'
       . '<button type="submit" class="btn btn-secondary px-4"><i class="bi bi-check2-circle me-1"></i>Guardar</button></div>';
    echo '</form></div></div>';
    cerrarPagina();
}

/* ====================================================================== PUNTOS DE ENTRADA (uno por página) */

// alta_X.php y editar_X.php
function mostrarFormulario($clave, $modo) {
    $def = entidad($clave);
    $datos = array("tiene_imagen" => 0);
    if ($modo === "editar") {
        $id = (int)($_GET["id"] ?? 0);
        $datos = cargarRegistro($def, $id);
        if (!$datos) {
            flash("danger", "No existe " . ($def["genero"] === "f" ? "la " : "el ") . $def["singular"] . " #" . $id . " (¿lo han borrado?).");
            redirigir("listado_" . $clave . ".php");
        }
    } else {
        // Valores iniciales; los desplegables pueden venir preseleccionados por la URL (?id_cliente=3)
        foreach ($def["campos"] as $nombre => $c) {
            if (isset($c["defecto"])) { $datos[$nombre] = ($c["defecto"] === "hoy") ? date("Y-m-d") : $c["defecto"]; }
            elseif ($c["control"] === "select" && isset($_GET[$nombre])) { $datos[$nombre] = (int)$_GET[$nombre]; }
            else { $datos[$nombre] = ""; }
        }
    }
    paginaFormulario($clave, $modo, $datos);
}

// proceso_alta_X.php y proceso_modificar_X.php
function procesarGuardado($clave, $modo) {
    $def = entidad($clave);
    $editar = ($modo === "modificar");
    $volver = "listado_" . $clave . ".php";
    $conexion = bd();
    $genero = ($def["genero"] === "f");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirigir($volver);
    }

    $id = 0;
    $actual = null;
    if ($editar) {
        $id = (int)($_POST["id"] ?? 0);
        $actual = cargarRegistro($def, $id);
        if (!$actual) {
            flash("danger", "No existe " . ($genero ? "la " : "el ") . $def["singular"] . " #" . $id . " (¿lo han borrado?).");
            redirigir($volver);
        }
    }

    // Si el formulario supera post_max_size, PHP descarta todo $_POST
    if (empty($_POST) && !empty($_SERVER["CONTENT_LENGTH"])) {
        $datos = $actual ? $actual : array("tiene_imagen" => 0);
        paginaFormulario($clave, $editar ? "editar" : "alta", $datos, array("imagen" => "El envío es demasiado grande (la imagen no puede superar 2 MB)."),
            "No se ha guardado nada.");
        exit;
    }

    list($valores, $errores) = validarCampos($def, $_POST);
    list($blob, $errorImagen) = leerImagenSubida();
    if ($errorImagen !== "") { $errores["imagen"] = $errorImagen; }

    // Datos que se vuelven a mostrar en el formulario si algo falla
    $datos = $actual ? $actual : array("tiene_imagen" => 0);
    foreach (array_keys($def["campos"]) as $nombre) {
        $datos[$nombre] = $_POST[$nombre] ?? "";
    }
    $modoForm = $editar ? "editar" : "alta";

    if ($errores) {
        $mensaje = "Revisa los campos marcados en rojo.";
        if (isset($errores["imagen"]) && count($errores) === 1) { $mensaje = "No se ha guardado nada: " . $errores["imagen"]; }
        paginaFormulario($clave, $modoForm, $datos, $errores, $mensaje);
        exit;
    }

    if ($editar) {
        $asignaciones = array();
        foreach (array_keys($valores) as $col) { $asignaciones[] = $col . " = ?"; }
        $tipos = tiposBind($def);
        $params = array_values($valores);
        if ($blob !== null) { $asignaciones[] = "imagen = ?"; $tipos .= "s"; $params[] = $blob; }
        elseif (!empty($_POST["quitar_imagen"])) { $asignaciones[] = "imagen = NULL"; }
        $tipos .= "i";
        $params[] = $id;
        $r = ejecutar($conexion, "UPDATE " . $def["tabla"] . " SET " . implode(", ", $asignaciones) . " WHERE id = ?", $tipos, $params);
    } else {
        $columnas = array_keys($valores);
        $tipos = tiposBind($def);
        $params = array_values($valores);
        if (!empty($def["insertarExtra"])) {
            foreach ($def["insertarExtra"] as $col => $info) { $columnas[] = $col; $tipos .= $info[0]; $params[] = $info[1]; }
        }
        if ($blob !== null) { $columnas[] = "imagen"; $tipos .= "s"; $params[] = $blob; }
        $r = ejecutar($conexion, "INSERT INTO " . $def["tabla"] . " (" . implode(", ", $columnas) . ") VALUES ("
            . implode(", ", array_fill(0, count($columnas), "?")) . ")", $tipos, $params);
    }

    if (!$r["ok"]) {
        paginaFormulario($clave, $modoForm, $datos, array(), "No se ha guardado nada. " . mensajeErrorBD($def, $r));
        exit;
    }

    // Los servicios mantienen al día el tiempo y el precio estimados de su reserva
    if (!empty($def["recalcula"])) {
        $fk = $def["recalcula"];
        recalcularReserva($valores[$fk]);
        if ($editar && (int)$actual[$fk] !== (int)$valores[$fk]) { recalcularReserva($actual[$fk]); }
    }

    $idFinal = $editar ? $id : $r["id"];
    $nombre = (!empty($def["nombreRegistro"]) && isset($valores[$def["nombreRegistro"]])) ? " «" . $valores[$def["nombreRegistro"]] . "»" : "";
    $sustantivo = ucfirst($def["singular"]) . $nombre . " (#" . $idFinal . ")";
    if ($editar) {
        flash("success", $sustantivo . ($genero ? " actualizada" : " actualizado") . " correctamente.");
    } else {
        flash("success", $sustantivo . ($genero ? " creada" : " creado") . " correctamente.");
    }
    redirigir($volver);
}

// buscar_X.php
function mostrarBuscar($clave) {
    $def = entidad($clave);
    abrirPagina();
    cabeceraSeccion($def, "Buscar " . ($def["genero"] === "f" ? "una " : "un ") . $def["singular"]);
    echo '<form class="card card-spa border-0 shadow-sm" method="get" action="proceso_buscar_' . $def["slug"] . '.php"><div class="card-body">'
       . '<div class="form-floating mb-3"><input type="search" class="form-control" id="q" name="q" placeholder="' . h($def["busquedaEtiqueta"]) . '" autofocus>'
       . '<label for="q">' . h($def["busquedaEtiqueta"]) . '</label></div>'
       . '<button type="submit" class="btn btn-primary btn-hero me-2"><i class="bi bi-search me-1"></i>Buscar</button>'
       . '<a class="btn btn-outline-dark" href="listado_' . $clave . '.php">Ver todo</a></div></form>';
    cerrarPagina();
}

// proceso_buscar_X.php
function procesarBuscar($clave) {
    $def = entidad($clave);
    $q = trim((string)($_GET["q"] ?? ""));
    $filas = consultar(bd(), $def["select"] . " WHERE " . $def["busqueda"] . " LIKE ? ORDER BY " . $def["orden"], "s", array("%" . $q . "%"));
    abrirPagina();
    cabeceraSeccion($def, "Resultado de la búsqueda");
    echo '<p class="text-body-secondary">' . h($def["titulo"]) . ' que contienen «<strong>' . h($q) . '</strong>»</p>';
    echo tablaHTML($clave, $filas);
    echo '<a class="btn btn-outline-primary mt-3" href="buscar_' . $def["slug"] . '.php"><i class="bi bi-arrow-left me-1"></i>Nueva búsqueda</a>';
    cerrarPagina();
}

// listado_Xs.php (sin parametrizar)
function mostrarListado($clave) {
    $def = entidad($clave);
    $filas = consultar(bd(), $def["select"] . " ORDER BY " . $def["orden"]);
    abrirPagina();
    cabeceraSeccion($def, "Listado de " . strtolower($def["titulo"]));
    echo tablaHTML($clave, $filas);
    cerrarPagina();
}

// listado_Xs_filtro.php (parametrizado)
function mostrarListadoFiltro($clave) {
    $def = entidad($clave);
    $filas = null;
    if (isset($_GET["btnFiltrar"])) {
        list($donde, $tipos, $params) = construirFiltros($def, $_GET);
        $sql = $def["select"] . ($donde ? " WHERE " . implode(" AND ", $donde) : "") . " ORDER BY " . $def["orden"];
        $filas = consultar(bd(), $sql, $tipos, $params);
    }
    abrirPagina();
    cabeceraSeccion($def, "Listado filtrado de " . strtolower($def["titulo"]));
    echo '<form class="card card-spa border-0 shadow-sm mb-4" method="get" action="listado_' . $clave . '_filtro.php"><div class="card-body"><div class="row g-3">';
    foreach ($def["filtros"] as $nombre => $f) {
        $f["nombre"] = $nombre;
        echo controlHTML($f, $_GET[$nombre] ?? "", "", "b_", true);
    }
    echo '</div><div class="mt-3"><button type="submit" name="btnFiltrar" value="1" class="btn btn-primary btn-hero me-2"><i class="bi bi-funnel me-1"></i>Aplicar filtros</button>'
       . '<a class="btn btn-outline-dark" href="listado_' . $clave . '_filtro.php">Limpiar</a></div></div></form>';
    if ($filas === null) {
        echo alertaHTML("info", "Rellena los filtros que quieras y pulsa «Aplicar filtros». Sin ningún filtro se muestran todos los registros.");
    } else {
        echo tablaHTML($clave, $filas);
    }
    cerrarPagina();
}

// proceso_borrar_X.php
function procesarBorrar($clave) {
    $def = entidad($clave);
    $volver = urlSegura($_POST["volver"] ?? "", "listado_" . $clave . ".php");
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirigir($volver);
    }
    $id = (int)($_POST["id"] ?? 0);
    $actual = cargarRegistro($def, $id);
    $el = ($def["genero"] === "f") ? "la " : "el ";
    if (!$actual) {
        flash("warning", "No existe " . $el . $def["singular"] . " #" . $id . " (¿ya estaba borrad" . ($def["genero"] === "f" ? "a" : "o") . "?).");
        redirigir($volver);
    }
    $r = ejecutar(bd(), "DELETE FROM " . $def["tabla"] . " WHERE id = ?", "i", array($id));
    if ($r["ok"]) {
        if (!empty($def["recalcula"])) { recalcularReserva($actual[$def["recalcula"]]); }
        flash("success", ucfirst($def["singular"]) . " #" . $id . ($def["genero"] === "f" ? " borrada" : " borrado") . " correctamente.");
    } else {
        flash("danger", "No se ha borrado " . $el . $def["singular"] . " #" . $id . ". " . mensajeErrorBD($def, $r));
    }
    redirigir($volver);
}
