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

// 2. Definir la jornada laboral del spa (Ejemplo: de 09:00 a 20:00)
$horaApertura = strtotime("09:00");
$horaCierre = strtotime("20:00");
$intervaloBusqueda = 30; // Minutos de salto para probar horarios (cada media hora)

// 3. Obtener las citas ya ocupadas de ese empleado en esa fecha
$stmt_citas = $conexion->prepare("SELECT fecha_y_hora FROM citas WHERE id_empleado = ? AND DATE(fecha_y_hora) = ? AND estado != 'Cancelada'");
// Nota: Si ya guardas la hora de fin, puedes comparar rangos. Por simplicidad, aquí validamos bloques exactos.
$stmt_citas->execute([$id_empleado, $fecha]);
$citasOcupadas = $stmt_citas->fetchAll(PDO::FETCH_COLUMN);

// Convertir las citas ocupadas a marcas de tiempo (timestamps) o horas formato 'H:i'
$horasOcupadas = [];
foreach ($citasOcupadas as $c) {
    $horasOcupadas[] = date('H:i', strtotime($c));
}

// 4. Generar los horarios disponibles dinámicamente
$horariosDisponibles = [];
$tiempoActual = $horaApertura;

while ($tiempoActual + ($duracionServicio * 60) <= $horaCierre) {
    $horaStr = date('H:i', $tiempoActual);
    
    // Verificar si este horario está libre (puedes agregar lógica de cruce de rangos aquí)
    if (!in_array($horaStr, $horasOcupadas)) {
        $horariosDisponibles[] = $horaStr;
    }
    
    // Avanzar al siguiente intervalo (ej. 30 minutos)
    $tiempoActual += ($intervaloBusqueda * 60);
}

// Devolver en formato JSON para que JavaScript los pinte en la página
header('Content-Type: application/json');
echo json_encode($horariosDisponibles);