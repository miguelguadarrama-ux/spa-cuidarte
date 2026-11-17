<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$id_servicio = $_GET['id'] ?? null;
if (!$id_servicio) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? 0;

    try {
        $stmt = $conexion->prepare("UPDATE servicios SET nombre = ?, precio = ? WHERE id_servicio = ?");
        $stmt->execute([$nombre, $precio, $id_servicio]);

        header("Location: dashboard.php?status=servicio_actualizado");
        exit;
    } catch (Exception $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
    }
}

$stmt = $conexion->prepare("SELECT * FROM servicios WHERE id_servicio = ?");
$stmt->execute([$id_servicio]);
$servicio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$servicio) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servicio | Consientt Spa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: var(--bg-cream, #FDFBF7);">
    <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px;">
        <h2 style="margin-bottom: 20px; color: #271a2e;">Editar Servicio #<?php echo $servicio['id_servicio']; ?></h2>
        
        <?php if (!empty($mensaje)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form action="editar_servicio.php?id=<?php echo $id_servicio; ?>" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Nombre del Servicio</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($servicio['nombre']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Precio Base ($)</label>
                <input type="number" step="0.01" name="precio" value="<?php echo htmlspecialchars($servicio['precio']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: #3a1e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; flex: 1;">Guardar Cambios</button>
                <a href="dashboard.php" style="background: #e0e0e0; color: #333; padding: 10px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>