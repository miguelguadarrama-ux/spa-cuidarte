<?php
session_start();
include 'conexion.php';

$id_cita = $_GET['id'] ?? $_POST['id_cita'] ?? null;

if (!$id_cita || !is_numeric($id_cita)) {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";
$tipoMensaje = "";

// Si se envía el formulario para guardar el nuevo horario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevaFecha = trim($_POST['nueva_fecha'] ?? '');
    $nuevaHora = trim($_POST['nueva_hora'] ?? '');
    $nuevoEmpleado = $_POST['id_empleado'] ?? null;

    if (empty($nuevaFecha) || empty($nuevaHora)) {
        $mensaje = "Selecciona una nueva fecha y una hora.";
        $tipoMensaje = "error";
    } else {
        $horaLimpia = (strlen($nuevaHora) >= 5) ? substr($nuevaHora, 0, 5) . ':00' : '09:00:00';
        $nuevaFechaHora = $nuevaFecha . ' ' . $horaLimpia;

        try {
            if ($nuevoEmpleado && is_numeric($nuevoEmpleado)) {
                $stmtUpd = $conexion->prepare("UPDATE citas SET fecha_y_hora = ?, id_empleado = ? WHERE id_cita = ?");
                $stmtUpd->execute([$nuevaFechaHora, (int)$nuevoEmpleado, (int)$id_cita]);
            } else {
                $stmtUpd = $conexion->prepare("UPDATE citas SET fecha_y_hora = ? WHERE id_cita = ?");
                $stmtUpd->execute([$nuevaFechaHora, (int)$id_cita]);
            }

            header("Location: dashboard.php?reagendada=1");
            exit;
        } catch (Exception $e) {
            $mensaje = "No se pudo reagendar: " . $e->getMessage();
            $tipoMensaje = "error";
        }
    }
}

// Consultar datos actuales de la cita
$sqlCita = "SELECT c.id_cita, c.fecha_y_hora, c.estado, c.id_empleado,
                   cl.nombre AS cliente_nombre, e.nombre AS empleado_nombre
            FROM citas c
            LEFT JOIN cliente cl ON c.id_cliente = cl.idcliente
            LEFT JOIN empleados e ON c.id_empleado = e.id_empleado
            WHERE c.id_cita = ?";
$stmtCita = $conexion->prepare($sqlCita);
$stmtCita->execute([(int)$id_cita]);
$cita = $stmtCita->fetch(PDO::FETCH_ASSOC);

if (!$cita) {
    header("Location: dashboard.php");
    exit;
}

// Consultar lista de empleados para permitir cambio opcional
$empleados = $conexion->query("SELECT id_empleado, nombre FROM empleados ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$fechaActual = date('Y-m-d', strtotime($cita['fecha_y_hora']));
$horaActual = date('H:i', strtotime($cita['fecha_y_hora']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reagendar Cita #<?= (int)$cita['id_cita'] ?> | Consientt Spa</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .reagendar-box {
            max-width: 520px;
            margin: 50px auto;
            background: #fff;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }
        .reagendar-box h2 {
            margin-top: 0;
            color: #3a1e3a;
        }
        .campo-form {
            margin-bottom: 16px;
        }
        .campo-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .campo-form input,
        .campo-form select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        .acciones-reagendar {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-guardar {
            background: #3a1e3a;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-cancelar {
            background: #eee;
            color: #333;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
        }
        .alerta-error {
            background: #fde8e8;
            color: #9b1c1c;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
    <div class="reagendar-box">
        <h2>Reagendar Cita #<?= (int)$cita['id_cita'] ?></h2>
        <p><strong>Cliente:</strong> <?= htmlspecialchars($cita['cliente_nombre'] ?? 'Cliente') ?></p>
        <p><strong>Horario actual:</strong> <?= htmlspecialchars($cita['fecha_y_hora']) ?></p>

        <?php if ($mensaje): ?>
            <div class="alerta-error"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id_cita" value="<?= (int)$cita['id_cita'] ?>">

            <div class="campo-form">
                <label for="nueva_fecha">Nueva fecha</label>
                <input type="date" id="nueva_fecha" name="nueva_fecha" value="<?= htmlspecialchars($fechaActual) ?>" required>
            </div>

            <div class="campo-form">
                <label for="nueva_hora">Nueva hora</label>
                <input type="time" id="nueva_hora" name="nueva_hora" value="<?= htmlspecialchars($horaActual) ?>" required>
            </div>

            <div class="campo-form">
                <label for="id_empleado">Especialista</label>
                <select id="id_empleado" name="id_empleado">
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= (int)$emp['id_empleado'] ?>" <?= ((int)$emp['id_empleado'] === (int)$cita['id_empleado']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="acciones-reagendar">
                <button type="submit" class="btn-guardar">Guardar nuevo horario</button>
                <a href="dashboard.php" class="btn-cancelar">Volver</a>
            </div>
        </form>
    </div>
</body>
</html>