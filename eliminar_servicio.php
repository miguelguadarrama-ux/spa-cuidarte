<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$id_servicio = $_GET['id'] ?? null;

if ($id_servicio) {
    try {
        $stmt = $conexion->prepare("DELETE FROM servicios WHERE id_servicio = ?");
        $stmt->execute([$id_servicio]);

        header("Location: dashboard.php?status=servicio_eliminado");
        exit;
    } catch (Exception $e) {
        echo "Error al eliminar el servicio (puede estar asociado a una cita): " . $e->getMessage();
        exit;
    }
}

header("Location: dashboard.php");
exit;
?>