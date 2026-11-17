<?php
session_start();
// Seguridad: Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Recoger los datos enviados por la URL (GET)
$id_cita = $_GET['id'] ?? null;
$nuevo_estado = $_GET['estado'] ?? null;

// Validar que vengan los datos obligatorios y que el estado sea seguro
$estadosPermitidos = ['Confirmada', 'Cancelada', 'Por Validar', 'Completada', 'No Asistió', 'Vencida'];

if ($id_cita && $nuevo_estado && in_array($nuevo_estado, $estadosPermitidos)) {
    try {
        // Actualizar el estado de la cita en Neon (PostgreSQL)
        $stmt = $conexion->prepare("UPDATE citas SET estado = ? WHERE id_cita = ?");
        $stmt->execute([$nuevo_estado, $id_cita]);

        // Redirigir de vuelta al dashboard con éxito
        header("Location: dashboard.php?status=success");
        exit;

    } catch (Exception $e) {
        // Manejo de errores por si algo falla en la conexión
        echo "Error al actualizar el estado: " . $e->getMessage();
        exit;
    }
} else {
    // Si mandan un estado extraño o falta el ID, mandarlos de regreso al panel
    header("Location: dashboard.php?status=error");
    exit;
}
?>
