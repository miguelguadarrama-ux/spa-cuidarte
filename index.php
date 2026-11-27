<?php
session_start();
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consientt Spa | Pedicure y Manicure</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Header -->
  <header>
    <div class="container header-flex">
      <div class="logo">
        <i class="fas fa-spa"></i> Consientt Spa
      </div>
      <nav>
        <a href="#servicios">Servicios</a>
        <a href="#citas">Agendar Cita</a>
        <a href="#experiencia">Sobre Nosotros</a>
        <a href="#contacto">Contacto</a>
        
        <?php if (isset($_SESSION['nombre'])): ?>
          <!-- Contenedor estilizado para el usuario, sus citas y cerrar sesión -->
          <div class="user-session-container">
            <span class="user-greeting">
              <i class="fas fa-user-circle"></i> ¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!
            </span>
            <a href="mis_citas.php" class="btn-mis-citas" style="margin-right: 8px; text-decoration: none; font-weight: 600;">
              <i class="fas fa-calendar-check"></i> Mis Citas
            </a>
            <a href="logout.php" class="btn-cerrar-sesion">
              <i class="fas fa-sign-out-alt"></i> Salir
            </a>
          </div>
        <?php else: ?>
          <!-- Se muestra cuando NO se ha iniciado sesión -->
          <a href="login.php" style="color: var(--primary-color, #b8860b); font-weight: 600;"><i class="fas fa-user-lock"></i> Ingresar</a>
          <a href="Registro.php" style="color: var(--primary-color, #b8860b); font-weight: 600;">Registrar</a>
        <?php endif; ?>
      </nav>
      <div class="header-actions">
        <button class="btn-primary" id="btnHeaderCita">Agendar Cita</button>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-text">
        <div class="hero-tag">
          <i class="fas fa-star"></i> Excelencia en cada detalle
        </div>
        <h1>Salud y belleza para tus pies y manos</h1>
        <p>Pedicure clínico con instrumental esterilizado, rituales spa de hidratación profunda y manicure de alta gama. Higiene de quirófano, calma de spa.</p>
        <div class="hero-buttons">
          <button class="btn-primary" id="btnHeroCita">Agendar Cita</button>
          <button class="btn-outline" id="btnVerPrecios">Ver todos los precios</button>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <h3>100% estéril</h3>
            <p>Instrumental</p>
          </div>
          <div class="hero-stat">
            <h3>Anticipo 20%</h3>
            <p>Para reservar</p>
          </div>
        </div>
      </div>
      <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1600948836101-f9ffda59d250?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Spa de pedicure y manicure">
        <div class="hero-card">
          <div>
            <p>Pedicure spa relajante con hidratación profunda</p>
            <span>Duración aproximada: 60 min</span>
          </div>
          <button class="btn-primary" style="padding: 10px 20px; font-size: 0.8rem;" id="btnHeroCard">Reservar</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Rituales -->
  <section class="rituales" id="servicios">
    <div class="container">
      <div class="section-header">
        <h2>Elige tu ritual</h2>
        <a href="#precios">Ver todos los servicios <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="rituales-grid">
        <!-- Ritual 1 -->
        <div class="ritual-card">
          <img src="https://images.unsplash.com/photo-1519415510236-718bdfcd89c8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Pedicure Clínico">
          <div class="ritual-card-content">
            <span class="ritual-tag">Clínico</span>
            <h3>Pedicure Clínico</h3>
            <p>Tratamiento especializado para uñas encarnadas, callosidades y hongos. Instrumental estéril.</p>
            <div class="ritual-footer">
              <span class="ritual-price">Desde $650</span>
              <a href="#citas" class="ritual-link" data-servicio="Pedicure Clínico Completo">Reservar <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <!-- Ritual 2 -->
        <div class="ritual-card">
          <img src="https://images.unsplash.com/photo-1519823551278-64ac92734fb1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Pedicure Spa">
          <div class="ritual-card-content">
            <span class="ritual-tag">Spa</span>
            <h3>Pedicure Spa</h3>
            <p>Baño de sales, exfoliación con azúcar, mascarilla de barro y masaje con piedras calientes.</p>
            <div class="ritual-footer">
              <span class="ritual-price">Desde $800</span>
              <a href="#citas" class="ritual-link" data-servicio="Spa de Hidratación Profunda">Reservar <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <!-- Ritual 3 -->
        <div class="ritual-card">
          <img src="https://images.unsplash.com/photo-1604654894610-df63bc536371?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Uñas de Gelish">
          <div class="ritual-card-content">
            <span class="ritual-tag">Estética</span>
            <h3>Uñas de Gelish</h3>
            <p>Esmaltado semipermanente de larga duración, con preparación completa y diseño personalizado.</p>
            <div class="ritual-footer">
              <span class="ritual-price">Desde $600</span>
              <a href="#citas" class="ritual-link" data-servicio="Pedicure Spa + Gelish">Reservar <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

 <div class="servicios-lista" id="serviciosLista">
        <?php
        require_once 'conexion.php';

        try {
          $stmtSrv = $conexion->query("SELECT * FROM servicios ORDER BY id_servicio DESC");
          $serviciosBD = $stmtSrv->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
          $serviciosBD = [];
        }

        if (empty($serviciosBD)):
        ?>
          <p style="color: var(--text-muted); text-align: center; padding: 20px;">
            No hay servicios registrados todavía. Agrega uno desde tu panel de administrador.
          </p>
        <?php
        else:
          foreach ($serviciosBD as $srv):
            $nombre = htmlspecialchars($srv['nombre'] ?? 'Servicio', ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars($srv['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');

            // Normalizar categoría para filtros: clinico, spa, estetica
            $catRaw = strtolower(trim($srv['categoria'] ?? ''));
            $catRaw = str_replace(['í', 'é', 'á', 'ó', 'ú'], ['i', 'e', 'a', 'o', 'u'], $catRaw);
            $categoria = in_array($catRaw, ['clinico', 'spa', 'estetica']) ? $catRaw : 'clinico';

            // Asignar ícono garantizado para cualquier servicio o categoría
            $nombreLower = strtolower($srv['nombre'] ?? '');
            if (strpos($nombreLower, 'manicure') !== false || strpos($nombreLower, 'gelish') !== false || strpos($nombreLower, 'mano') !== false || $categoria === 'estetica') {
              $iconoClase = 'fas fa-gem';
            } elseif ($categoria === 'spa' || strpos($nombreLower, 'spa') !== false || strpos($nombreLower, 'masaje') !== false) {
              $iconoClase = 'fas fa-spa';
            } else {
              $iconoClase = 'fas fa-spa';
            }

            // Precio y duración
            $precioNum = is_numeric($srv['precio'] ?? null) ? (float)$srv['precio'] : 0;
            $precioFormateado = '$' . number_format($precioNum, ($precioNum == (int)$precioNum) ? 0 : 2, '.', ',');

            $duracionRaw = trim((string)($srv['duracion'] ?? '60'));
            $duracionTexto = (stripos($duracionRaw, 'min') !== false) ? $duracionRaw : ($duracionRaw . ' min');

            $badge = htmlspecialchars($srv['etiqueta'] ?? ($srv['badge'] ?? ''), ENT_QUOTES, 'UTF-8');
        ?>
          <div class="servicio-item"
               data-categoria="<?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') ?>"
               data-nombre="<?= $nombre ?>"
               data-precio="<?= htmlspecialchars((string)$precioNum, ENT_QUOTES, 'UTF-8') ?>"
               data-duracion="<?= htmlspecialchars($duracionTexto, ENT_QUOTES, 'UTF-8') ?>">
            <div class="servicio-info">
              <div class="servicio-icon"><i class="<?= $iconoClase ?>"></i></div>
              <div class="servicio-detalle">
                <h3>
                  <?= $nombre ?>
                  <?php if (!empty($badge)): ?>
                    <span class="servicio-badge"><?= $badge ?></span>
                  <?php endif; ?>
                </h3>
                <?php if (!empty($descripcion)): ?>
                  <p><?= $descripcion ?></p>
                <?php endif; ?>
              </div>
            </div>
            <div class="servicio-precio">
              <span class="precio"><?= $precioFormateado ?></span>
              <span class="duracion"><?= htmlspecialchars($duracionTexto, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          </div>
        <?php
          endforeach;
        endif;
        ?>
      </div>

  <!-- Agendamiento -->
  <section class="agendamiento" id="citas">
    <div class="container">
      <h2>Agenda y aparta con 20% de anticipo</h2>
      <p class="sub">Reserva tu cita en línea. Te confirmaremos por WhatsApp.</p>

      <?php if (isset($_SESSION['nombre'])): ?>
        <!-- FORMULARIO VISIBLE SOLO SI TIENE SESIÓN INICIADA -->
        <div class="agendamiento-grid">
          <!-- Formulario -->
          <form class="agendamiento-form" id="formCita" enctype="multipart/form-data" novalidate>
          <!-- Paso 1 -->
            <div class="form-step">
              <h3><span class="step-number">1</span> Elige fecha y horario</h3>
              <div class="form-group" style="margin-bottom: 20px;">
                <label for="inputFechaCita" style="font-weight: 600; margin-bottom: 8px; display: block;">Fecha de la cita</label>
                <input type="date" id="inputFechaCita" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Inter', sans-serif;">
              </div>
              
              <label style="font-weight: 600; margin-bottom: 8px; display: block;">Horarios disponibles</label>
              <div class="horas-grid" id="horasGrid">
                <!-- JS genera las horas -->
              </div>
            </div>

            <!-- Paso 2 -->
            <div class="form-step">
              <h3><span class="step-number">2</span> Tus datos</h3>
              <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" placeholder="Ej. Ana García" value="<?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?>" required>
              </div>
              <div class="form-group">
                <label for="telefono">Teléfono (WhatsApp)</label>
                <input type="tel" id="telefono" placeholder="+52 55 1234 5678" required>
              </div>
              <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" placeholder="ana@ejemplo.com" value="<?php echo htmlspecialchars($_SESSION['correo'] ?? ''); ?>" required>
              </div>
            </div>

           <!-- Paso 3 -->
            <div class="form-step">
              <h3><span class="step-number">3</span> Servicio, especialista y pago</h3>
              
          <!-- Selector de Servicio (Dinámico desde Neon) -->
              <div class="form-group" style="margin-bottom: 15px;">
                <label for="servicioSelect">Servicio deseado</label>
                <select id="servicioSelect" name="id_servicio" required>
                  <option value="" disabled selected>Selecciona un servicio</option>
                  <?php
                  include 'conexion.php';
                  $stmt_srv = $conexion->query("SELECT * FROM servicios ORDER BY nombre ASC");
                  while ($srv = $stmt_srv->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $srv['id_servicio'] . '" data-precio="' . $srv['precio'] . '" data-duracion="' . $srv['duracion'] . ' min">'
                  . htmlspecialchars($srv['nombre']) . ' — $' . number_format($srv['precio'], 2) . ' (' . $srv['duracion'] . ' mins)'
                  . '</option>';
                  }
                  ?>
                </select>
              </div>

              <!-- 📌 Selector de Empleado / Especialista -->
              <div class="form-group" style="margin-bottom: 15px;">
                <label for="id_empleado">Especialista deseado</label>
                <select id="id_empleado" name="id_empleado" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;">
                  <option value="" selected>Asignar automáticamente (El primero disponible)</option>
                  <?php
                    try {
                        $stmtEmpleadosTab = $conexion->query("SELECT id_empleado, nombre FROM empleados ORDER BY nombre ASC");
                        while ($rowEmp = $stmtEmpleadosTab->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . $rowEmp['id_empleado'] . '">' . htmlspecialchars($rowEmp['nombre']) . '</option>';
                        }
                    } catch (Exception $e) {
                        // Si hay algún detalle con la conexión, no rompe la página
                    }
                  ?>
                </select>
              </div>

              <!-- Selector de modalidad de pago -->
              <div class="form-group" style="margin-bottom: 15px;">
                <label for="modalidadPago">¿Cómo deseas pagar?</label>
                <select id="modalidadPago" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;">
                  <option value="anticipo" selected>Solo anticipo (20%) — El 80% en recepción</option>
                  <option value="completo">Pagar servicio completo (100%)</option>
                </select>
              </div>

              <!-- Caja informativa con datos de transferencia -->
              <div style="background: #fdf8f0; border: 1px solid #e6d5bc; padding: 15px; border-radius: 8px; margin-top: 15px;">
                <p style="font-weight: 600; color: #b8860b; margin-bottom: 5px;"><i class="fas fa-university"></i> Datos para Transferencia Bancaria</p>
                <p style="font-size: 0.88rem; color: #555; margin-bottom: 8px;" id="textoInstruccionPago">
                  Realiza tu transferencia del <strong>20% de anticipo</strong> para apartar tu lugar. El resto se liquida al asistir.
                </p>
                <div style="font-size: 0.85rem; color: #444; background: #fff; padding: 10px; border-radius: 6px; border: 1px dashed #d4bc96;">
                  <strong>Banco:</strong> BBVA<br>
                  <strong>CLABE:</strong> 012345678901234567<br>
                  <strong>Titular:</strong> Consientt Spa
                </div>
              </div>
              <!-- 📌 NUEVO: Campo para subir el comprobante de pago -->
              <div class="form-group" style="margin-bottom: 15px;">
                <label for="comprobante" style="font-weight: 600; display: block; margin-bottom: 5px;">
                  <i class="fas fa-file-upload"></i> Sube tu comprobante de transferencia
                </label>
                <input type="file" id="comprobante" name="comprobante" accept="image/*,.pdf" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; background: #fff; font-family: 'Inter', sans-serif;">
                <small style="color: #666; font-size: 0.8rem; display: block; margin-top: 4px;">Formatos aceptados: Imagen (JPG, PNG) o PDF. (Máx. 5MB)</small>
              </div>
            </div>

            <div class="mensaje-confirmacion" id="mensajeConfirmacion">
              <i class="fas fa-check-circle"></i> ¡Gracias! Tu cita ha sido agendada. Te contactaremos por WhatsApp para confirmar.
            </div>
          </form>

          <!-- Resumen -->
          <div class="resumen-cita">
            <h3>Resumen de tu cita</h3>
            <div class="resumen-item">
              <span>Servicio</span>
              <span id="resumenServicio">—</span>
            </div>
            <div class="resumen-item">
              <span>Fecha</span>
              <span id="resumenFecha">—</span>
            </div>
            <div class="resumen-item">
              <span>Hora</span>
              <span id="resumenHora">—</span>
            </div>
            <div class="resumen-item">
              <span>Duración</span>
              <span id="resumenDuracion">—</span>
            </div>
            <div class="resumen-total">
              <span>Total</span>
              <span id="resumenTotal">$0</span>
            </div>
            <div class="resumen-item" style="border-bottom: none; padding-top: 0;">
             <span id="textoLabelPago">Anticipo a pagar (20%)</span>
             <span id="resumenAnticipo">$0</span>
            </div>
            <button type="submit" form="formCita" class="btn-agendar" id="btnAgendar">Pagar y agendar cita</button>
            <p class="resumen-nota">Al agendar aceptas nuestra política de cancelación.</p>
          </div>
        </div>
      <?php else: ?>
        <!-- AVISO PARA VISITANTES SIN SESIÓN -->
        <div style="text-align: center; padding: 50px 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 30px auto;">
          <div style="font-size: 3rem; color: #b8860b; margin-bottom: 15px;">
            <i class="fas fa-lock"></i>
          </div>
          <h3 style="margin-bottom: 10px; color: #333;">Inicia sesión para agendar tu cita</h3>
          <p style="color: #666; margin-bottom: 25px;">Para brindarte una atención personalizada y llevar el control de tus reservas, necesitas iniciar sesión o crear una cuenta.</p>
          <div>
            <a href="login.php" class="btn-primary" style="padding: 12px 25px; text-decoration: none; display: inline-block; margin-right: 10px; border-radius: 6px;">Iniciar Sesión</a>
            <a href="Registro.php" class="btn-outline" style="padding: 12px 25px; text-decoration: none; display: inline-block; border-radius: 6px; border: 1px solid #b8860b; color: #b8860b;">Registrarse</a>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Experiencia -->
  <section class="experiencia" id="experiencia">
    <div class="container experiencia-grid">
      <div>
        <h2>Higiene clínica, trato de spa familiar</h2>
        <p>En Consientt Spa la salud de tus pies y manos se trata con el rigor de un quirófano y la calidez de un spa familiar.</p>
        <p>Nuestro equipo de podólogos certificados y estilistas expertas te acompañan en cada visita para que salgas renovado.</p>
        <div class="experiencia-botones">
          <button class="btn-primary" id="btnExperienciaCita">Agendar Cita</button>
          <button class="btn-outline">Conócenos</button>
        </div>
      </div>
      <div class="experiencia-imagen">
        <img src="https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Instrumental esterilizado">
        <div class="experiencia-card">
          <i class="fas fa-shield-alt"></i>
          <div>
            <strong>Esterilización garantizada</strong>
            <p>Instrumental con autoclave y bolsas selladas.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contacto">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="logo"><i class="fas fa-spa"></i> Consientt Spa</div>
          <p>Salud y belleza para tus pies y manos. Higiene clínica, trato de spa.</p>
        </div>
        <div class="footer-col">
          <h4>Servicios</h4>
          <ul>
            <li><a href="#servicios">Pedicure Clínico</a></li>
            <li><a href="#servicios">Pedicure Spa</a></li>
            <li><a href="#servicios">Manicure Ruso</a></li>
            <li><a href="#servicios">Gelish</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Contacto</h4>
          <ul>
            <li><a href="#">+52 55 3322530111</a></li>
            <li><a href="#">miguel.guadarrama@alumnos.udg.mx</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Horarios</h4>
          <ul>
            <li><a href="#">Lun - Dom: 9:00 - 20:00</a></li>
            <li><a href="#">Sujeto a disponibilidad</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2025 Consientt Spa. Todos los derechos reservados.</span>
        <span>Plutón</span>
      </div>
    </div>
  </footer>
<!-- JS -->
  <script src="script.js"></script>
  <script>
    // Escuchar cuando cambien los selectores o la fecha (usando tus IDs exactos)
    document.getElementById('servicioSelect')?.addEventListener('change', actualizarHorarios);
    document.getElementById('inputFechaCita')?.addEventListener('change', actualizarHorarios);
    document.getElementById('empleadoSelect')?.addEventListener('change', actualizarHorarios);

    function actualizarHorarios() {
        const servicioEl = document.getElementById('servicioSelect');
        const fechaEl = document.getElementById('inputFechaCita');
        const empleadoEl = document.getElementById('empleadoSelect');

        if (!servicioEl || !fechaEl || !empleadoEl) return;

        const servicio = servicioEl.value;
        const fecha = fechaEl.value;
        const empleado = empleadoEl.value;

        if (!servicio || !fecha || !empleado) return;

        // Llamada AJAX al archivo PHP para calcular los horarios según la duración del servicio
        fetch(`obtener_horarios.php?servicio=${encodeURIComponent(servicio)}&fecha=${encodeURIComponent(fecha)}&empleado=${encodeURIComponent(empleado)}`)
            .then(response => response.json())
            .then(horarios => {
                const contenedor = document.getElementById('horasGrid');
                if (!contenedor) return;
                contenedor.innerHTML = ''; // Limpiar horas anteriores

                if (!Array.isArray(horarios) || horarios.length === 0) {
                    contenedor.innerHTML = '<p style="color: #999; grid-column: 1 / -1; text-align: center;">No hay horarios disponibles para esta combinación.</p>';
                    return;
                }

                // Filtrar horarios para dejar ciclos de 90 minutos (1 hora y media)
                const horariosHoraYMedia = [];
                let ultimoMinuto = null;

                horarios.forEach(hora => {
                    const partes = String(hora).trim().split(':');
                    if (partes.length < 2) return;

                    const horasNum = parseInt(partes[0], 10);
                    const minsNum = parseInt(partes[1], 10);
                    if (Number.isNaN(horasNum) || Number.isNaN(minsNum)) return;

                    const minutosActuales = horasNum * 60 + minsNum;

                    if (ultimoMinuto === null || (minutosActuales - ultimoMinuto) >= 90) {
                        horariosHoraYMedia.push(hora);
                        ultimoMinuto = minutosActuales;
                    }
                });

                if (horariosHoraYMedia.length === 0) {
                    contenedor.innerHTML = '<p style="color: #999; grid-column: 1 / -1; text-align: center;">No hay horarios disponibles para esta combinación.</p>';
                    return;
                }

                // Crear dinámicamente los botones de horarios dentro de tu grid
                horariosHoraYMedia.forEach(hora => {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'btn-horario';
                    boton.textContent = hora;

                    // Evento al hacer clic en un horario disponible
                    boton.addEventListener('click', function() {
                        document.querySelectorAll('.btn-horario').forEach(b => {
                            b.classList.remove('active');
                            b.style.background = 'white';
                            b.style.color = '#333';
                        });
                        boton.classList.add('active');
                        boton.style.background = '#3a1e3a';
                        boton.style.color = 'white';

                        // Guardar la hora seleccionada en un input oculto para que se envíe al guardar la cita
                        let inputOculto = document.getElementById('inputHoraSeleccionada');
                        if (!inputOculto) {
                            inputOculto = document.createElement('input');
                            inputOculto.type = 'hidden';
                            inputOculto.id = 'inputHoraSeleccionada';
                            inputOculto.name = 'hora';
                            document.getElementById('formCita')?.appendChild(inputOculto) || document.querySelector('form')?.appendChild(inputOculto);
                        }
                        inputOculto.value = hora;
                    });

                    contenedor.appendChild(boton);
                });
            })
            .catch(error => console.error('Error al cargar horarios:', error));
    }
  </script>
</body>
</html> 
