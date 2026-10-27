<?php

$dsn = "pgsql:host=ep-patient-breeze-b5e6cx5s-pooler.c-7.us-east-2.aws.neon.tech;dbname=neondb;sslmode=require";
$usuario = "neondb_owner";
$contrasena = "npg_kj6suWBPJQz1"; 

try {
    $conexion = new PDO($dsn, $usuario, $contrasena, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos en la nube: " . $e->getMessage());
}
?>
