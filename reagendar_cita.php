<?php
session_start();
include 'conexion.php';

date_default_timezone_set('America/Mexico_City');

$id_cita = $_GET['id'] ?? $_POST['id_cita'] ?? null;

if (!$id_cita || !is_numeric($id_cita)) {
    header("Location: dashboard.php");
    exit;
}

$hoy = date('Y-m-d');
$mensaje = "";

// Bloques de 90 minutos de 09:00 a 18:00
$bloques90 = [];
for ($m = 9 * 60; $m <= 18 * 60; $m += 90) {
    $bloques90[] = sprintf('%02d:%02d', floor($m / 60), $m % 60);
}

// Consultar servicios
$servicios = $conexion->query("SELECT id_servicio, nombre, precio, duracion FROM servicios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Consultar especialistas
$empleados = $conexion->query("SELECT id_empleado, nombre FROM empleados ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Consultar cita actual
$sqlCita = "SELECT c.id_cita, c.fecha_y_hora, c.estado, c.id_empleado, c.total,
                   cl.nombre AS cliente_nombre,
                   dc.id_servicio
            FROM citas c
            LEFT JOIN cliente cl ON c.id_cliente = cl.idcliente
            LEFT JOIN detalle_citas dc ON c.id_cita = dc.id_cita
            WHERE c.id_cita = ?
            LIMIT 1";
$stmtCita = $conexion->prepare($sqlCita);
$stmtCita->execute([(int)$id_cita]);
$cita = $stmtCita->fetch(PDO::FETCH_ASSOC);

if (!$cita) {
    header("Location: dashboard.php");
    exit;
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevaFecha = trim($_POST['nueva_fecha'] ?? '');
    $nuevaHora = trim($_POST['nueva_hora'] ?? '');
    $idServicio = (int)($_POST['id_servicio'] ?? 0);
    $nuevoEmpleado = (int)($_POST['id_empleado'] ?? 0);
    $modalidadPago = $_POST['modalidad_pago'] ?? 'anticipo';

    $servicioSeleccionado = null;
    foreach ($servicios as $srv) {
        if ((int)$srv['id_servicio'] === $idServicio) {
            $servicioSeleccionado = $srv;
            break;
        }
    }

    if (empty($nuevaFecha) || empty($nuevaHora)) {
        $mensaje = "Selecciona una nueva fecha y una hora.";
    } elseif ($nuevaFecha < $hoy) {
        $mensaje = "No puedes reagendar a una fecha pasada. Selecciona hoy o una fecha posterior.";
    } elseif (!in_array($nuevaHora, $bloques90, true)) {
        $mensaje = "Selecciona un horario válido en intervalos de 90 minutos (hasta las 18:00).";
    } elseif (!$servicioSeleccionado) {
        $mensaje = "Selecciona un servicio válido.";
    } else {
        $nuevaFechaHora = $nuevaFecha . ' ' . $nuevaHora . ':00';
        $nuevoTotal = (float)$servicioSeleccionado['precio'];
        $montoAbono = ($modalidadPago === 'completo') ? $nuevoTotal : round($nuevoTotal * 0.20, 2);

        $comprobanteBase64 = null;
        if (!empty($_FILES['comprobante']['tmp_name']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $mime = mime_content_type($_FILES['comprobante']['tmp_name']) ?: 'image/jpeg';
            $contenido = file_get_contents($_FILES['comprobante']['tmp_name']);
            if ($contenido !== false) {
                $comprobanteBase64 = 'data:' . $mime . ';base64,' . base64_encode($contenido);
            }
        }

        try {
            $conexion->beginTransaction();

            if ($nuevoEmpleado > 0) {
                $stmtUpd = $conexion->prepare("UPDATE citas SET fecha_y_hora = ?, id_empleado = ?, total = ? WHERE id_cita = ?");
                $stmtUpd->execute([$nuevaFechaHora, $nuevoEmpleado, $nuevoTotal, (int)$id_cita]);
            } else {
                $stmtUpd = $conexion->prepare("UPDATE citas SET fecha_y_hora = ?, total = ? WHERE id_cita = ?");
                $stmtUpd->execute([$nuevaFechaHora, $nuevoTotal, (int)$id_cita]);
            }

            // Actualizar detalle_citas si existe registro
            $stmtChkDet = $conexion->prepare("SELECT COUNT(*) FROM detalle_citas WHERE id_cita = ?");
            $stmtChkDet->execute([(int)$id_cita]);
            if ((int)$stmtChkDet->fetchColumn() > 0) {
                $stmtDet = $conexion->prepare("UPDATE detalle_citas SET id_servicio = ? WHERE id_cita = ?");
                $stmtDet->execute([$idServicio, (int)$id_cita]);
            }

            // Actualizar o insertar pago si existe tabla pagos
            try {
                $stmtChkPago = $conexion->prepare("SELECT COUNT(*) FROM pagos WHERE id_cita = ?");
                $stmtChkPago->execute([(int)$id_cita]);
                if ((int)$stmtChkPago->fetchColumn() > 0) {
                    if ($comprobanteBase64) {
                        $stmtPago = $conexion->prepare("UPDATE pagos SET monto = ?, comprobante_url = ? WHERE id_cita = ?");
                        $stmtPago->execute([$montoAbono, $comprobanteBase64, (int)$id_cita]);
                    } else {
                        $stmtPago = $conexion->prepare("UPDATE pagos SET monto = ? WHERE id_cita = ?");
                        $stmtPago->execute([$montoAbono, (int)$id_cita]);
                    }
                } else {
                    $stmtInsPago = $conexion->prepare("INSERT INTO pagos (id_cita, monto, metodo_pago, comprobante_url) VALUES (?, ?, 'Transferencia', ?)");
                    $stmtInsPago->execute([(int)$id_cita, $montoAbono, $comprobanteBase64]);
                }
            } catch (Exception $ePago) {
                // Evita romper el reagendado si la estructura de pagos difiere
            }

            $conexion->commit();
            header("Location: dashboard.php?reagendada=1");
            exit;
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $mensaje = "No se pudo guardar la reagendación: " . $e->getMessage();
        }
    }
}

$fechaActual = date('Y-m-d', strtotime($cita['fecha_y_hora']));
$fechaInput = ($fechaActual < $hoy) ? $hoy : $fechaActual;
$horaActual = date('H:i', strtotime($cita['fecha_y_hora']));
$servicioActualId = (int)($cita['id_servicio'] ?? ($servicios[0]['id_servicio'] ?? 0));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reagendar Cita #<?= (int)$cita['id_cita'] ?> | Consientt Spa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #faf7f2;
            color: #2b262d;
            padding: 30px 16px;
        }
        .reagendar-box {
            max-width: 640px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 28px rgba(58, 30, 58, 0.09);
            border: 1px solid #efe7dc;
        }
        .reagendar-box h2 {
            margin: 0 0 6px;
            color: #3a1e3a;
        }
        .meta-cita {
            background: #fcfaf7;
            border-left: 4px solid #c9a96e;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.93rem;
        }
        .campo-form {
            margin-bottom: 16px;
        }
        .campo-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #3a1e3a;
        }
        .campo-form input,
        .campo-form select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d5cdd9;
            border-radius: 9px;
            font-size: 0.95rem;
            background: #fff;
        }
        .banco-box {
            background: #fff9ec;
            border: 1px solid #f0dca8;
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0;
            font-size: 0.92rem;
        }
        .banco-box h4 {
            margin: 0 0 6px;
            color: #8a5a00;
        }
        .clabe-card {
            background: #fff;
            border: 1px dashed #d6b87c;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 10px;
            line-height: 1.6;
        }
        .acciones-reagendar {
            display: flex;
            gap: 12px;
            margin-top: 22px;
            flex-wrap: wrap;
        }
        .btn-guardar {
            background: #3a1e3a;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 9px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .btn-cancelar {
            background: #f1eef3;
            color: #333;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 9px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .alerta-error {
            background: #fde8e8;
            color: #9b1c1c;
            padding: 12px 14px;
            border-radius: 9px;
            margin-bottom: 16px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="reagendar-box">
        <h2><i class="far fa-calendar-check"></i> Reagendar Cita #<?= (int)$cita['id_cita'] ?></h2>
        <div class="meta-cita">
            <div><strong>Cliente:</strong> <?= htmlspecialchars($cita['cliente_nombre'] ?? 'Cliente') ?></div>
            <div><strong>Horario actual:</strong> <?= htmlspecialchars($cita['fecha_y_hora']) ?></div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta-error"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_cita" value="<?= (int)$cita['id_cita'] ?>">

            <div class="campo-form">
                <label for="nueva_fecha">Nueva fecha</label>
                <input type="date" id="nueva_fecha" name="nueva_fecha" min="<?= $hoy ?>" value="<?= htmlspecialchars($fechaInput) ?>" required>
            </div>

            <div class="campo-form">
                <label for="nueva_hora">Nueva hora (bloques de 90 min, hasta las 18:00)</label>
                <select id="nueva_hora" name="nueva_hora" required>
                    <?php foreach ($bloques90 as $bloque): ?>
                        <option value="<?= $bloque ?>" <?= ($bloque === $horaActual) ? 'selected' : '' ?>>
                            <?= $bloque ?> hrs
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo-form">
                <label for="id_servicio">Servicio deseado</label>
                <select id="id_servicio" name="id_servicio" required>
                    <?php foreach ($servicios as $srv): ?>
                        <option value="<?= (int)$srv['id_servicio'] ?>" data-precio="<?= (float)$srv['precio'] ?>" <?= ((int)$srv['id_servicio'] === $servicioActualId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($srv['nombre']) ?> — $<?= number_format((float)$srv['precio'], 2) ?> (<?= (int)$srv['duracion'] ?> min)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo-form">
                <label for="id_empleado">Especialista deseado</label>
                <select id="id_empleado" name="id_empleado">
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= (int)$emp['id_empleado'] ?>" <?= ((int)$emp['id_empleado'] === (int)$cita['id_empleado']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo-form">
                <label for="modalidad_pago">¿Cómo deseas pagar?</label>
                <select id="modalidad_pago" name="modalidad_pago">
                    <option value="anticipo">Solo anticipo (20%) — El 80% en recepción</option>
                    <option value="completo">Pago completo (100%)</option>
                </select>
            </div>

            <div class="banco-box">
                <h4><i class="fas fa-university"></i> Datos para Transferencia Bancaria</h4>
                <p id="texto-pago-dinamico" style="margin: 0;">
                    Realiza tu transferencia del <strong>20% de anticipo (<span id="monto-calculado">$0.00</span>)</strong> para apartar tu horario.
                </p>
                <div class="clabe-card">
                    <div><strong>Banco:</strong> BBVA</div>
                    <div><strong>CLABE:</strong> 012345678901234567</div>
                    <div><strong>Titular:</strong> Consientt Spa</div>
                </div>
            </div>

            <div class="campo-form">
                <label for="comprobante">Comprobante de transferencia (opcional al reagendar)</label>
                <input type="file" id="comprobante" name="comprobante" accept="image/*,application/pdf">
            </div>

            <div class="acciones-reagendar">
                <button type="submit" class="btn-guardar"><i class="fas fa-check"></i> Guardar cambios de cita</button>
                <a href="dashboard.php" class="btn-cancelar">Volver</a>
            </div>
        </form>
    </div>

    <script>
        const selectServicio = document.getElementById('id_servicio');
        const selectPago = document.getElementById('modalidad_pago');
        const montoSpan = document.getElementById('monto-calculado');
        const textoPago = document.getElementById('texto-pago-dinamico');

        function actualizarResumenPago() {
            const opt = selectServicio.options[selectServicio.selectedIndex];
            const precio = opt ? parseFloat(opt.getAttribute('data-precio') || '0') : 0;
            const esCompleto = selectPago.value === 'completo';
            const aPagar = esCompleto ? precio : precio * 0.20;

            montoSpan.textContent = '$' + aPagar.toFixed(2);
            if (esCompleto) {
                textoPago.innerHTML = 'Realiza tu transferencia por el <strong>pago completo (<span id="monto-calculado">$' + aPagar.toFixed(2) + '</span>)</strong>.';
            } else {
                textoPago.innerHTML = 'Realiza tu transferencia del <strong>20% de anticipo (<span id="monto-calculado">$' + aPagar.toFixed(2) + '</span>)</strong>. El 80% restante se liquida al asistir.';
            }
        }

        selectServicio.addEventListener('change', actualizarResumenPago);
        selectPago.addEventListener('change', actualizarResumenPago);
        actualizarResumenPago();
    </script>
</body>
</html>
