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

    try {
        // 1. Verificar si el cliente ya existe por correo
        $stmt = $conexion->prepare("SELECT idCliente FROM cliente WHERE correo = ?");
        $stmt->execute([$email]);
        $filaCliente = $stmt->fetch();

        if ($filaCliente) {
            // Nota: En PostgreSQL las columnas sin comillas se guardan en minúsculas (idcliente)
            $id_cliente = $filaCliente['idCliente'] ?? $filaCliente['idcliente'];
            $update = $conexion->prepare("UPDATE cliente SET nombre = ?, telefono = ? WHERE idCliente = ?");
            $update->execute([$nombre, $telefono, $id_cliente]);
        } else {
            // En PostgreSQL usamos RETURNING para obtener el ID insertado al instante
            $insert = $conexion->prepare("INSERT INTO cliente (nombre, telefono, correo) VALUES (?, ?, ?) RETURNING idCliente");
            $insert->execute([$nombre, $telefono, $email]);
            $filaNuevo = $insert->fetch();
            $id_cliente = $filaNuevo['idCliente'] ?? $filaNuevo['idcliente'];
        }

        // 2. Obtener el ID y precio del servicio buscando por su nombre
        $stmtServicio = $conexion->prepare("SELECT id_servicio, precio FROM servicios WHERE nombre = ?");
        $stmtServicio->execute([$servicio]);
        $datosServicio = $stmtServicio->fetch();

        if (!$datosServicio) {
            echo json_encode(["status" => "error", "message" => "El servicio seleccionado no existe en la base de datos."]);
            exit;
        }

        $id_servicio = $datosServicio['id_servicio'];
        $total = $datosServicio['precio'];
        
        // CORREGIDO: Operador de multiplicación (*) en una sola línea
        $anticipo = $total * 0.20; 
        
        $id_empleado = 1; // Empleado por defecto

        // Usamos la fecha actual o la seleccionada formateada para Postgres (TIMESTAMP)
        $fechaHoraMysql = date('Y-m-d') . ' ' . ($hora ? $hora : '12:00') . ':00';

        // 3. Insertar la Cita (con RETURNING para obtener el id_cita)
        $insertCita = $conexion->prepare("INSERT INTO citas (id_cliente, id_empleado, Fecha_y_hora, estado, total) VALUES (?, ?, ?, 'Pendiente', ?) RETURNING id_cita");
        $insertCita->execute([$id_cliente, $id_empleado, $fechaHoraMysql, $total]);
        $filaCita = $insertCita->fetch();
        $id_cita = $filaCita['id_cita'];

        // 4. Insertar detalle de cita
        $insertDetalle = $conexion->prepare("INSERT INTO detalle_citas (id_cita, id_servicio, precio_unitario) VALUES (?, ?, ?)");
        $insertDetalle->execute([$id_cita, $id_servicio, $total]);

        // 5. Registrar el pago del anticipo
        $insertPago = $conexion->prepare("INSERT INTO pagos (id_cita, monto, metodo_pago) VALUES (?, ?, ?)");
        $insertPago->execute([$id_cita, $anticipo, $metodoPago]);

        echo json_encode(["status" => "success", "message" => "¡Cita guardada correctamente en la base de datos en la nube!"]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}

$conexion = null; // Cierra la conexión PDO
?>
