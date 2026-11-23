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
    $modalidadPago = trim($_POST['modalidad_pago'] ?? 'anticipo');
    
    // Recoger el empleado seleccionado (o se asignará por defecto)
    $id_empleado = $_POST['id_empleado'] ?? null;

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

    // 2. Obtener el ID, precio Y LA DURACIÓN del servicio (por ID o por nombre)
        if (is_numeric($servicio)) {
            $stmtServicio = $conexion->prepare("SELECT id_servicio, precio, duracion FROM servicios WHERE id_servicio = ?");
            $stmtServicio->execute([(int)$servicio]);
        } else {
            $stmtServicio = $conexion->prepare("SELECT id_servicio, precio, duracion FROM servicios WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))");
            $stmtServicio->execute([$servicio]);
        }

        $datosServicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);

        if (!$datosServicio) {
            echo json_encode(["status" => "error", "message" => "El servicio seleccionado no existe en la base de datos."]);
            exit;
        }

        $id_servicio = $datosServicio['id_servicio'];
        $total = $datosServicio['precio'];
        $duracionMinutos = intval($datosServicio['duracion'] ?? 60);

        if ($modalidadPago === 'completo') {
            $montoPago = $total;
            $metodoTexto = 'Transferencia (Pago Completo)';
        } else {
            $montoPago = $total * 0.20; // Anticipo del 20%
            $metodoTexto = 'Transferencia (Anticipo 20%)';
        }
        // 3. Asignar empleado válido si no se envió uno
        if (!$id_empleado) {
            $stmtEmp = $conexion->query("SELECT id_empleado FROM empleados ORDER BY id_empleado ASC LIMIT 1");
            $empValido = $stmtEmp->fetch();
            $id_empleado = $empValido['id_empleado'] ?? 5; 
        }

        // 4. Blindaje total de Fecha y Hora de Inicio
        $horaLimpia = (strlen($hora) >= 5) ? substr($hora, 0, 5) . ':00' : '12:00:00';
        
        if (empty($fechaTexto) || $fechaTexto === 'null' || $fechaTexto === 'undefined' || preg_match('/[a-zA-Z]/', $fechaTexto)) {
            $fechaHoraInicio = date('Y-m-d') . ' ' . $horaLimpia;
        } else {
            $fechaHoraInicio = $fechaTexto . ' ' . $horaLimpia;
        }

        // Calcular la hora y fecha de fin exacta sumando los minutos del servicio
        $timestampInicio = strtotime($fechaHoraInicio);
        $timestampFin = $timestampInicio + ($duracionMinutos * 60);
        $fechaHoraFin = date('Y-m-d H:i:s', $timestampFin);

        // 5. VALIDACIÓN INTELIGENTE DE CRUCE DE HORARIOS (RANGOS)
        // Evita que un nuevo servicio se encime con una cita activa del mismo especialista
        $sqlCruces = "SELECT c.id_cita 
                      FROM citas c 
                      JOIN detalle_citas dc ON c.id_cita = dc.id_cita
                      JOIN servicios s ON dc.id_servicio = s.id_servicio
                      WHERE c.id_empleado = ? 
                      AND c.estado != 'Cancelada' 
                      AND c.estado != 'Vencida'
                      AND (
                          (:inicio_nuevo < (c.fecha_y_hora + (s.duracion || ' minutes')::interval)) 
                          AND 
                          (:fin_nuevo > c.fecha_y_hora)
                      )";
                      
        $stmtCheck = $conexion->prepare($sqlCruces);
        // Pasamos los parámetros de inicio y fin de la nueva cita que se quiere agendar
        $stmtCheck->execute([
            'id_empleado' => $id_empleado,
            'inicio_nuevo' => $fechaHoraInicio,
            'fin_nuevo' => $fechaHoraFin
        ]);
        
        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Lo sentimos, el profesional ya tiene otra cita ocupada en ese intervalo de tiempo. Elige un horario distinto."]);
            exit;
        }

        // 6. PROCESAR Y CONVERTIR EL COMPROBANTE A BASE64 (Data URI)
        $comprobanteBase64 = null;
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['comprobante']['tmp_name'];
            $fileName = $_FILES['comprobante']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileData = file_get_contents($fileTmpPath);
                if ($fileData !== false) {
                    // Detectar tipo MIME real del archivo (ej. image/jpeg, image/png, application/pdf)
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $fileTmpPath);
                    finfo_close($finfo);
                    
                    // Empaquetar como Data URI de Base64 para guardarlo directo en la BD
                    $comprobanteBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
                }
            }
        }

        if (!$comprobanteBase64) {
            echo json_encode(["status" => "error", "message" => "Es obligatorio adjuntar un comprobante de pago válido para registrar la cita."]);
            exit;
        }

        // 7. Insertar Cita con estado 'Por Validar'
        $insertCita = $conexion->prepare("INSERT INTO citas (id_cliente, id_empleado, fecha_y_hora, estado, total) VALUES (?, ?, ?, 'Por Validar', ?) RETURNING id_cita");
        $insertCita->execute([$id_cliente, $id_empleado, $fechaHora, $total]);
        $filaCita = $insertCita->fetch();
        $id_cita = $filaCita['id_cita'];

        // 8. Insertar detalle de cita
        $insertDetalle = $conexion->prepare("INSERT INTO detalle_citas (id_cita, id_servicio, precio_unitario) VALUES (?, ?, ?)");
        $insertDetalle->execute([$id_cita, $id_servicio, $total]);

        // 9. Registrar el pago guardando la cadena Base64 en Neon
        $insertPago = $conexion->prepare("INSERT INTO pagos (id_cita, monto, metodo_pago, comprobante_url) VALUES (?, ?, ?, ?)");
        $insertPago->execute([$id_cita, $montoPago, $metodoTexto, $comprobanteBase64]);

        echo json_encode([
            "status" => "success", 
            "message" => "¡Cita registrada con éxito! Tu espacio está 'Por Validar'."
        ]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}

$conexion = null;
?>
