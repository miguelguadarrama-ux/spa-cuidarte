<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$id_empleado = $_GET['id'] ?? null;

if ($id_empleado) {
    try {
        $stmt = $conexion->prepare("DELETE FROM empleados WHERE id_empleado = ?");
        $stmt->execute([$id_empleado]);

        header("Location: dashboard.php?status=empleado_eliminado");
        exit;
    } catch (Exception $e) {
        echo "Error al dar de baja al empleado (puede tener citas pendientes asignadas): " . $e->getMessage();
        exit;
    }
}

header("Location: dashboard.php");
exit;
?>