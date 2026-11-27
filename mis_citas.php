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

$citas = [];
if ($cliente) {
    $idCliente = $cliente['idcliente'];
    $nombreUsuario = $cliente['nombre'] ?: $nombreUsuario;

    $sql = "SELECT 
                c.id_cita,
                c.fecha_y_hora,
                c.estado AS estado_cita,
                COALESCE(c.total, s.precio, 0) AS total,
                COALESCE(s.nombre, 'Servicio de Spa') AS servicio,
                COALESCE(s.duracion, 90) AS duracion,
                COALESCE(e.nombre, 'Por asignar') AS empleado,
                COALESCE(p.monto, 0) AS monto_pagado,
                COALESCE(p.metodo_pago, 'Transferencia') AS metodo_pago
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
            letter-spacing: 0.4px;
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
            transition: 0.2s ease;
        }
        .btn-dorado {
            background: var(--dorado);
            color: #1f1421;
        }
        .btn-outline {
            border: 1px solid rgba(255,255,255,0.35);
            color: #fff;
        }
        .contenedor {
            max-width: 1050px;
            margin: 32px auto;
            padding: 0 20px 50px;
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
        .hero-resumen h1 {
            margin: 0 0 6px;
            font-size: 1.55rem;
            color: var(--morado);
        }
        .hero-resumen p {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
        }
        .kpis {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }
        .kpi {
            background: var(--crema);
            border: 1px solid #eadcc7;
            border-radius: 12px;
            padding: 12px 18px;
            min-width: 145px;
        }
        .kpi span {
            display: block;
            font-size: 0.78rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi strong {
            font-size: 1.25rem;
            color: var(--morado);
        }
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
        .cita-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }
        .cita-id {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--dorado);
            text-transform: uppercase;
        }
        .cita-card h3 {
            margin: 3px 0 0;
            font-size: 1.15rem;
            color: var(--morado);
        }
        .badge {
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-ok { background: #e6f7ed; color: #1b6b3a; }
        .badge-warn { background: #fff4db; color: #8a5a00; }
        .badge-bad { background: #fde8e8; color: #9b1c1c; }
        .badge-info { background: #f3e8f5; color: var(--morado); }
        .datos-lista {
            font-size: 0.92rem;
            color: #3d3642;
            line-height: 1.65;
        }
        .datos-lista i {
            width: 18px;
            color: var(--morado);
        }
        .pago-box {
            background: #fcfaf7;
            border: 1px dashed #d8c3a5;
            border-radius: 12px;
            padding: 13px 14px;
            font-size: 0.9rem;
        }
        .pago-estado-completo {
            color: #1b6b3a;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .pago-estado-anticipo {
            color: #8a5a00;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }
        .pago-falta {
            margin-top: 4px;
            color: var(--morado);
            font-weight: 600;
        }
        .empty-state {
            background: #fff;
            border-radius: 16px;
            padding: 40px 24px;
            text-align: center;
            box-shadow: 0 8px 22px rgba(58, 30, 58, 0.06);
        }
        .empty-state i {
            font-size: 2.2rem;
            color: var(--dorado);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <i class="fas fa-spa"></i> Consientt Spa
        </div>
        <div class="nav-actions">
            <a href="index.php#citas" class="btn-nav btn-dorado"><i class="fas fa-plus-circle"></i> Agendar Cita</a>
            <a href="index.php" class="btn-nav btn-outline"><i class="fas fa-home"></i> Inicio</a>
            <a href="logout.php" class="btn-nav btn-outline"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <main class="contenedor">
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
            <div class="empty-state">
                <i class="far fa-calendar-times"></i>
                <h3>Aún no tienes citas registradas</h3>
                <p>Agenda tu próximo tratamiento y aquí podrás revisar tus horarios y pagos.</p>
                <a href="index.php#citas" class="btn-nav btn-dorado"><i class="fas fa-calendar-plus"></i> Agendar ahora</a>
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

                    $pagoCompleto = (!$esCancelada) && ($total > 0) && ($restante <= 0.01 || ($pagado >= $total && $total > 0));
                    $dioAnticipo = (!$esCancelada) && (!$pagoCompleto) && ($pagado > 0);

                    $claseBadge = 'badge-info';
                    if ($esCancelada) {
                        $claseBadge = 'badge-bad';
                    } elseif ($esCompletada || strcasecmp(trim($cita['estado_cita']), 'Confirmada') === 0) {
                        $claseBadge = 'badge-ok';
                    } elseif (strcasecmp(trim($cita['estado_cita']), 'Por Validar') === 0) {
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
                                    Falta por pagar: $<?= number_format($restante, 2) ?> (en recepción o transferencia)
                                </div>
                            <?php else: ?>
                                <div class="pago-estado-anticipo">
                                    <i class="fas fa-hourglass-half"></i> Anticipo pendiente (20%: $<?= number_format($anticipoEsperado, 2) ?>)
                                </div>
                                <div class="pago-falta">
                                    Falta por pagar: $<?= number_format($restante > 0 ? $restante : $total, 2) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
