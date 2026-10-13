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

  <!-- Servicios y Precios -->
  <section class="servicios" id="precios">
    <div class="container">
      <div class="servicios-header">
        <div>
          <h2>Servicios, precios y duración</h2>
          <p style="color: var(--text-muted); margin-top: 8px;">Precios transparentes. Sin sorpresas.</p>
        </div>
        <div class="servicios-tabs" id="serviciosTabs">
          <button class="active" data-filtro="todos">Todos</button>
          <button data-filtro="clinico">Clínico</button>
          <button data-filtro="spa">Spa</button>
          <button data-filtro="estetica">Estética</button>
        </div>
      </div>

      <div class="servicios-lista" id="serviciosLista">
        <!-- Servicio 1 -->
        <div class="servicio-item" data-categoria="clinico" data-nombre="Pedicure Clínico Completo" data-precio="650" data-duracion="60 min">
          <div class="servicio-info">
            <div class="servicio-icon"><i class="fas fa-footprint"></i></div>
            <div class="servicio-detalle">
              <h3>Pedicure Clínico Completo <span class="servicio-badge">Recomendado</span></h3>
              <p>Tratamiento integral para uñas encarnadas, callosidades y hongos. Instrumental estéril.</p>
            </div>
          </div>
          <div class="servicio-precio">
            <span class="precio">$650</span>
            <span class="duracion">60 min</span>
          </div>
        </div>

        <!-- Servicio 2 -->
        <div class="servicio-item" data-categoria="spa" data-nombre="Spa de Hidratación Profunda" data-precio="780" data-duracion="75 min">
          <div class="servicio-info">
            <div class="servicio-icon"><i class="fas fa-spa"></i></div>
            <div class="servicio-detalle">
              <h3>Spa de Hidratación Profunda <span class="servicio-badge">Popular</span></h3>
              <p>Baño de sales, exfoliación con azúcar, mascarilla de barro, masaje con piedras calientes.</p>
            </div>
          </div>
          <div class="servicio-precio">
            <span class="precio">$780</span>
            <span class="duracion">75 min</span>
          </div>
        </div>

        <!-- Servicio 3 -->
        <div class="servicio-item" data-categoria="estetica" data-nombre="Baile de Manos - Manicure Ruso" data-precio="480" data-duracion="50 min">
          <div class="servicio-info">
            <div class="servicio-icon"><i class="fas fa-gem"></i></div>
            <div class="servicio-detalle">
              <h3>Baile de Manos - Manicure Ruso</h3>
              <p>Limpieza profunda de cutículas, limado y esmaltado semipermanente de alta duración.</p>
            </div>
          </div>
          <div class="servicio-precio">
            <span class="precio">$480</span>
            <span class="duracion">50 min</span>
          </div>
        </div>

        <!-- Servicio 4 -->
        <div class="servicio-item" data-categoria="estetica" data-nombre="Pedicure Spa + Gelish" data-precio="950" data-duracion="90 min">
          <div class="servicio-info">
            <div class="servicio-icon"><i class="fas fa-paint-brush"></i></div>
            <div class="servicio-detalle">
              <h3>Pedicure Spa + Gelish <span class="servicio-badge">Nuevo</span></h3>
              <p>Ritual completo de spa + esmaltado semipermanente de larga duración. Incluye diseño.</p>
            </div>
          </div>
          <div class="servicio-precio">
            <span class="precio">$950</span>
            <span class="duracion">90 min</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Agendamiento -->
  <section class="agendamiento" id="citas">
    <div class="container">
      <h2>Agenda y aparta con 20% de anticipo</h2>
      <p class="sub">Reserva tu cita en línea. Te confirmaremos por WhatsApp.</p>

      <div class="agendamiento-grid">
        <!-- Formulario -->
        <form class="agendamiento-form" id="formCita" novalidate>
          <!-- Paso 1 -->
          <div class="form-step">
            <h3><span class="step-number">1</span> Elige fecha y horario</h3>
            <div class="fechas-grid" id="fechasGrid">
              <!-- JS genera las fechas -->
            </div>
            <div class="horas-grid" id="horasGrid">
              <!-- JS genera las horas -->
            </div>
          </div>

          <!-- Paso 2 -->
          <div class="form-step">
            <h3><span class="step-number">2</span> Tus datos</h3>
            <div class="form-group">
              <label for="nombre">Nombre completo</label>
              <input type="text" id="nombre" placeholder="Ej. Ana García" required>
            </div>
            <div class="form-group">
              <label for="telefono">Teléfono (WhatsApp)</label>
              <input type="tel" id="telefono" placeholder="+52 55 1234 5678" required>
            </div>
            <div class="form-group">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" placeholder="ana@ejemplo.com" required>
            </div>
          </div>

          <!-- Paso 3 -->
          <div class="form-step">
            <h3><span class="step-number">3</span> Servicio y anticipo</h3>
            <div class="form-group">
              <label for="servicioSelect">Servicio deseado</label>
              <select id="servicioSelect" required>
                <option value="" disabled selected>Selecciona un servicio</option>
                <option value="Pedicure Clínico Completo" data-precio="650" data-duracion="60 min">Pedicure Clínico Completo - $650</option>
                <option value="Spa de Hidratación Profunda" data-precio="780" data-duracion="75 min">Spa de Hidratación Profunda - $780</option>
                <option value="Baile de Manos - Manicure Ruso" data-precio="480" data-duracion="50 min">Baile de Manos - Manicure Ruso - $480</option>
                <option value="Pedicure Spa + Gelish" data-precio="950" data-duracion="90 min">Pedicure Spa + Gelish - $950</option>
              </select>
            </div>
            <div class="form-group">
              <label>Método de pago</label>
              <select id="metodoPago">
                <option>Tarjeta de crédito/débito</option>
                <option>Transferencia bancaria</option>
                <option>Efectivo en recepción</option>
              </select>
            </div>
            <div class="form-group">
              <label>Nombre en la tarjeta</label>
              <input type="text" id="nombreTarjeta" placeholder="Como aparece en la tarjeta">
            </div>
            <div class="form-group">
              <label>Número de tarjeta</label>
              <input type="text" id="numeroTarjeta" placeholder="0000 0000 0000 0000" maxlength="19">
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
            <span>Anticipo a pagar (20%)</span>
            <span id="resumenAnticipo">$0</span>
          </div>
          <button type="submit" form="formCita" class="btn-agendar" id="btnAgendar">Pagar y agendar cita</button>
          <p class="resumen-nota">Al agendar aceptas nuestra política de cancelación.</p>
        </div>
      </div>
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
            <li><a href="#">+52 55 1234 5678</a></li>
            <li><a href="#">hola@consienttspa.com</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Horarios</h4>
          <ul>
            <li><a href="#">Lun - Vie: 9:00 - 20:00</a></li>
            <li><a href="#">Sáb: 9:00 - 18:00</a></li>
            <li><a href="#">Dom: Cerrado</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2025 Consientt Spa. Todos los derechos reservados.</span>
        <span>Diseño por Tu Estudio</span>
      </div>
    </div>
  </footer>

  <!-- Botón flotante de WhatsApp -->
  <a href="https://wa.me/525512345678?text=Hola%2C%20quiero%20agendar%20una%20cita%20en%20Consientt%20Spa" 
     class="whatsapp-float" 
     target="_blank" 
     rel="noopener noreferrer" 
     aria-label="Contactar por WhatsApp">
    <i class="fab fa-whatsapp"></i>
  </a>

  <!-- JS -->
  <script src="script.js"></script>
</body>
</html>