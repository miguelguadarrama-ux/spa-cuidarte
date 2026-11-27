<?php
session_start();
include 'conexion.php';

// Ajusta el nombre de la variable de sesión según cómo guardes el usuario en procesar_login.php
$correoUsuario = $_SESSION['correo'] ?? $_SESSION['email'] ?? $_SESSION['usuario_correo'] ?? null;
$nombreUsuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Cliente';

if (!$correoUsuario) {
    header("Location: login.php");
    exit;
}

// 1. Buscar el idcliente en la tabla cliente
$stmtCliente = $conexion->prepare("SELECT idcliente, nombre FROM cliente WHERE LOWER(TRIM(correo)) = LOWER(TRIM(?)) LIMIT 1");
$stmtCliente->execute([$correoUsuario]);
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

$citas = [];
if ($cliente) {
    $idCliente = $cliente['idcliente'];
    $nombreUsuario = $cliente['nombre'] ?: $nombreUsuario;

    // 2. Consultar todas las citas del cliente
    $sql = "SELECT 
                c.id_cita,
                c.fecha_y_hora,
                c.estado AS estado_cita,
                c.total,
                COALESCE(s.nombre, 'Servicio') AS servicio,
                COALESCE(s.duracion, 60) AS duracion,
                COALESCE(e.nombre, 'Por asignar') AS empleado,
                COALESCE(p.monto, 0) AS monto_pagado,
                COALESCE(p.metodo_pago, 'Pendiente') AS metodo_pago
            FROM citas c
            LEFT JOIN detalle_citas dc ON c.id_cita = dc.id_cita
            LEFT JOIN servicios s ON dc.id_servicio = s.id_servicio
            LEFT JOIN empleados e ON c.id_empleado = e.id_empleado
            LEFT JOIN pagos p ON c.id_cita = p.id_cita
            WHERE c.id_cliente = ?
            ORDER BY c.fecha_y_hora DESC";

    $stmtCitas = $conexion->prepare($sql);
    $stmtCitas->execute([$idCliente]);
    $citas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas | Consientt Spa</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .mis-citas-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .mis-citas-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .citas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 18px;
        }
        .cita-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border-left: 5px solid #3a1e3a;
        }
        .cita-card h3 {
            margin: 0 0 8px;
            color: #3a1e3a;
        }
        .cita-dato {
            margin: 6px 0;
            font-size: 0.95rem;
            color: #333;
        }
        .badge-estado {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            background: #f3e8f5;
            color: #3a1e3a;
            margin-top: 8px;
        }
        .btn-volver {
            display: inline-block;
            background: #3a1e3a;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="mis-citas-container">
        <div class="mis-citas-header">
            <div>
                <h2>Mis Citas — <?= htmlspecialchars($nombreUsuario) ?></h2>
                <p>Aquí puedes consultar todas tus citas registradas y el estado de tu pago.</p>
            </div>
            <a href="index.php#citas" class="btn-volver">+ Agendar nueva cita</a>
        </div>

        <?php if (empty($citas)): ?>
            <div class="cita-card">
                <h3>Aún no tienes citas registradas</h3>
                <p class="cita-dato">Cuando agendes un servicio con tu correo, aparecerá aquí automáticamente.</p>
            </div>
        <?php else: ?>
            <div class="citas-grid">
                <?php foreach ($citas as $cita):
                    $restante = max(0, (float)$cita['total'] - (float)$cita['monto_pagado']);
                    $fechaFormateada = date('d/m/Y H:i', strtotime($cita['fecha_y_hora']));
                ?>
                    <div class="cita-card">
                        <h3><?= htmlspecialchars($cita['servicio']) ?></h3>
                        <p class="cita-dato"><strong>Fecha y hora:</strong> <?= htmlspecialchars($fechaFormateada) ?></p>
                        <p class="cita-dato"><strong>Especialista:</strong> <?= htmlspecialchars($cita['empleado']) ?></p>
                        <p class="cita-dato"><strong>Duración:</strong> <?= (int)$cita['duracion'] ?> min</p>
                        <p class="cita-dato"><strong>Total:</strong> $<?= number_format((float)$cita['total'], 2) ?></p>
                        <p class="cita-dato">
                            <strong>Saldo pendiente:</strong>
                            <?= $restante <= 0 ? '$0.00 (Liquidado)' : '$' . number_format($restante, 2) ?>
                        </p>
                        <span class="badge-estado">Estado: <?= htmlspecialchars($cita['estado_cita']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>