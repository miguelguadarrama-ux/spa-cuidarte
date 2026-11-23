<?php
include 'conexion.php';

$id_empleado = $_GET['empleado'] ?? null;
$id_servicio = $_GET['servicio'] ?? null;
$fecha = $_GET['fecha'] ?? null;

if (!$id_empleado || !$id_servicio || !$fecha) {
    echo json_encode([]);
    exit;
}

// 1. Obtener la duración del servicio elegido
$stmt_srv = $conexion->prepare("SELECT duracion FROM servicios WHERE id_servicio = ?");
$stmt_srv->execute([$id_servicio]);
$srv = $stmt_srv->fetch(PDO::FETCH_ASSOC);
$duracionServicio = $srv['duracion'] ?? 60; // En minutos

// 2. Definir la jornada laboral del spa (09:00 a 20:00) con ciclos de 1 hora y media (90 min)
$horaApertura = strtotime("09:00");
$horaCierre = strtotime("20:00");
$intervaloBusqueda = 90; // 1 hora y media en minutos

// 3. Obtener las citas ya ocupadas de ese empleado en esa fecha
$stmt_citas = $conexion->prepare("SELECT fecha_y_hora FROM citas WHERE id_empleado = ? AND DATE(fecha_y_hora) = ? AND estado != 'Cancelada'");
$stmt_citas->execute([$id_empleado, $fecha]);
$citasOcupadas = $stmt_citas->fetchAll(PDO::FETCH_COLUMN);

$horasOcupadas = [];
foreach ($citasOcupadas as $c) {
    $horasOcupadas[] = date('H:i', strtotime($c));
}

// 4. Generar los horarios disponibles cada 90 minutos
$horariosDisponibles = [];
$tiempoActual = $horaApertura;

while ($tiempoActual + ($duracionServicio * 60) <= $horaCierre) {
    $horaStr = date('H:i', $tiempoActual);

    if (!in_array($horaStr, $horasOcupadas)) {
        $horariosDisponibles[] = $horaStr;
    }

    // Avanzar 1 hora y media (90 minutos)
    $tiempoActual += ($intervaloBusqueda * 60);
}

header('Content-Type: application/json');
echo json_encode($horariosDisponibles);
