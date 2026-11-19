<?php
session_start();
// Seguridad: Si no ha iniciado sesión o no es admin/empleado, al login
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}


include 'conexion.php';

// Variables de sesión para los filtros
$empleado_id = $_SESSION['id'] ?? null; 
$rol = $_SESSION['rol'] ?? '';
$correo = $_SESSION['correo'] ?? '';

// Definimos si tiene acceso total (tú o superadmin)
$es_acceso_total = ($rol === 'superadmin' || $rol === 'admin' || $correo === 'miguel@consientt.com');

// 1. OBTENER CITAS UNIENDO LA TABLA PAGOS
if ($es_acceso_total) {
    $sql_citas = "SELECT c.id_cita AS id_cita, cl.nombre AS cliente_nombre, e.nombre AS empleado_nombre, c.fecha_y_hora, c.estado, c.total, p.comprobante_url 
                  FROM citas c
                  JOIN cliente cl ON c.id_cliente = cl.idcliente
                  JOIN empleados e ON c.id_empleado = e.id_empleado
                  LEFT JOIN pagos p ON c.id_cita = p.id_cita
                  ORDER BY c.fecha_y_hora DESC";
    $stmt_citas = $conexion->prepare($sql_citas);
    $stmt_citas->execute();
} else {
    $sql_citas = "SELECT c.id_cita AS id_cita, cl.nombre AS cliente_nombre, e.nombre AS empleado_nombre, c.fecha_y_hora, c.estado, c.total, p.comprobante_url 
                  FROM citas c
                  JOIN cliente cl ON c.id_cliente = cl.idcliente
                  JOIN empleados e ON c.id_empleado = e.id_empleado
                  LEFT JOIN pagos p ON c.id_cita = p.id_cita
                  WHERE c.id_empleado = ?
                  ORDER BY c.fecha_y_hora DESC";
    $stmt_citas = $conexion->prepare($sql_citas);
    $stmt_citas->execute([$empleado_id]);
}
$citas = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);;

// Lógica para calcular estado dinámico (En proceso o Vencida)
$ahoraTime = time();
$duracionEstimada = 3600; // 1 hora de duración por cita en segundos

foreach ($citas as &$cita) {
    $estadoActual = trim($cita['estado']);
    $fechaCitaTime = strtotime($cita['fecha_y_hora']);

    // Solo evaluamos si está pendiente
    if (strcasecmp($estadoActual, 'Pendiente') === 0) {
        if ($ahoraTime >= $fechaCitaTime && $ahoraTime <= ($fechaCitaTime + $duracionEstimada)) {
            $cita['estado_visual'] = 'En proceso';
            $cita['clase_estado'] = 'en-proceso';
        } elseif ($ahoraTime > ($fechaCitaTime + $duracionEstimada)) {
            $cita['estado_visual'] = 'Vencida';
            $cita['clase_estado'] = 'vencida';
        } else {
            $cita['estado_visual'] = $estadoActual;
            $cita['clase_estado'] = strtolower($estadoActual);
        }
    } else {
        $cita['estado_visual'] = $estadoActual;
        $cita['clase_estado'] = strtolower($estadoActual);
    }
}
unset($cita);

// 2. OBTENER CLIENTES
$stmt_clientes = $conexion->query("SELECT * FROM cliente ORDER BY idcliente DESC");
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// 3. OBTENER EMPLEADOS
$stmt_empleados = $conexion->query("SELECT * FROM empleados ORDER BY id_empleado DESC");
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

// 4. OBTENER SERVICIOS
$stmt_servicios = $conexion->query("SELECT * FROM servicios ORDER BY id_servicio DESC");
$servicios = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);

// 5. OBTENER PAGOS
$stmt_pagos = $conexion->query("SELECT * FROM pagos ORDER BY id_pago DESC");
$pagos = $stmt_pagos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrativo | Consientt Spa</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<!-- Modal Agregar Servicio -->
<div id="modalServicio" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px; font-family: 'Plus Jakarta Sans', sans-serif; color: #271a2e;">Agregar Nuevo Servicio</h3>
        <form action="agregar_servicio.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Nombre del Servicio</label>
                <input type="text" name="nombre" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <!-- 📌 NUEVO CAMPO DE DURACIÓN -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Duración (Minutos)</label>
                <input type="number" name="duracion" required placeholder="Ej. 60" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Precio Base ($)</label>
                <input type="number" step="0.01" name="precio" required placeholder="Ej. 750.00" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: #3a1e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; flex: 1;">Guardar Servicio</button>
                <button type="button" onclick="cerrarModalServicio()" style="background: #e0e0e0; color: #333; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Registrar Empleado -->
<div id="modalEmpleado" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px; font-family: 'Plus Jakarta Sans', sans-serif; color: #271a2e;">Registrar Nuevo Empleado</h3>
        <form action="agregar_empleado.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Nombre Completo</label>
                <input type="text" name="nombre" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Correo Electrónico</label>
                <input type="email" name="correo" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Contraseña Temporal</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
          <!-- Rol fijo como Admin -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Rol en el sistema</label>
                <input type="text" value="Admin" disabled style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; background: #f5f5f5; color: #555;">
                <input type="hidden" name="rol" value="admin">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: #3a1e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; flex: 1;">Registrar</button>
                <button type="button" onclick="cerrarModalEmpleado()" style="background: #e0e0e0; color: #333; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
        </form>
    </div>
</div>
    <!-- Modal Añadir Cliente -->
<div id="modalCliente" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 30px; border-radius: 16px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px; font-family: 'Plus Jakarta Sans', sans-serif; color: #271a2e;">Añadir Nuevo Cliente</h3>
        <form action="agregar_cliente.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Nombre Completo</label>
                <input type="text" name="nombre" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Teléfono</label>
                <input type="text" name="telefono" placeholder="Ej. 3322530111" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Correo Electrónico</label>
                <input type="email" name="correo" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: #3a1e3a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; flex: 1;">Guardar Cliente</button>
                <button type="button" onclick="cerrarModalCliente()" style="background: #e0e0e0; color: #333; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

  <div class="dashboard-container">
    
    <!-- Barra Lateral de Navegación -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <i class="fas fa-spa brand-icon"></i>
        <h2>Consientt Spa</h2>
      </div>
      
      <nav class="sidebar-menu">
        <button class="menu-item active" data-target="tab-citas">
          <i class="fas fa-calendar-check"></i> Citas
        </button>
        <button class="menu-item" data-target="tab-clientes">
          <i class="fas fa-users"></i> Clientes
        </button>
        <button class="menu-item" data-target="tab-empleados">
          <i class="fas fa-user-tie"></i> Empleados
        </button>
        <button class="menu-item" data-target="tab-servicios">
          <i class="fas fa-hand-holding-heart"></i> Servicios
        </button>
        <button class="menu-item" data-target="tab-pagos">
          <i class="fas fa-credit-card"></i> Pagos
        </button>
      </nav>

      <div class="sidebar-footer">
        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
      </div>
    </aside>

    <!-- Área Principal de Contenido -->
    <main class="main-content">
      
      <header class="content-header">
        <div class="header-title">
          <span class="header-subtitle">Gestión Interna</span>
          <h1 id="current-tab-title">Control de Citas</h1>
        </div>
        <div class="admin-profile">
          <div class="admin-info">
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?></span>
            <span class="admin-role"><?php echo $es_acceso_total ? 'Acceso Total' : 'Empleado'; ?></span>
          </div>
          <div class="admin-avatar"><?php echo strtoupper(substr($_SESSION['nombre'] ?? 'A', 0, 1)); ?></div>
        </div>
      </header>

      <div class="content-body">
        
      <!-- PESTAÑA: CITAS -->
        <section id="tab-citas" class="dashboard-tab active">
          <div class="action-bar">
            <h3>Registros de <code>citas</code></h3>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Cita</th>
                  <th>Cliente</th>
                  <th>Atendió (Empleado)</th>
                  <th>Fecha y Hora</th>
                  <th>Estado</th>
                  <th>Total</th>
                  <th>Acciones</th> <!-- 📌 NUEVA COLUMNA -->
                </tr>
              </thead>
              <tbody>
                <?php if (empty($citas)): ?>
                  <tr><td colspan="7" style="text-align: center;">No hay citas registradas.</td></tr>
                <?php else: ?>
                  <?php foreach ($citas as $cita): ?>
                    <tr>
                      <td><strong>#<?php echo $cita['id_cita']; ?></strong></td>
                      <td><?php echo htmlspecialchars($cita['cliente_nombre']); ?></td>
                      <td><?php echo htmlspecialchars($cita['empleado_nombre']); ?></td>
                      <td><?php echo htmlspecialchars($cita['fecha_y_hora']); ?></td>
                      <td><span class="status-badge <?php echo htmlspecialchars($cita['clase_estado']); ?>"><?php echo htmlspecialchars($cita['estado_visual']); ?></span></td>
                      <td><strong>$<?php echo number_format($cita['total'], 2); ?></strong></td>
                      
                     <!-- 📌 BOTONES DE ACCIÓN POR CITA -->
                      <td style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                        
                        <!-- 1. Ver Comprobante Base64 -->
                        <?php if (!empty($cita['comprobante_url'])): ?>
                            <button type="button" onclick="verComprobante('<?php echo $cita['comprobante_url']; ?>')" class="btn-accion" title="Ver Comprobante de Pago" style="background: #C9A96E; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer;">
                                <i class="fas fa-file-image"></i>
                            </button>
                        <?php else: ?>
                            <span style="font-size: 0.75rem; color: #999;">Sin comprobante</span>
                        <?php endif; ?>

                        <!-- 2. Aprobar si está Por Validar -->
                        <?php if (strcasecmp(trim($cita['estado']), 'Por Validar') === 0): ?>
                            <a href="cambiar_estado.php?id=<?php echo $cita['id_cita']; ?>&estado=Confirmada" title="Aprobar Cita" style="background: #28a745; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                                <i class="fas fa-check"></i>
                            </a>
                        <?php endif; ?>

                        <!-- 3. Si la cita ya está Confirmada o Pendiente: Opciones de control de asistencia -->
                        <?php if (strcasecmp(trim($cita['estado']), 'Confirmada') === 0 || strcasecmp(trim($cita['estado']), 'Pendiente') === 0): ?>
                            <!-- Marcar como Atendida -->
                            <a href="cambiar_estado.php?id=<?php echo $cita['id_cita']; ?>&estado=Completada" title="Marcar como Atendida" style="background: #17a2b8; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                                <i class="fas fa-user-check"></i> Atendida
                            </a>

                            <!-- Marcar como No Asistió -->
                            <a href="cambiar_estado.php?id=<?php echo $cita['id_cita']; ?>&estado=No Asistió" title="Cliente no se presentó" onclick="return confirm('¿Marcar esta cita como que el cliente no se presentó?');" style="background: #6c757d; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                                <i class="fas fa-user-times"></i>
                            </a>
                        <?php endif; ?>

                        <!-- 4. Cancelar (Único y limpio: disponible si no está completada o cancelada previamente) -->
                        <?php if (strcasecmp(trim($cita['estado']), 'Completada') !== 0 && strcasecmp(trim($cita['estado']), 'Cancelada') !== 0): ?>
                            <a href="cambiar_estado.php?id=<?php echo $cita['id_cita']; ?>&estado=Cancelada" title="Cancelar Cita" onclick="return confirm('¿Seguro que deseas cancelar esta cita?');" style="background: #dc3545; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>

                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

       <!-- PESTAÑA: CLIENTES -->
        <section id="tab-clientes" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>clientes</code></h3>
            <button class="btn-action" onclick="abrirModalCliente()"><i class="fas fa-plus"></i> Añadir Cliente</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Cliente</th>
                  <th>Nombre Completo</th>
                  <th>Teléfono</th>
                  <th>Correo Electrónico</th>
                  <th>Acciones</th> <!-- 📌 NUEVA COLUMNA -->
                </tr>
              </thead>
              <tbody>
                <?php if (empty($clientes)): ?>
                  <tr><td colspan="5" style="text-align: center;">No hay clientes registrados.</td></tr>
                <?php else: ?>
                  <?php foreach ($clientes as $cli): ?>
                    <tr>
                      <td><strong>#<?php echo $cli['idcliente']; ?></strong></td>
                      <td><?php echo htmlspecialchars($cli['nombre']); ?></td>
                      <td><?php echo htmlspecialchars($cli['telefono'] ?? 'N/D'); ?></td>
                      <td><?php echo htmlspecialchars($cli['correo']); ?></td>
                      
                      <!-- 📌 BOTONES DE ACCIÓN PARA CLIENTES -->
                      <td style="display: flex; gap: 6px; align-items: center;">
                        
                        <!-- Botón para Editar (puedes enlazarlo a un script de edición) -->
                        <a href="editar_cliente.php?id=<?php echo $cli['idcliente']; ?>" title="Editar Cliente" style="background: #C9A96E; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Botón para Eliminar / Dar de baja -->
                        <a href="eliminar_cliente.php?id=<?php echo $cli['idcliente']; ?>" title="Dar de baja" onclick="return confirm('¿Estás seguro de eliminar a este cliente? Se borrarán sus datos del sistema.');" style="background: #dc3545; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                            <i class="fas fa-trash-alt"></i>
                        </a>

                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

     <!-- PESTAÑA: EMPLEADOS -->
        <section id="tab-empleados" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>empleados</code></h3>
            <button class="btn-action" onclick="abrirModalEmpleado()"><i class="fas fa-plus"></i> Registrar Empleado</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Empleado</th>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Rol</th>
                  <th>Acciones</th> <!-- 📌 NUEVA COLUMNA -->
                </tr>
              </thead>
              <tbody>
                <?php if (empty($empleados)): ?>
                  <tr><td colspan="5" style="text-align: center;">No hay empleados registrados.</td></tr>
                <?php else: ?>
                  <?php foreach ($empleados as $emp): ?>
                    <tr>
                      <td><strong>#<?php echo $emp['id_empleado']; ?></strong></td>
                      <td><?php echo htmlspecialchars($emp['nombre']); ?></td>
                      <td><?php echo htmlspecialchars($emp['correo']); ?></td>
                      <td><?php echo htmlspecialchars($emp['rol']); ?></td>
                      
                      <!-- 📌 BOTONES DE ACCIÓN PARA EMPLEADOS -->
                      <td style="display: flex; gap: 6px; align-items: center;">
                        
                        <!-- Botón Editar -->
                        <a href="editar_empleado.php?id=<?php echo $emp['id_empleado']; ?>" title="Editar Empleado" style="background: #C9A96E; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Botón Eliminar / Dar de baja -->
                        <a href="eliminar_empleado.php?id=<?php echo $emp['id_empleado']; ?>" title="Dar de baja" onclick="return confirm('¿Estás seguro de dar de baja a este empleado?');" style="background: #dc3545; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                            <i class="fas fa-trash-alt"></i>
                        </a>

                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

       <!-- PESTAÑA: SERVICIOS -->
        <section id="tab-servicios" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>servicios</code></h3>
            <button class="btn-action" onclick="abrirModalServicio()"><i class="fas fa-plus"></i> Agregar Servicio</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Servicio</th>
                  <th>Nombre del Servicio</th>
                  <th>Precio Base</th>
                  <th>Acciones</th> <!-- 📌 NUEVA COLUMNA -->
                </tr>
              </thead>
              <tbody>
                <?php if (empty($servicios)): ?>
                  <tr><td colspan="4" style="text-align: center;">No hay servicios registrados.</td></tr>
                <?php else: ?>
                  <?php foreach ($servicios as $ser): ?>
                    <tr>
                      <td><strong>#<?php echo $ser['id_servicio']; ?></strong></td>
                      <td><?php echo htmlspecialchars($ser['nombre']); ?></td>
                      <td><strong>$<?php echo number_format($ser['precio'], 2); ?></strong></td>
                      
                      <!-- 📌 BOTONES DE ACCIÓN PARA SERVICIOS -->
                      <td style="display: flex; gap: 6px; align-items: center;">
                        
                        <!-- Botón Editar -->
                        <a href="editar_servicio.php?id=<?php echo $ser['id_servicio']; ?>" title="Editar Servicio" style="background: #C9A96E; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Botón Eliminar -->
                        <a href="eliminar_servicio.php?id=<?php echo $ser['id_servicio']; ?>" title="Eliminar Servicio" onclick="return confirm('¿Estás seguro de eliminar este servicio?');" style="background: #dc3545; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                            <i class="fas fa-trash-alt"></i>
                        </a>

                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

       <!-- PESTAÑA: PAGOS -->
        <section id="tab-pagos" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>pagos</code></h3>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Pago</th>
                  <th>ID Cita</th>
                  <th>Pagado</th>
                  <th>Método</th>
                  <th>Falta por pagar</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pagos) || !is_array($pagos)): ?>
                  <tr>
                    <td colspan="5" style="text-align: center; color: #777; padding: 20px;">
                      No hay registros de pagos disponibles.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($pagos as $pag):
                    $idPago   = $pag['id_pago'] ?? ($pag['id'] ?? ($pag['pago_id'] ?? ''));
                    $idCita   = $pag['id_cita'] ?? ($pag['cita_id'] ?? '');
                    $montoRaw = $pag['monto'] ?? ($pag['anticipo'] ?? 0);
                    $metodo   = (string)($pag['metodo'] ?? ($pag['modalidad_pago'] ?? ($pag['metodo_pago'] ?? 'Transferencia')));

                    $montoPagado = is_numeric($montoRaw) ? (float)$montoRaw : 0.0;

                    // Intentamos obtener el total del servicio/cita si viene en la consulta
                    $totalServicioRaw = $pag['precio_servicio'] ?? ($pag['total_servicio'] ?? ($pag['precio'] ?? ($pag['total_cita'] ?? null)));

                    $esPagoCompleto = (stripos($metodo, 'completo') !== false) || (stripos($metodo, '100%') !== false);

                    if ($esPagoCompleto) {
                      $faltaPorPagar = 0.0;
                    } elseif (is_numeric($totalServicioRaw) && (float)$totalServicioRaw > 0) {
                      $faltaPorPagar = max(0.0, (float)$totalServicioRaw - $montoPagado);
                    } else {
                      // Respaldo si solo viene el anticipo del 20% (monto * 4 = 80% restante)
                      $faltaPorPagar = max(0.0, round($montoPagado * 4, 2));
                    }

                    $idPagoStr = htmlspecialchars((string)($idPago ?? ''), ENT_QUOTES, 'UTF-8');
                    $idCitaStr = htmlspecialchars((string)($idCita ?? ''), ENT_QUOTES, 'UTF-8');
                    $metodoStr = htmlspecialchars($metodo !== '' ? $metodo : 'No especificado', ENT_QUOTES, 'UTF-8');
                  ?>
                    <tr>
                      <td><strong>#<?= $idPagoStr !== '' ? $idPagoStr : '—' ?></strong></td>
                      <td>Cita #<?= $idCitaStr !== '' ? $idCitaStr : '—' ?></td>
                      <td><strong>$<?= number_format($montoPagado, 2) ?></strong></td>
                      <td><?= $metodoStr ?></td>
                      <td>
                        <?php if ($faltaPorPagar <= 0): ?>
                          <span style="color: #15803d; font-weight: 600;">$0.00 (Liquidado)</span>
                        <?php else: ?>
                          <span style="color: #b45309; font-weight: 700;">$<?= number_format($faltaPorPagar, 2) ?></span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

      </div>
    </main>
  </div>

  <script>
    const menuItems = document.querySelectorAll('.menu-item');
    const tabs = document.querySelectorAll('.dashboard-tab');
    const tabTitle = document.getElementById('current-tab-title');    
   
    function abrirModalServicio() {
        document.getElementById('modalServicio').style.display = 'flex';
    }

    function cerrarModalServicio() {
        document.getElementById('modalServicio').style.display = 'none';
    } 
      
    function abrirModalEmpleado() {
        document.getElementById('modalEmpleado').style.display = 'flex';
    }

    function cerrarModalEmpleado() {
        document.getElementById('modalEmpleado').style.display = 'none';
    }
      
      // Funciones para abrir y cerrar el modal de clientes
    function abrirModalCliente() {
        document.getElementById('modalCliente').style.display = 'flex';
    }

    function cerrarModalCliente() {
        document.getElementById('modalCliente').style.display = 'none';
    }
// Función para abrir el comprobante guardado en Base64
    function verComprobante(base64Data) {
        let ventana = window.open();
        ventana.document.write(`
            <html>
                <head>
                    <title>Comprobante de Pago - Consientt Spa</title>
                </head>
                <body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#1a1a1a; margin:0;">
                    <img src="${base64Data}" style="max-width:95%; max-height:95%; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.7);">
                </body>
            </html>
        `);
    }
    menuItems.forEach(item => {
      item.addEventListener('click', () => {
        menuItems.forEach(i => i.classList.remove('active'));
        tabs.forEach(t => t.classList.remove('active'));

        item.classList.add('active');
        const targetTab = item.getAttribute('data-target');
        document.getElementById(targetTab).classList.add('active');
        
        tabTitle.textContent = "Control de " + item.textContent.trim();
      });
    });
  </script>
</body>
</html>
