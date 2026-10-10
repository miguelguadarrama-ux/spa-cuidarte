document.addEventListener('DOMContentLoaded', function () {

  /* ==========================================
     1. GENERAR FECHAS DISPONIBLES (próximos 5 días)
     ========================================== */
  const fechasGrid = document.getElementById('fechasGrid');
  const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

  let fechaSeleccionada = null;

  function generarFechas() {
    if (!fechasGrid) return;
    fechasGrid.innerHTML = '';
    const hoy = new Date();
    for (let i = 0; i < 5; i++) {
      const fecha = new Date(hoy);
      fecha.setDate(hoy.getDate() + i);
      const div = document.createElement('div');
      div.classList.add('fecha-item');
      if (i === 2) div.classList.add('active');
      div.dataset.fecha = `${diasSemana[fecha.getDay()]} ${fecha.getDate()} de ${meses[fecha.getMonth()]}`;
      div.innerHTML = `
        <span class="dia">${diasSemana[fecha.getDay()]}</span>
        <span class="numero">${fecha.getDate()}</span>
      `;
      div.addEventListener('click', () => seleccionarFecha(div));
      fechasGrid.appendChild(div);
    }
    const activa = fechasGrid.querySelector('.fecha-item.active');
    if (activa) fechaSeleccionada = activa.dataset.fecha;
  }

  function seleccionarFecha(el) {
    fechasGrid.querySelectorAll('.fecha-item').forEach(f => f.classList.remove('active'));
    el.classList.add('active');
    fechaSeleccionada = el.dataset.fecha;
    actualizarResumen();
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
    horasGrid.querySelectorAll('.hora-item').forEach(h => h.classList.remove('active'));
    el.classList.add('active');
    horaSeleccionada = el.dataset.hora;
    actualizarResumen();
  }

  /* ==========================================
     3. ACTUALIZAR RESUMEN DE CITA
     ========================================== */
  const resumenServicio = document.getElementById('resumenServicio');
  const resumenFecha = document.getElementById('resumenFecha');
  const resumenHora = document.getElementById('resumenHora');
  const resumenDuracion = document.getElementById('resumenDuracion');
  const resumenTotal = document.getElementById('resumenTotal');
  const resumenAnticipo = document.getElementById('resumenAnticipo');
  const servicioSelect = document.getElementById('servicioSelect');

  let servicioActual = {
    nombre: '',
    precio: 0,
    duracion: ''
  };

  function actualizarResumen() {
    const opcion = servicioSelect.options[servicioSelect.selectedIndex];
    if (opcion && opcion.value) {
      servicioActual.nombre = opcion.value;
      servicioActual.precio = parseInt(opcion.dataset.precio, 10) || 0;
      servicioActual.duracion = opcion.dataset.duracion || '—';
    } else {
      servicioActual = { nombre: '', precio: 0, duracion: '' };
    }

    resumenServicio.textContent = servicioActual.nombre || '—';
    resumenFecha.textContent = fechaSeleccionada || '—';
    resumenHora.textContent = horaSeleccionada || '—';
    resumenDuracion.textContent = servicioActual.duracion || '—';
    resumenTotal.textContent = `$${servicioActual.precio}`;
    const anticipo = Math.round(servicioActual.precio * 0.2);
    resumenAnticipo.textContent = `$${anticipo}`;
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

  /* ==========================================
     5. ENLACES "RESERVAR" DE LAS TARJETAS RITUALES
     ========================================== */
  document.querySelectorAll('.ritual-link').forEach(link => {
    link.addEventListener('click', (e) => {
      const servicio = link.dataset.servicio;
      if (servicio) {
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
     6. BOTONES QUE LLEVAN A AGENDAR
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
      btn.addEventListener('click', () => {
        document.getElementById('citas').scrollIntoView({ behavior: 'smooth' });
      });
    }
  });

  const btnVerPrecios = document.getElementById('btnVerPrecios');
  if (btnVerPrecios) {
    btnVerPrecios.addEventListener('click', () => {
      document.getElementById('precios').scrollIntoView({ behavior: 'smooth' });
    });
  }

  /* ==========================================
     7. FORMATEAR NÚMERO DE TARJETA
     ========================================== */
  const numeroTarjeta = document.getElementById('numeroTarjeta');
  if (numeroTarjeta) {
    numeroTarjeta.addEventListener('input', (e) => {
      let value = e.target.value.replace(/\D/g, '');
      value = value.substring(0, 16);
      const partes = value.match(/.{1,4}/g) || [];
      e.target.value = partes.join(' ');
    });
  }

  /* ==========================================
     8. VALIDACIÓN Y ENVÍO DEL FORMULARIO
     ========================================== */
  const form = document.getElementById('formCita');
  const mensajeConfirmacion = document.getElementById('mensajeConfirmacion');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const nombre = document.getElementById('nombre');
      const telefono = document.getElementById('telefono');
      const email = document.getElementById('email');
      const nombreTarjeta = document.getElementById('nombreTarjeta');
      const numeroTarjeta = document.getElementById('numeroTarjeta');

      let valido = true;

      [nombre, telefono, email, nombreTarjeta, numeroTarjeta].forEach(campo => {
        if (campo) campo.classList.remove('error');
      });

      if (!nombre.value.trim()) {
        nombre.classList.add('error');
        valido = false;
      }
      if (!telefono.value.trim() || telefono.value.trim().length < 8) {
        telefono.classList.add('error');
        valido = false;
      }
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!email.value.trim() || !emailRegex.test(email.value.trim())) {
        email.classList.add('error');
        valido = false;
      }
      if (!servicioSelect.value) {
        servicioSelect.classList.add('error');
        valido = false;
      }
      const metodoPago = document.getElementById('metodoPago').value;
      if (metodoPago === 'Tarjeta de crédito/débito') {
        if (!nombreTarjeta.value.trim()) {
          nombreTarjeta.classList.add('error');
          valido = false;
        }
        const numLimpio = numeroTarjeta.value.replace(/\s/g, '');
        if (numLimpio.length < 16) {
          numeroTarjeta.classList.add('error');
          valido = false;
        }
      }

      if (!valido) {
        alert('Por favor, revisa los campos marcados en rojo.');
        return;
      }

      mensajeConfirmacion.classList.add('show');
      mensajeConfirmacion.scrollIntoView({ behavior: 'smooth', block: 'center' });

      setTimeout(() => {
        form.reset();
        mensajeConfirmacion.classList.remove('show');
        servicioSelect.selectedIndex = 0;
        servicioActual = { nombre: '', precio: 0, duracion: '' };
        generarFechas();
        generarHoras();
        actualizarResumen();
      }, 4000);
    });
  }

  /* ==========================================
     9. INICIALIZAR
     ========================================== */
  generarFechas();
  generarHoras();
  actualizarResumen();

  if (servicioSelect) {
    servicioSelect.addEventListener('change', actualizarResumen);
  }

});