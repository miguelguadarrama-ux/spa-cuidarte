<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$id_cliente = $_GET['id'] ?? null;

if ($id_cliente) {
    try {
        // Eliminar el cliente de la base de datos
        $stmt = $conexion->prepare("DELETE FROM cliente WHERE idcliente = ?");
        $stmt->execute([$id_cliente]);

        header("Location: dashboard.php?status=cliente_eliminado");
        exit;
    } catch (Exception $e) {
        echo "Error al eliminar el cliente (es posible que tenga citas asociadas): " . $e->getMessage();
        exit;
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>