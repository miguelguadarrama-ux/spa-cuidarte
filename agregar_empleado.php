<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'empleado';

    if (!empty($nombre) && !empty($correo) && !empty($password)) {
        // Encriptar contraseña para seguridad
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conexion->prepare("INSERT INTO empleados (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $correo, $passwordHash, $rol]);

            header("Location: dashboard.php?status=empleado_creado");
            exit;
        } catch (Exception $e) {
            echo "Error al registrar empleado: " . $e->getMessage();
            exit;
        }
    }
}

header("Location: dashboard.php");
exit;
?>