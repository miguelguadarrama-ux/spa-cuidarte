document.addEventListener('DOMContentLoaded', function () {

  /* ==========================================
     1. SELECTOR DE FECHA LIBRE
     ========================================== */
  const empleadoSelect = document.getElementById('id_empleado');
  const inputFechaCita = document.getElementById('inputFechaCita');
  let fechaSeleccionada = null;

  if (inputFechaCita) {
    const hoyStr = new Date().toISOString().split('T')[0];
    inputFechaCita.min = hoyStr;
    if (!inputFechaCita.value) {
      inputFechaCita.value = hoyStr;
    }
    fechaSeleccionada = inputFechaCita.value;

    inputFechaCita.addEventListener('change', (e) => {
      fechaSeleccionada = e.target.value;
      actualizarResumen();
    });
  }

  /* ==========================================
     2. GENERAR HORAS DISPONIBLES
     ========================================== */
  const horasGrid = document.getElementById('horasGrid');
  const horasDisponibles = ['09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '19:30'];
  let horaSeleccionada = null;

  function generarHoras() {
    if (!horasGrid) return;
    horasGrid.innerHTML = '';
    horasDisponibles.forEach((hora, index) => {
      const div = document.createElement('div');
      div.classList.add('hora-item');
      if (index === 2) div.classList.add('active');
      div.textContent = hora;
      div.dataset.hora = hora;
      div.addEventListener('click', () => seleccionarHora(div));
      horasGrid.appendChild(div);
    });
    const activa = horasGrid.querySelector('.hora-item.active');
    if (activa) horaSeleccionada = activa.dataset.hora;
  }

  function seleccionarHora(el) {
    if (!horasGrid) return;
    horasGrid.querySelectorAll('.hora-item').forEach(h => h.classList.remove('active'));
    el.classList.add('active');
    horaSeleccionada = el.dataset.hora;
    actualizarResumen();
  }

  /* ==========================================
     3. ACTUALIZAR RESUMEN DE CITA Y MODALIDAD
     ========================================== */
  const resumenServicio = document.getElementById('resumenServicio');
  const resumenFecha = document.getElementById('resumenFecha');
  const resumenHora = document.getElementById('resumenHora');
  const resumenDuracion = document.getElementById('resumenDuracion');
  const resumenTotal = document.getElementById('resumenTotal');
  const resumenAnticipo = document.getElementById('resumenAnticipo');
  const textoLabelPago = document.getElementById('textoLabelPago');
  const servicioSelect = document.getElementById('servicioSelect');
  const modalidadPagoSelect = document.getElementById('modalidadPago');
  const textoInstruccionPago = document.getElementById('textoInstruccionPago');

  let servicioActual = { nombre: '', precio: 0, duracion: '' };

  function actualizarResumen() {
    if (!servicioSelect) return;

    const opcion = servicioSelect.options[servicioSelect.selectedIndex];
    if (opcion && opcion.value) {
      servicioActual.nombre = opcion.value;
      servicioActual.precio = parseInt(opcion.dataset.precio, 10) || 0;
      servicioActual.duracion = opcion.dataset.duracion || '—';
    } else {
      servicioActual = { nombre: '', precio: 0, duracion: '' };
    }

    if (resumenServicio) resumenServicio.textContent = servicioActual.nombre || '—';
    if (resumenFecha) resumenFecha.textContent = fechaSeleccionada || (inputFechaCita ? inputFechaCita.value : '—');
    if (resumenHora) resumenHora.textContent = horaSeleccionada || '—';
    if (resumenDuracion) resumenDuracion.textContent = servicioActual.duracion || '—';
    if (resumenTotal) resumenTotal.textContent = `$${servicioActual.precio}`;

    const esCompleto = modalidadPagoSelect && modalidadPagoSelect.value === 'completo';
    const montoTransferencia = esCompleto ? servicioActual.precio : Math.round(servicioActual.precio * 0.2);

    if (textoLabelPago) {
      textoLabelPago.textContent = esCompleto ? 'Total a transferir (100%)' : 'Anticipo a pagar (20%)';
    }

    if (resumenAnticipo) {
      resumenAnticipo.textContent = `$${montoTransferencia}`;
    }

    if (textoInstruccionPago) {
      if (esCompleto) {
        textoInstruccionPago.innerHTML = `Realiza tu transferencia por el <strong>100% del servicio ($${montoTransferencia})</strong> para dejar tu cita completamente pagada.`;
      } else {
        textoInstruccionPago.innerHTML = `Realiza tu transferencia del <strong>20% de anticipo ($${montoTransferencia})</strong> para apartar tu lugar. El 80% restante se liquida al asistir.`;
      }
    }
  }

  /* ==========================================
     4. FILTROS DE SERVICIOS
     ========================================== */
  const tabs = document.querySelectorAll('#serviciosTabs button');
  const serviciosItems = document.querySelectorAll('.servicio-item');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const filtro = tab.dataset.filtro;
      serviciosItems.forEach(item => {
        if (filtro === 'todos' || item.dataset.categoria === filtro) {
          item.classList.remove('oculto');
        } else {
          item.classList.add('oculto');
        }
      });
    });
  });

  document.querySelectorAll('.ritual-link').forEach(link => {
    link.addEventListener('click', () => {
      const servicio = link.dataset.servicio;
      if (servicio && servicioSelect) {
        for (let i = 0; i < servicioSelect.options.length; i++) {
          if (servicioSelect.options[i].value === servicio) {
            servicioSelect.selectedIndex = i;
            break;
          }
        }
        actualizarResumen();
      }
    });
  });

  /* ==========================================
     5. BOTONES QUE LLEVAN A AGENDAR (SCROLL)
     ========================================== */
  const botonesCita = [
    'btnHeaderCita',
    'btnHeroCita',
    'btnHeroCard',
    'btnExperienciaCita'
  ];

  botonesCita.forEach(id => {
    const btn = document.getElementById(id);
    if (btn) {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const seccionCitas = document.getElementById('citas');
        if (seccionCitas) {
          seccionCitas.scrollIntoView({ behavior: 'smooth' });
        }
      });
    }
  });

  const btnVerPrecios = document.getElementById('btnVerPrecios');
  if (btnVerPrecios) {
    btnVerPrecios.addEventListener('click', (e) => {
      e.preventDefault();
      const seccionPrecios = document.getElementById('precios');
      if (seccionPrecios) {
        seccionPrecios.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  /* ==========================================
     6. VALIDACION Y ENVIO DEL FORMULARIO
     ========================================== */
  const form = document.getElementById('formCita');
  const mensajeConfirmacion = document.getElementById('mensajeConfirmacion');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const nombre = document.getElementById('nombre');
      const telefono = document.getElementById('telefono');
      const email = document.getElementById('email');

      let valido = true;

      [nombre, telefono, email].forEach(campo => {
        if (campo) campo.classList.remove('error');
      });

      if (nombre && !nombre.value.trim()) { nombre.classList.add('error'); valido = false; }
      if (telefono && (!telefono.value.trim() || telefono.value.trim().length < 8)) { telefono.classList.add('error'); valido = false; }
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (email && (!email.value.trim() || !emailRegex.test(email.value.trim()))) { email.classList.add('error'); valido = false; }
      if (servicioSelect && !servicioSelect.value) { servicioSelect.classList.add('error'); valido = false; }

      if (!valido) {
        alert('⚠️ Te faltan campos por rellenar o hay información inválida. Por favor, revisa los campos marcados en rojo.');
        return;
      }

      const fechaFinal = fechaSeleccionada || (inputFechaCita ? inputFechaCita.value : '');

      const datosFormulario = new FormData();
      datosFormulario.append('nombre', nombre ? nombre.value.trim() : '');
      datosFormulario.append('telefono', telefono ? telefono.value.trim() : '');
      datosFormulario.append('email', email ? email.value.trim() : '');
      datosFormulario.append('servicio', servicioSelect ? servicioSelect.value : '');
      datosFormulario.append('fecha', fechaFinal);
      datosFormulario.append('hora', horaSeleccionada);
      datosFormulario.append('modalidad_pago', modalidadPagoSelect ? modalidadPagoSelect.value : 'anticipo');
      datosFormulario.append('id_empleado', empleadoSelect ? empleadoSelect.value : '');

      fetch('guardar_cita.php', {
        method: 'POST',
        body: datosFormulario
      })
      .then(response => response.text())
      .then(texto => {
        console.log("Respuesta del servidor:", texto);
        try {
          const data = JSON.parse(texto);
          if (data.status === 'success') {
            const numeroWhatsApp = "523322530111";
            const mensajeWp = encodeURIComponent("Hola, acabo de agendar mi cita en Consientt Spa y aquí está mi comprobante de transferencia.");

            if (mensajeConfirmacion) {
              mensajeConfirmacion.innerHTML = `
                <strong>${data.message}</strong><br><br>
                <p style="font-size: 0.9rem; margin-bottom: 10px;">Haz clic en el siguiente botón para enviarnos tu comprobante por WhatsApp y validar tu espacio de inmediato:</p>
                <a href="https://wa.me/${numeroWhatsApp}?text=${mensajeWp}" target="_blank" style="display: inline-block; background: #25d366; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.95rem;">
                  <i class="fab fa-whatsapp"></i> Enviar comprobante por WhatsApp
                </a>
              `;

              mensajeConfirmacion.classList.add('show');
              mensajeConfirmacion.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            setTimeout(() => {
              form.reset();
              if (mensajeConfirmacion) mensajeConfirmacion.classList.remove('show');
              if (servicioSelect) servicioSelect.selectedIndex = 0;
              servicioActual = { nombre: '', precio: 0, duracion: '' };
              generarHoras();
              actualizarResumen();
            }, 15000);
          } else {
            alert('Error: ' + data.message);
          }
        } catch(e) {
          console.error("El servidor devolvió HTML en lugar de JSON:", texto);
          alert('Hubo un error en el archivo PHP. Revisa la consola con F12.');
        }
      })
      .catch(error => {
        console.error('Error de red:', error);
        alert('Ocurrió un error al conectar con el servidor.');
      });
    });
  }

  /* ==========================================
     7. INICIALIZAR
     ========================================== */
  generarHoras();
  actualizarResumen();

  if (servicioSelect) servicioSelect.addEventListener('change', actualizarResumen);
  if (modalidadPagoSelect) modalidadPagoSelect.addEventListener('change', actualizarResumen);

});
