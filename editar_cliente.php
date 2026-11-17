<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

$id_cliente = $_GET['id'] ?? null;

if (!$id_cliente) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";

// Si se envió el formulario para actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';

    try {
        $stmt_update = $conexion->prepare("UPDATE cliente SET nombre = ?, telefono = ?, correo = ? WHERE idcliente = ?");
        $stmt_update->execute([$nombre, $telefono, $correo, $id_cliente]);
        
        header("Location: dashboard.php?status=cliente_actualizado");
        exit;
    } catch (Exception $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
    }
}

// Obtener los datos actuales del cliente
$stmt = $conexion->prepare("SELECT * FROM cliente WHERE idcliente = ?");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente | Consientt Spa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: var(--bg-cream, #FDFBF7);">

    <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px;">
        <h2 style="margin-bottom: 20px; font-family: 'Plus Jakarta Sans', sans-serif; color: #271a2e;">Editar Cliente #<?php echo $cliente['idcliente']; ?></h2>
        
        <?php if (!empty($mensaje)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form action="editar_cliente.php?id=<?php echo $id_cliente; ?>" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Nombre Completo</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Teléfono</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Correo Electrónico</label>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($cliente['correo']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: #3a1e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; flex: 1;">Guardar Cambios</button>
                <a href="dashboard.php" style="background: #e0e0e0; color: #333; padding: 10px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">Cancelar</a>
            </div>
        </form>
    </div>

</body>
</html>