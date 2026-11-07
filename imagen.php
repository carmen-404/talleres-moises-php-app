<?php
// Devuelve la imagen (BLOB) de un registro: imagen.php?t=clientes&id=3
require_once("funcionesBD.php");
session_write_close();

$t = $_GET["t"] ?? "";
$id = (int)($_GET["id"] ?? 0);
if (!isset($ENTIDADES[$t])) {
    http_response_code(404);
    exit;
}
$filas = consultar(bd(), "SELECT imagen FROM " . $ENTIDADES[$t]["tabla"] . " WHERE id = ? AND imagen IS NOT NULL", "i", array($id));
if (!$filas) {
    http_response_code(404);
    exit;
}
$info = @getimagesizefromstring($filas[0]["imagen"]);
header("Content-Type: " . ($info ? $info["mime"] : "application/octet-stream"));
header("Cache-Control: no-cache");
echo $filas[0]["imagen"];
