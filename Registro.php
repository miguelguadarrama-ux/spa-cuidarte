<?php
session_start();
include 'conexion.php';

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nombre) || empty($telefono) || empty($correo) || empty($password)) {
        $mensaje = "Por favor completa todos los campos.";
        $tipo_alerta = "error";
    } else {
        try {
            // Verificamos si el correo ya existe en clientes o empleados
            $stmt = $conexion->prepare("SELECT correo FROM cliente WHERE correo = ? UNION SELECT correo FROM empleados WHERE correo = ?");
            $stmt->execute([$correo, $correo]);
            
            if ($stmt->fetch()) {
                $mensaje = "El correo electrónico ya está registrado.";
                $tipo_alerta = "error";
            } else {
                // Ciframos la contraseña de forma segura
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertamos el nuevo cliente con su teléfono y rol 'usuario'
                $stmtIns = $conexion->prepare("INSERT INTO cliente (nombre, telefono, correo, password, rol) VALUES (?, ?, ?, ?, 'usuario')");
                $stmtIns->execute([$nombre, $telefono, $correo, $password_hash]);

                $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión.";
                $tipo_alerta = "success";
            }
        } catch (Exception $e) {
            $mensaje = "Error en el servidor: " . $e->getMessage();
            $tipo_alerta = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Consientt Spa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container" style="max-width: 450px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2><i class="fas fa-spa"></i> Crear Cuenta</h2>
            <p style="color: #666;">Regístrate para agendar tus citas fácilmente</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div style="padding: 12px; margin-bottom: 15px; border-radius: 6px; text-align: center; background: <?php echo ($tipo_alerta === 'success') ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo ($tipo_alerta === 'success') ? '#155724' : '#721c24'; ?>;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px;" placeholder="Tu nombre">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px;" placeholder="Tu número de teléfono">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px;" placeholder="correo@ejemplo.com">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px;" placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; background: #b8860b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Registrarse</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <p>¿Ya tienes una cuenta? <a href="login.php" style="color: #b8860b; text-decoration: none; font-weight: bold;">Inicia sesión</a></p>
        </div>
    </div>
</body>
</html>
