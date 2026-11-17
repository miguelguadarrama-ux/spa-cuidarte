<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$id_empleado = $_GET['id'] ?? null;
if (!$id_empleado) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $rol = $_POST['rol'] ?? '';

    try {
        $stmt = $conexion->prepare("UPDATE empleados SET nombre = ?, correo = ?, rol = ? WHERE id_empleado = ?");
        $stmt->execute([$nombre, $correo, $rol, $id_empleado]);

        header("Location: dashboard.php?status=empleado_actualizado");
        exit;
    } catch (Exception $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
    }
}

$stmt = $conexion->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
$stmt->execute([$id_empleado]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empleado | Consientt Spa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: var(--bg-cream, #FDFBF7);">
    <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px;">
        <h2 style="margin-bottom: 20px; color: #271a2e;">Editar Empleado #<?php echo $empleado['id_empleado']; ?></h2>
        
        <?php if (!empty($mensaje)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form action="editar_empleado.php?id=<?php echo $id_empleado; ?>" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Nombre Completo</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($empleado['nombre']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Correo Electrónico</label>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($empleado['correo']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
           <!-- Rol fijo como Admin en edición -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Rol</label>
                <input type="text" value="Admin" disabled style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; background: #f5f5f5; color: #555;">
                <input type="hidden" name="rol" value="admin">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: #3a1e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; flex: 1;">Guardar Cambios</button>
                <a href="dashboard.php" style="background: #e0e0e0; color: #333; padding: 10px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
