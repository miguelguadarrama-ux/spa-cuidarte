<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? 0;

    if (!empty($nombre) && !empty($precio)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO servicios (nombre, precio) VALUES (?, ?)");
            $stmt->execute([$nombre, $precio]);

            header("Location: dashboard.php?status=servicio_creado");
            exit;
        } catch (Exception $e) {
            echo "Error al registrar el servicio: " . $e->getMessage();
            exit;
        }
    }
}

header("Location: dashboard.php");
exit;
?>