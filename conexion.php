<?php
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "spa_cuidarte";

$conexion = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
echo "¡Conexión exitosa a la base de datos!";
?>