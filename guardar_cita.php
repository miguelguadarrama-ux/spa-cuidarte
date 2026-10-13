<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $servicio = trim($_POST['servicio'] ?? ''); // Recibimos el nombre del servicio en texto
    $fechaTexto = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $metodoPago = trim($_POST['metodoPago'] ?? '');

    if (empty($nombre) || empty($telefono) || empty($email) || empty($servicio)) {
        echo json_encode(["status" => "error", "message" => "Faltan campos obligatorios."]);
        exit;
    }

    // 1. Verificar si el cliente ya existe por correo
    $stmt = $conexion->prepare("SELECT idCliente FROM cliente WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultadoCliente = $stmt->get_result();

    if ($filaCliente = $resultadoCliente->fetch_assoc()) {
        $id_cliente = $filaCliente['idCliente'];
        $update = $conexion->prepare("UPDATE cliente SET nombre = ?, telefono = ? WHERE idCliente = ?");
        $update->bind_param("ssi", $nombre, $telefono, $id_cliente);
        $update->execute();
    } else {
        $insert = $conexion->prepare("INSERT INTO cliente (nombre, telefono, correo) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $nombre, $telefono, $email);
        $insert->execute();
        $id_cliente = $conexion->insert_id;
    }
    $stmt->close();

    // 2. Obtener el ID y precio del servicio buscando por su nombre
    $stmtServicio = $conexion->prepare("SELECT id_servicio, precio FROM servicios WHERE nombre = ?");
    $stmtServicio->bind_param("s", $servicio);
    $stmtServicio->execute();
    $resServicio = $stmtServicio->get_result();

    if (!$datosServicio = $resServicio->fetch_assoc()) {
        echo json_encode(["status" => "error", "message" => "El servicio seleccionado no existe en la base de datos."]);
        exit;
    }

    $id_servicio = $datosServicio['id_servicio'];
    $total = $datosServicio['precio'];
    $anticipo = $total * 0.20;
    $id_empleado = 1; // Empleado por defecto

    $fechaHoraMysql = date('Y-m-d') . ' ' . ($hora ? $hora : '12:00') . ':00';

    // 3. Insertar la Cita
    $insertCita = $conexion->prepare("INSERT INTO citas (id_cliente, id_empleado, Fecha_y_hora, estado, total) VALUES (?, ?, ?, 'Pendiente', ?)");
    $insertCita->bind_param("iiss", $id_cliente, $id_empleado, $fechaHoraMysql, $total);
    $insertCita->execute();
    $id_cita = $conexion->insert_id;
    $insertCita->close();

    // 4. Insertar detalle de cita
    $insertDetalle = $conexion->prepare("INSERT INTO detalle_citas (id_cita, id_servicio, precio_unitario) VALUES (?, ?, ?)");
    $insertDetalle->bind_param("iid", $id_cita, $id_servicio, $total);
    $insertDetalle->execute();
    $insertDetalle->close();

    // 5. Registrar el pago del anticipo
    $insertPago = $conexion->prepare("INSERT INTO pagos (id_cita, monto, metodo_pago) VALUES (?, ?, ?)");
    $insertPago->bind_param("ids", $id_cita, $anticipo, $metodoPago);
    $insertPago->execute();
    $insertPago->close();

    echo json_encode(["status" => "success", "message" => "¡Cita guardada correctamente en la base de datos!"]);

} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
$conexion->close();
?>