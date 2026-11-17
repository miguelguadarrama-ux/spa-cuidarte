<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';

    if (!empty($nombre) && !empty($correo)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO cliente (nombre, telefono, correo) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $telefono, $correo]);

            header("Location: dashboard.php?status=cliente_creado");
            exit;
        } catch (Exception $e) {
            echo "Error al registrar el cliente: " . $e->getMessage();
            exit;
        }
    }
}

header("Location: dashboard.php");
exit;
?>