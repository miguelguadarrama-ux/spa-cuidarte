document.addEventListener('DOMContentLoaded', function () {

  /* ==========================================
     1. SELECTOR DE FECHA Y EMPLEADO
     ========================================== */
  const empleadoSelect = document.getElementById('id_empleado') || document.getElementById('empleadoSelect');
  const inputFechaCita = document.getElementById('inputFechaCita');
  const servicioSelect = document.getElementById('servicioSelect');
  const horasGrid = document.getElementById('horasGrid');

  let fechaSeleccionada = null;
  let horaSeleccionada = null;

  if (inputFechaCita) {
    const hoyStr = new Date().toISOString().split('T')[0];
    inputFechaCita.min = hoyStr;
    if (!inputFechaCita.value) {
      inputFechaCita.value = hoyStr;
    }
    fechaSeleccionada = inputFechaCita.value;

    inputFechaCita.addEventListener('change', (e) => {
      fechaSeleccionada = e.target.value;
      horaSeleccionada = null;
      cargarHorariosDisponibles();
      actualizarResumen();
    });
  }

  /* ==========================================
     2. CARGAR HORAS DINÁMICAS DESDE PHP
     ========================================== */
  function limpiarHorarios(mensaje = 'Selecciona servicio, especialista y fecha para ver los horarios.') {
    if (!horasGrid) return;
    horaSeleccionada = null;
    horasGrid.innerHTML = `<p style="color: #777; grid-column: 1 / -1; text-align: center; font-size: 0.9rem;">${mensaje}</p>`;
    actualizarResumen();
  }

  function seleccionarHora(el, hora) {
    if (!horasGrid) return;
    horasGrid.querySelectorAll('.hora-item').forEach(h => h.classList.remove('active'));
    el.classList.add('active');
    horaSeleccionada = hora;
    actualizarResumen();
  }

  function cargarHorariosDisponibles() {
    if (!horasGrid) return;

    const servicioVal = servicioSelect ? servicioSelect.value : '';
    const fechaVal = fechaSeleccionada || (inputFechaCita ? inputFechaCita.value : '');
    const empleadoVal = empleadoSelect ? empleadoSelect.value : '';

    if (!servicioVal || !fechaVal || !empleadoVal) {
      limpiarHorarios();
      return;
    }

    horasGrid.innerHTML = `<p style="color: #777; grid-column: 1 / -1; text-align: center; font-size: 0.9rem;">Buscando horarios disponibles...</p>`;

    const params = new URLSearchParams({
      servicio: servicioVal,
      fecha: fechaVal,
      empleado: empleadoVal
    });

    fetch(`obtener_horarios.php?${params.toString()}`)
      .then(response => response.json())
      .then(data => {
        // Soporta tanto un array directo ['09:00', ...] como un objeto { horarios: [...] }
        const horarios = Array.isArray(data) ? data : (data.horarios || []);
        horasGrid.innerHTML = '';
        horaSeleccionada = null;

        if (!horarios.length) {
          horasGrid.innerHTML = `<p style="color: #999; grid-column: 1 / -1; text-align: center; font-size: 0.9rem;">No hay horarios disponibles para esta combinación.</p>`;
          actualizarResumen();
          return;
        }

        horarios.forEach((hora, index) => {
          const div = document.createElement('div');
          div.classList.add('hora-item');
          div.textContent = hora;
          div.dataset.hora = hora;

          if (index === 0) {
            div.classList.add('active');
            horaSeleccionada = hora;
          }

          div.addEventListener('click', () => seleccionarHora(div, hora));
          horasGrid.appendChild(div);
        });

        actualizarResumen();
      })
      .catch(error => {
        console.error('Error al obtener horarios:', error);
        limpiarHorarios('No se pudieron cargar los horarios. Intenta de nuevo.');
      });
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
  const modalidadPagoSelect = document.getElementById('modalidadPago');
  const textoInstruccionPago = document.getElementById('textoInstruccionPago');

  let servicioActual = { nombre: '', precio: 0, duracion: '' };

  function actualizarResumen() {
    if (!servicioSelect) return;

    const opcion = servicioSelect.options[servicioSelect.selectedIndex];
    if (opcion && opcion.value) {
      servicioActual.nombre = opcion.dataset.nombre || opcion.value;
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
        cargarHorariosDisponibles();
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
      if (!horaSeleccionada) { valido = false; }

      if (!valido) {
        alert('⚠️ Revisa los campos obligatorios o selecciona un horario disponible.');
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

      const inputComprobante = document.getElementById('comprobante');
      if (inputComprobante && inputComprobante.files[0]) {
        datosFormulario.append('comprobante', inputComprobante.files[0]);
      }

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
    if (mensajeConfirmacion) {
      mensajeConfirmacion.innerHTML = `
        <strong>${data.message}</strong><br>
        <p style="font-size: 0.9rem; margin-top: 6px; margin-bottom: 0;">Tu espacio está <strong>Por Validar</strong> y te mandaremos mensaje por WhatsApp.</p>
      `;

      mensajeConfirmacion.classList.add('show');
      mensajeConfirmacion.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    setTimeout(() => {
      form.reset();
      if (mensajeConfirmacion) mensajeConfirmacion.classList.remove('show');
      if (servicioSelect) servicioSelect.selectedIndex = 0;
      if (inputFechaCita) {
        const hoyStr = new Date().toISOString().split('T')[0];
        inputFechaCita.value = hoyStr;
        fechaSeleccionada = hoyStr;
      }
      servicioActual = { nombre: '', precio: 0, duracion: '' };
      cargarHorariosDisponibles();
      actualizarResumen();
    }, 15000);
  } else {
    alert('Error: ' + data.message);
  }
}catch (e) {
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
     7. INICIALIZAR Y EVENTOS
     ========================================== */
  if (servicioSelect) {
    servicioSelect.addEventListener('change', () => {
      cargarHorariosDisponibles();
      actualizarResumen();
    });
  }
  if (empleadoSelect) {
    empleadoSelect.addEventListener('change', cargarHorariosDisponibles);
  }
  if (modalidadPagoSelect) {
    modalidadPagoSelect.addEventListener('change', actualizarResumen);
  }

  cargarHorariosDisponibles();
  actualizarResumen();
});
