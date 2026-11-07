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

// 1. OBTENER CITAS (Filtradas por rol)
if ($es_acceso_total) {
    $sql_citas = "SELECT c.id_cita AS id_cita, cl.nombre AS cliente_nombre, e.nombre AS empleado_nombre, c.fecha_y_hora, c.estado, c.total 
                  FROM citas c
                  JOIN cliente cl ON c.id_cliente = cl.idcliente
                  JOIN empleados e ON c.id_empleado = e.idcliente
                  ORDER BY c.fecha_y_hora DESC";
    $stmt_citas = $conexion->prepare($sql_citas);
    $stmt_citas->execute();
} else {
    $sql_citas = "SELECT c.id_cita AS id_cita, cl.nombre AS cliente_nombre, e.nombre AS empleado_nombre, c.fecha_y_hora, c.estado, c.total 
                  FROM citas c
                  JOIN cliente cl ON c.id_cliente = cl.idcliente
                  JOIN empleados e ON c.id_empleado = e.id_empleado 
                  WHERE c.id_empleado = ?
                  ORDER BY c.fecha_y_hora DESC";
    $stmt_citas = $conexion->prepare($sql_citas);
    $stmt_citas->execute([$empleado_id]);
}
$citas = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);

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
            <button class="btn-action"><i class="fas fa-plus"></i> Nueva Cita</button>
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
                </tr>
              </thead>
              <tbody>
                <?php if (empty($citas)): ?>
                  <tr><td colspan="6" style="text-align: center;">No hay citas registradas.</td></tr>
                <?php else: ?>
                  <?php foreach ($citas as $cita): ?>
                    <tr>
                      <td><strong>#<?php echo $cita['id_cita']; ?></strong></td>
                      <td><?php echo htmlspecialchars($cita['cliente_nombre']); ?></td>
                      <td><?php echo htmlspecialchars($cita['empleado_nombre']); ?></td>
                      <td><?php echo htmlspecialchars($cita['fecha_hora']); ?></td>
                      <td><span class="status-badge <?php echo strtolower($cita['estado']); ?>"><?php echo htmlspecialchars($cita['estado']); ?></span></td>
                      <td><strong>$<?php echo number_format($cita['total'], 2); ?></strong></td>
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
            <button class="btn-action"><i class="fas fa-plus"></i> Añadir Cliente</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Cliente</th>
                  <th>Nombre Completo</th>
                  <th>Teléfono</th>
                  <th>Correo Electrónico</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($clientes as $cli): ?>
                  <tr>
                    <td><strong>#<?php echo $cli['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($cli['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($cli['telefono'] ?? 'N/D'); ?></td>
                    <td><?php echo htmlspecialchars($cli['correo']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- PESTAÑA: EMPLEADOS -->
        <section id="tab-empleados" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>empleados</code></h3>
            <button class="btn-action"><i class="fas fa-plus"></i> Registrar Empleado</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Empleado</th>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Rol</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($empleados as $emp): ?>
                  <tr>
                    <td><strong>#<?php echo $emp['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($emp['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($emp['correo']); ?></td>
                    <td><?php echo htmlspecialchars($emp['rol']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- PESTAÑA: SERVICIOS -->
        <section id="tab-servicios" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>servicios</code></h3>
            <button class="btn-action"><i class="fas fa-plus"></i> Agregar Servicio</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Servicio</th>
                  <th>Nombre del Servicio</th>
                  <th>Precio Base</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($servicios as $ser): ?>
                  <tr>
                    <td><strong>#<?php echo $ser['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($ser['nombre']); ?></td>
                    <td><strong>$<?php echo number_format($ser['precio'], 2); ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- PESTAÑA: PAGOS -->
        <section id="tab-pagos" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>pagos</code></h3>
            <button class="btn-action"><i class="fas fa-file-invoice-dollar"></i> Reporte Financiero</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>ID Pago</th>
                  <th>ID Cita</th>
                  <th>Monto</th>
                  <th>Método</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pagos as $pag): ?>
                  <tr>
                    <td><strong>#<?php echo $pag['id']; ?></strong></td>
                    <td>Cita #<?php echo $pag['cita_id']; ?></td>
                    <td><strong>$<?php echo number_format($pag['monto'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars($pag['metodo']); ?></td>
                  </tr>
                <?php endforeach; ?>
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
