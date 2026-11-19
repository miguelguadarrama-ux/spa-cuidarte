<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Detecta automáticamente la variable de conexión PDO más común
$db = $conexion ?? ($pdo ?? ($conn ?? null));

if (!$db) {
    echo json_encode(['ok' => false, 'error' => 'No se encontró la variable de conexión en conexion.php']);
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

// Intentamos primero con id_pago y si falla por columna inexistente, probamos con id
$columnasId = ['id_pago', 'id'];
$actualizado = false;
$ultimoError = '';

foreach ($columnasId as $colId) {
    try {
        if ($restante !== false && $restante > 0) {
            $sql = "UPDATE pagos
                    SET monto = COALESCE(monto, 0) + ?,
                        metodo = 'Transferencia (Pago Completo)'
                    WHERE {$colId} = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$restante, $idPago]);
        } else {
            $sql = "UPDATE pagos
                    SET metodo = 'Transferencia (Pago Completo)'
                    WHERE {$colId} = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idPago]);
        }

        if ($stmt->rowCount() > 0) {
            $actualizado = true;
            break;
        } else {
            $ultimoError = "No se encontró ningún registro con {$colId} = {$idPago}";
        }
    } catch (Exception $e) {
        $ultimoError = $e->getMessage();
    }
}

if ($actualizado) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => $ultimoError ?: 'No se pudo actualizar el pago']);
}
