<?php
session_start();
include 'conexion.php';

date_default_timezone_set('America/Mexico_City');

$correoUsuario = $_SESSION['correo'] ?? $_SESSION['email'] ?? $_SESSION['usuario_correo'] ?? null;
$nombreUsuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Cliente';

if (!$correoUsuario) {
    header("Location: login.php");
    exit;
}

$stmtCliente = $conexion->prepare("SELECT idcliente, nombre FROM cliente WHERE LOWER(TRIM(correo)) = LOWER(TRIM(?)) LIMIT 1");
$stmtCliente->execute([$correoUsuario]);
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

$idCliente = $cliente ? (int)$cliente['idcliente'] : 0;
if ($cliente && !empty($cliente['nombre'])) {
    $nombreUsuario = $cliente['nombre'];
}

$mensajeExito = "";
$mensajeError = "";

// Procesar subida de comprobante desde Mis Citas
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_cita_comprobante']) && $idCliente > 0) {
    $idCitaSubida = (int)$_POST['id_cita_comprobante'];
    $tipoAbono = $_POST['tipo_abono'] ?? 'restante';

    if (empty($_FILES['comprobante']['tmp_name']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
        $mensajeError = "Selecciona un archivo de comprobante válido (JPG, PNG o PDF).";
    } elseif ($_FILES['comprobante']['size'] > 5 * 1024 * 1024) {
        $mensajeError = "El comprobante no debe superar los 5 MB.";
    } else {
        // Verificar que la cita pertenezca al cliente actual
        $stmtVerify = $conexion->prepare("
            SELECT c.id_cita, COALESCE(c.total, s.precio, 0) AS total, COALESCE(p.monto, 0) AS pagado
            FROM citas c
            LEFT JOIN detalle_citas dc ON c.id_cita = dc.id_cita
            LEFT JOIN servicios s ON dc.id_servicio = s.id_servicio
            LEFT JOIN pagos p ON c.id_cita = p.id_cita
            WHERE c.id_cita = ? AND c.id_cliente = ?
            LIMIT 1
        ");
        $stmtVerify->execute([$idCitaSubida, $idCliente]);
        $citaActual = $stmtVerify->fetch(PDO::FETCH_ASSOC);

        if (!$citaActual) {
            $mensajeError = "No se encontró la cita seleccionada.";
        } else {
            $mime = mime_content_type($_FILES['comprobante']['tmp_name']) ?: 'image/jpeg';
            $binario = file_get_contents($_FILES['comprobante']['tmp_name']);
            $comprobanteBase64 = 'data:' . $mime . ';base64,' . base64_encode($binario);

            $totalCita = (float)$citaActual['total'];
            $pagadoPrevio = (float)$citaActual['pagado'];
            $faltante = max(0, $totalCita - $pagadoPrevio);

            $nuevoMontoPagado = ($tipoAbono === 'completo') ? $totalCita : ($pagadoPrevio + $faltante);

            try {
                $conexion->beginTransaction();

                $stmtChkPago = $conexion->prepare("SELECT COUNT(*) FROM pagos WHERE id_cita = ?");
                $stmtChkPago->execute([$idCitaSubida]);

                if ((int)$stmtChkPago->fetchColumn() > 0) {
                    $stmtUpdPago = $conexion->prepare("UPDATE pagos SET monto = ?, comprobante_url = ? WHERE id_cita = ?");
                    $stmtUpdPago->execute([$nuevoMontoPagado, $comprobanteBase64, $idCitaSubida]);
                } else {
                    $stmtInsPago = $conexion->prepare("INSERT INTO pagos (id_cita, monto, metodo_pago, comprobante_url) VALUES (?, ?, 'Transferencia', ?)");
                    $stmtInsPago->execute([$idCitaSubida, $nuevoMontoPagado, $comprobanteBase64]);
                }

                $stmtUpdCita = $conexion->prepare("UPDATE citas SET estado = 'Por Validar' WHERE id_cita = ?");
                $stmtUpdCita->execute([$idCitaSubida]);

                $conexion->commit();
                $mensajeExito = "Comprobante enviado para la Cita #" . $idCitaSubida . ". Quedará en estado Por Validar y será aprobada por administración.";
            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $mensajeError = "No se pudo guardar el comprobante: " . $e->getMessage();
            }
        }
    }
}

$citas = [];
if ($idCliente > 0) {
    $sql = "SELECT 
                c.id_cita,
                c.fecha_y_hora,
                c.estado AS estado_cita,
                COALESCE(c.total, s.precio, 0) AS total,
                COALESCE(s.nombre, 'Servicio de Spa') AS servicio,
                COALESCE(s.duracion, 90) AS duracion,
                COALESCE(e.nombre, 'Por asignar') AS empleado,
                COALESCE(p.monto, 0) AS monto_pagado,
                CASE WHEN p.comprobante_url IS NOT NULL AND p.comprobante_url <> '' THEN 1 ELSE 0 END AS tiene_comprobante
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

$totalCitas = count($citas);
$pendienteGlobal = 0;
foreach ($citas as $item) {
    $t = (float)$item['total'];
    $pag = (float)$item['monto_pagado'];
    if (strcasecmp(trim($item['estado_cita']), 'Cancelada') !== 0) {
        $pendienteGlobal += max(0, $t - $pag);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas | Consientt Spa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --morado: #3a1e3a;
            --morado-oscuro: #241024;
            --dorado: #c9a96e;
            --crema: #faf7f2;
            --texto: #2b262d;
            --muted: #6c6570;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--crema);
            color: var(--texto);
        }
        .topbar {
            background: linear-gradient(135deg, var(--morado-oscuro), var(--morado));
            color: #fff;
            padding: 18px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        .brand {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand i { color: var(--dorado); }
        .nav-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-nav {
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .btn-dorado { background: var(--dorado); color: #1f1421; }
        .btn-outline { border: 1px solid rgba(255,255,255,0.35); color: #fff; }
        .contenedor {
            max-width: 1050px;
            margin: 32px auto;
            padding: 0 20px 50px;
        }
        .alerta-ok {
            background: #e6f7ed;
            color: #165b31;
            border: 1px solid #a8dfc0;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .alerta-err {
            background: #fde8e8;
            color: #9b1c1c;
            border: 1px solid #f5b5b5;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .hero-resumen {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(58, 30, 58, 0.06);
            border: 1px solid #efe7dc;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }
        .hero-resumen h1 { margin: 0 0 6px; font-size: 1.55rem; color: var(--morado); }
        .hero-resumen p { margin: 0; color: var(--muted); font-size: 0.95rem; }
        .kpis { display: flex; gap: 14px; flex-wrap: wrap; }
        .kpi {
            background: var(--crema);
            border: 1px solid #eadcc7;
            border-radius: 12px;
            padding: 12px 18px;
            min-width: 145px;
        }
        .kpi span { display: block; font-size: 0.78rem; color: var(--muted); text-transform: uppercase; }
        .kpi strong { font-size: 1.25rem; color: var(--morado); }
        .citas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 20px;
        }
        .cita-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 8px 22px rgba(58, 30, 58, 0.07);
            border-top: 5px solid var(--morado);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
        }
        .cita-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .cita-id { font-size: 0.8rem; font-weight: 700; color: var(--dorado); text-transform: uppercase; }
        .cita-card h3 { margin: 3px 0 0; font-size: 1.15rem; color: var(--morado); }
        .badge { padding: 5px 11px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; white-space: nowrap; }
        .badge-ok { background: #e6f7ed; color: #1b6b3a; }
        .badge-warn { background: #fff4db; color: #8a5a00; }
        .badge-bad { background: #fde8e8; color: #9b1c1c; }
        .badge-info { background: #f3e8f5; color: var(--morado); }
        .datos-lista { font-size: 0.92rem; color: #3d3642; line-height: 1.65; }
        .datos-lista i { width: 18px; color: var(--morado); }
        .pago-box {
            background: #fcfaf7;
            border: 1px dashed #d8c3a5;
            border-radius: 12px;
            padding: 13px 14px;
            font-size: 0.9rem;
        }
        .pago-estado-completo { color: #1b6b3a; font-weight: 700; display: flex; align-items: center; gap: 6px; }
        .pago-estado-anticipo { color: #8a5a00; font-weight: 700; display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
        .pago-falta { margin-top: 4px; color: var(--morado); font-weight: 600; }
        .form-comprobante {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ece3d5;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-comprobante input[type="file"],
        .form-comprobante select {
            font-size: 0.84rem;
            padding: 6px 8px;
            border-radius: 7px;
            border: 1px solid #d5cdd9;
            background: #fff;
        }
        .btn-subir-comp {
            background: var(--morado);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .nota-validacion {
            font-size: 0.78rem;
            color: #6c6570;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand"><i class="fas fa-spa"></i> Consientt Spa</div>
        <div class="nav-actions">
            <a href="index.php#citas" class="btn-nav btn-dorado"><i class="fas fa-plus-circle"></i> Agendar Cita</a>
            <a href="index.php" class="btn-nav btn-outline"><i class="fas fa-home"></i> Inicio</a>
            <a href="logout.php" class="btn-nav btn-outline"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <main class="contenedor">
        <?php if ($mensajeExito): ?>
            <div class="alerta-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if ($mensajeError): ?>
            <div class="alerta-err"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <section class="hero-resumen">
            <div>
                <h1>Mis Citas — <?= htmlspecialchars($nombreUsuario) ?></h1>
                <p>Consulta tus reservas, especialista asignado y el estatus de tu anticipo o pago.</p>
            </div>
            <div class="kpis">
                <div class="kpi">
                    <span>Citas registradas</span>
                    <strong><?= (int)$totalCitas ?></strong>
                </div>
                <div class="kpi">
                    <span>Saldo por liquidar</span>
                    <strong>$<?= number_format($pendienteGlobal, 2) ?></strong>
                </div>
            </div>
        </section>

        <?php if (empty($citas)): ?>
            <div class="pago-box" style="text-align:center; padding:32px;">
                <h3>Aún no tienes citas registradas</h3>
                <a href="index.php#citas" class="btn-nav btn-dorado" style="margin-top:10px;">Agendar ahora</a>
            </div>
        <?php else: ?>
            <div class="citas-grid">
                <?php foreach ($citas as $cita):
                    $total = (float)$cita['total'];
                    $pagado = (float)$cita['monto_pagado'];
                    $restante = max(0, $total - $pagado);
                    $anticipoEsperado = round($total * 0.20, 2);
                    $esCancelada = strcasecmp(trim($cita['estado_cita']), 'Cancelada') === 0;
                    $esCompletada = strcasecmp(trim($cita['estado_cita']), 'Completada') === 0;
                    $esPorValidar = strcasecmp(trim($cita['estado_cita']), 'Por Validar') === 0;

                    $pagoCompleto = (!$esCancelada) && ($total > 0) && ($restante <= 0.01);
                    $dioAnticipo = (!$esCancelada) && (!$pagoCompleto) && ($pagado > 0);
                    $permiteSubirComprobante = (!$esCancelada) && (!$pagoCompleto);

                    $claseBadge = 'badge-info';
                    if ($esCancelada) {
                        $claseBadge = 'badge-bad';
                    } elseif ($esCompletada || strcasecmp(trim($cita['estado_cita']), 'Confirmada') === 0) {
                        $claseBadge = 'badge-ok';
                    } elseif ($esPorValidar) {
                        $claseBadge = 'badge-warn';
                    }

                    $fechaFormateada = date('d/m/Y · H:i', strtotime($cita['fecha_y_hora']));
                ?>
                    <article class="cita-card">
                        <div>
                            <div class="cita-top">
                                <div>
                                    <span class="cita-id">Cita #<?= (int)$cita['id_cita'] ?></span>
                                    <h3><?= htmlspecialchars($cita['servicio']) ?></h3>
                                </div>
                                <span class="badge <?= $claseBadge ?>"><?= htmlspecialchars($cita['estado_cita']) ?></span>
                            </div>

                            <div class="datos-lista" style="margin-top: 12px;">
                                <div><i class="far fa-calendar-alt"></i> <strong>Fecha y hora:</strong> <?= htmlspecialchars($fechaFormateada) ?></div>
                                <div><i class="far fa-user"></i> <strong>Especialista:</strong> <?= htmlspecialchars($cita['empleado']) ?></div>
                                <div><i class="far fa-clock"></i> <strong>Duración:</strong> <?= (int)$cita['duracion'] ?> min</div>
                                <div><i class="fas fa-tag"></i> <strong>Total del servicio:</strong> $<?= number_format($total, 2) ?></div>
                            </div>
                        </div>

                        <div class="pago-box">
                            <?php if ($esCancelada): ?>
                                <div style="color: #9b1c1c; font-weight: 600;">Cita cancelada — sin saldo pendiente.</div>
                            <?php elseif ($pagoCompleto): ?>
                                <div class="pago-estado-completo">
                                    <i class="fas fa-check-circle"></i> Pago completo ($<?= number_format($total, 2) ?>)
                                </div>
                            <?php elseif ($dioAnticipo): ?>
                                <div class="pago-estado-anticipo">
                                    <i class="fas fa-coins"></i> Anticipo pagado ($<?= number_format($pagado, 2) ?>)
                                </div>
                                <div class="pago-falta">
                                    Falta por pagar: $<?= number_format($restante, 2) ?>
                                </div>
                            <?php else: ?>
                                <div class="pago-estado-anticipo">
                                    <i class="fas fa-hourglass-half"></i> Anticipo pendiente (20%: $<?= number_format($anticipoEsperado, 2) ?>)
                                </div>
                                <div class="pago-falta">
                                    Falta por pagar: $<?= number_format($restante > 0 ? $restante : $total, 2) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($esPorValidar): ?>
                                <div class="nota-validacion" style="margin-top:6px; color:#8a5a00; font-weight:600;">
                                    Comprobante en revisión: será aprobada por administración.
                                </div>
                            <?php endif; ?>

                            <?php if ($permiteSubirComprobante): ?>
                                <form method="POST" enctype="multipart/form-data" class="form-comprobante">
                                    <input type="hidden" name="id_cita_comprobante" value="<?= (int)$cita['id_cita'] ?>">
                                    <select name="tipo_abono">
                                        <option value="restante">Cubrir restante ($<?= number_format($restante > 0 ? $restante : $total, 2) ?>)</option>
                                        <option value="completo">Registrar pago completo ($<?= number_format($total, 2) ?>)</option>
                                    </select>
                                    <input type="file" name="comprobante" accept="image/*,application/pdf" required>
                                    <button type="submit" class="btn-subir-comp">
                                        <i class="fas fa-upload"></i> Subir comprobante
                                    </button>
                                    <span class="nota-validacion">Al enviarlo, tu cita quedará pendiente y será aprobada por administración.</span>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
