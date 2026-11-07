<?php
session_start();
// Seguridad: Si no ha iniciado sesión o no es administrador, lo mandamos al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrativo | Consientt Spa</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>

  <!-- Contenedor General del Dashboard -->
  <div class="dashboard-container">
    
    <!-- Barra Lateral de Navegación (Tablas SQL) -->
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
      
      <!-- Encabezado Superior -->
      <header class="content-header">
        <div class="header-title">
          <span class="header-subtitle">Gestión Interna</span>
          <h1 id="current-tab-title">Control de Citas</h1>
        </div>
        <div class="admin-profile">
          <div class="admin-info">
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?></span>
            <span class="admin-role">Acceso Total</span>
          </div>
          <div class="admin-avatar"><?php echo strtoupper(substr($_SESSION['nombre'] ?? 'A', 0, 1)); ?></div>
        </div>
      </header>

      <!-- SECCIÓN PRINCIPAL: CAMBIANTE POR PESTAÑAS -->
      <div class="content-body">
        
        <!-- PESTAÑA: CITAS -->
        <section id="tab-citas" class="dashboard-tab active">
          <div class="action-bar">
            <h3>Registros de <code>citas</code> y <code>detalle_citas</code></h3>
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
                  <th>Servicios Solicitados (Detalle)</th>
                  <th>Estado</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>#1</strong></td>
                  <td>Ana García</td>
                  <td>Clara Gómez</td>
                  <td>12/10/2026 15:30</td>
                  <td><span class="badge-service">Pedicure Clínico Completo</span></td>
                  <td><span class="status-badge pendiente">Pendiente</span></td>
                  <td><strong>$650.00</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- PESTAÑA: CLIENTES -->
        <section id="tab-clientes" class="dashboard-tab">
          <div class="action-bar">
            <h3>Registros de <code>cliente</code></h3>
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
                <tr>
                  <td><strong>#1</strong></td>
                  <td>Ana García</td>
                  <td>+52 55 1234 5678</td>
                  <td>ana@ejemplo.com</td>
                </tr>
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
                  <th>Especialidad</th>
                  <th>Teléfono</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>#1</strong></td>
                  <td>Clara Gómez</td>
                  <td>Podología Clínica</td>
                  <td>+52 55 9876 5432</td>
                </tr>
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
                  <th>Duración</th>
                  <th>Precio Base</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>#1</strong></td>
                  <td>Pedicure Clínico Completo</td>
                  <td>60 min</td>
                  <td><strong>$650.00</strong></td>
                </tr>
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
                  <th>ID Cita Relacionada</th>
                  <th>Monto Pagado (20% o Total)</th>
                  <th>Fecha de Pago</th>
                  <th>Método de Pago</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>#1</strong></td>
                  <td>Cita #1</td>
                  <td><strong>$130.00</strong></td>
                  <td>10/10/2026 19:38</td>
                  <td>Tarjeta de Crédito</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </div>
    </main>
  </div>

  <!-- JavaScript básico para cambio de pestañas interactivo -->
  <script>
    const menuItems = document.querySelectorAll('.menu-item');
    const tabs = document.querySelectorAll('.dashboard-tab');
    const tabTitle = document.getElementById('current-tab-title');

    menuItems.forEach(item => {
      item.addEventListener('click', () => {
        // Remover clases activas pasadas
        menuItems.forEach(i => i.classList.remove('active'));
        tabs.forEach(t => t.classList.remove('active'));

        // Activar la nueva pestaña seleccionada
        item.classList.add('active');
        const targetTab = item.getAttribute('data-target');
        document.getElementById(targetTab).classList.add('active');
        
        // Actualizar título dinámico en cabecera
        tabTitle.textContent = "Control de " + item.textContent.trim();
      });
    });
  </script>
</body>
</html>
