<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

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
    // Opción A: si guardas el total acumulado en `monto` y el método/estado en `metodo`
    if ($restante !== false && $restante > 0) {
        $sql = "UPDATE pagos
                SET monto = COALESCE(monto, 0) + :restante,
                    metodo = 'Transferencia (Pago Completo)'
                WHERE id_pago = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':restante' => $restante,
            ':id' => $idPago
        ]);
    } else {
        $sql = "UPDATE pagos
                SET metodo = 'Transferencia (Pago Completo)'
                WHERE id_pago = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $idPago]);
    }

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al actualizar el pago']);
}