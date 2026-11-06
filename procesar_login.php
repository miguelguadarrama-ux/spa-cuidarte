<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($correo) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Por favor completa todos los campos."]);
        exit;
    }

    try {
        // 1. Primero buscamos en la tabla de empleados (para tu amiga y su hermana / admin)
        $stmt = $conexion->prepare("SELECT id_empleado, nombre, correo, password, rol FROM empleados WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $tipo = 'empleado';

        // 2. Si no está en empleados, buscamos en la tabla de clientes
        if (!$usuario) {
            $stmt = $conexion->prepare("SELECT idCliente AS id_empleado, nombre, correo, password, rol FROM cliente WHERE correo = ?");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $tipo = 'cliente';
        }

        if (!$usuario) {
            echo json_encode(["status" => "error", "message" => "El correo o la contraseña son incorrectos."]);
            exit;
        }

        // 3. Verificamos la contraseña (asumiendo que está hasheada con password_hash)
        if (password_verify($password, $usuario['password'])) {
            // Guardamos los datos esenciales en la sesión de PHP
            $_SESSION['id_usuario'] = $usuario['id_empleado'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol']; // 'admin' o 'usuario'

            // Determinamos a dónde redirigir según el rol
            $redirect = ($usuario['rol'] === 'admin') ? 'admin.php' : 'index.html';

            echo json_encode([
                "status" => "success", 
                "message" => "¡Bienvenido de nuevo, " . $usuario['nombre'] . "!",
                "redirect" => $redirect
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "El correo o la contraseña son incorrectos."]);
        }

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}

$conexion = null;
?>