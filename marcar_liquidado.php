<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$db = $conexion ?? ($pdo ?? ($conn ?? null));

if (!$db) {
    echo json_encode(['ok' => false, 'error' => 'No se encontró la variable de conexión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$idPago = filter_input(INPUT_POST, 'id_pago', FILTER_VALIDATE_INT);
$restante = filter_input(INPUT_POST, 'restante', FILTER_VALIDATE_FLOAT);

if (!$idPago) {
    echo json_encode(['ok' => false, 'error' => 'ID de pago inválido']);
    exit;
}

try {
    if ($restante !== false && $restante > 0) {
        $sql = "UPDATE pagos
                SET monto = COALESCE(monto, 0) + ?,
                    metodo_pago = 'Transferencia (Pago Completo)'
                WHERE id_pago = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$restante, $idPago]);
    } else {
        $sql = "UPDATE pagos
                SET metodo_pago = 'Transferencia (Pago Completo)'
                WHERE id_pago = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPago]);
    }

    if ($stmt->rowCount() > 0) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => "No se encontró el pago con id_pago = {$idPago}"]);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
