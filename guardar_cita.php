<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $servicio = trim($_POST['servicio'] ?? ''); 
    $fechaTexto = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $metodoPago = trim($_POST['metodoPago'] ?? '');

    if (empty($nombre) || empty($telefono) || empty($email) || empty($servicio)) {
        echo json_encode(["status" => "error", "message" => "Faltan campos obligatorios."]);
        exit;
    }

    try {
        // 1. Verificar si el cliente ya existe por correo
        $stmt = $conexion->prepare("SELECT idcliente FROM cliente WHERE correo = ?");
        $stmt->execute([$email]);
        $filaCliente = $stmt->fetch();

        if ($filaCliente) {
            $id_cliente = $filaCliente['idcliente'];
            $update = $conexion->prepare("UPDATE cliente SET nombre = ?, telefono = ? WHERE idcliente = ?");
            $update->execute([$nombre, $telefono, $id_cliente]);
        } else {
            $insert = $conexion->prepare("INSERT INTO cliente (nombre, telefono, correo) VALUES (?, ?, ?) RETURNING idcliente");
            $insert->execute([$nombre, $telefono, $email]);
            $filaNuevo = $insert->fetch();
            $id_cliente = $filaNuevo['idcliente'];
        }

        // 2. Obtener el ID y precio del servicio
        $stmtServicio = $conexion->prepare("SELECT id_servicio, precio FROM servicios WHERE nombre = ?");
        $stmtServicio->execute([$servicio]);
        $datosServicio = $stmtServicio->fetch();

        if (!$datosServicio) {
            echo json_encode(["status" => "error", "message" => "El servicio seleccionado no existe en la base de datos."]);
            exit;
        }

        $id_servicio = $datosServicio['id_servicio'];
        $total = $datosServicio['precio'];
        $anticipo = $total * 0.20; 

        // 3. Asignar empleado válido (recibido por POST o buscando el primer empleado disponible en la BD)
        $id_empleado = $_POST['id_empleado'] ?? null;
        if (!$id_empleado) {
            $stmtEmp = $conexion->query("SELECT id_empleado FROM empleados ORDER BY id_empleado ASC LIMIT 1");
            $empValido = $stmtEmp->fetch();
            $id_empleado = $empValido['id_empleado'] ?? 5; // Si no encuentra nada, usa 5 de respaldo
        }

        // Formatear la fecha para PostgreSQL
        $fechaHora = ($fechaTexto ? $fechaTexto : date('Y-m-d')) . ' ' . ($hora ? $hora : '12:00') . ':00';

        // 4. Insertar Cita (usando columna fecha_y_horario)
        $insertCita = $conexion->prepare("INSERT INTO citas (id_cliente, id_empleado, fecha_y_horara, estado, total) VALUES (?, ?, ?, 'Pendiente', ?) RETURNING id_cita");
        $insertCita->execute([$id_cliente, $id_empleado, $fechaHora, $total]);
        $filaCita = $insertCita->fetch();
        $id_cita = $filaCita['id_cita'];

        // 5. Insertar detalle de cita
        $insertDetalle = $conexion->prepare("INSERT INTO detalle_citas (id_cita, id_servicio, precio_unitario) VALUES (?, ?, ?)");
        $insertDetalle->execute([$id_cita, $id_servicio, $total]);

        // 6. Registrar el pago del anticipo
        $insertPago = $conexion->prepare("INSERT INTO pagos (id_cita, monto, metodo_pago) VALUES (?, ?, ?)");
        $insertPago->execute([$id_cita, $anticipo, $metodoPago]);

        echo json_encode(["status" => "success", "message" => "¡Cita guardada correctamente en la base de datos en la nube!"]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}

$conexion = null;
?>
