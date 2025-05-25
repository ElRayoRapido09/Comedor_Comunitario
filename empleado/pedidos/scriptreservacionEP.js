document.addEventListener("DOMContentLoaded", function() {
  console.log("Sistema de reservaciones cargado correctamente");

  // Variables globales
  let dateInput = document.getElementById("reservations-date");
  
  // ----------------------------
  // INICIALIZACIÓN Y CONFIGURACIÓN DE FECHAS
  // ----------------------------
    // Establecer fecha actual por defecto si no está configurada
  function initializeDateInput() {
    if (!dateInput.value) {
      const today = new Date();
      // Usar fecha local para evitar problemas de zona horaria
      const todayStr = today.getFullYear() + '-' + 
                      String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(today.getDate()).padStart(2, '0');
      dateInput.value = todayStr;
      console.log('Fecha inicializada:', todayStr);
    } else {
      console.log('Fecha ya configurada:', dateInput.value);
    }
  }
  // Navegación de fechas
  function navigateDate(direction) {
    console.log('Navegando fecha:', direction, 'desde:', dateInput.value);
    
    // Crear fecha usando el constructor de Date con componentes separados para evitar problemas de zona horaria
    const dateStr = dateInput.value;
    const [year, month, day] = dateStr.split('-').map(num => parseInt(num, 10));
    const currentDate = new Date(year, month - 1, day); // month - 1 porque Date usa meses base 0
    
    console.log('Fecha actual creada:', currentDate);
    
    // Verificar que la fecha sea válida
    if (isNaN(currentDate.getTime())) {
      console.error('Fecha inválida:', dateInput.value);
      initializeDateInput();
      return;
    }
    
    // Navegar la fecha
    if (direction === 'prev') {
      currentDate.setDate(currentDate.getDate() - 1);
      console.log('Retrocediendo un día a:', currentDate);
    } else if (direction === 'next') {
      currentDate.setDate(currentDate.getDate() + 1);
      console.log('Avanzando un día a:', currentDate);
    }
    
    // Formatear la nueva fecha
    const newDateStr = currentDate.getFullYear() + '-' + 
                      String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(currentDate.getDate()).padStart(2, '0');
    
    console.log('Nueva fecha formateada:', newDateStr);
    
    // Actualizar el input
    dateInput.value = newDateStr;
    
    // Mostrar mensaje de carga
    const loadingMessage = `Cargando reservaciones para ${formatDate(currentDate)}...`;
    showToast(loadingMessage, 'info');
      // Cargar las reservaciones
    loadReservations(newDateStr);
  }
  
  // Event listeners para navegación de fechas con manejo de errores
  const prevDateBtn = document.getElementById("prev-date");
  const nextDateBtn = document.getElementById("next-date");
  
  console.log('Elementos encontrados:', {
    prevDateBtn: !!prevDateBtn,
    nextDateBtn: !!nextDateBtn,
    dateInput: !!dateInput
  });
  
  // Variable para prevenir múltiples clics rápidos
  let isNavigating = false;
  
  if (prevDateBtn) {
    prevDateBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (isNavigating) {
        console.log('Navegación en proceso, ignorando clic');
        return;
      }
      isNavigating = true;
      
      // Deshabilitar botón temporalmente
      prevDateBtn.disabled = true;
      
      navigateDate('prev');
      
      // Rehabilitar después de un breve delay
      setTimeout(() => {
        isNavigating = false;
        prevDateBtn.disabled = false;
      }, 500);
    });
  } else {
    console.warn('Botón prev-date no encontrado');
  }

  if (nextDateBtn) {
    nextDateBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (isNavigating) {
        console.log('Navegación en proceso, ignorando clic');
        return;
      }
      isNavigating = true;
      
      // Deshabilitar botón temporalmente
      nextDateBtn.disabled = true;
      
      navigateDate('next');
      
      // Rehabilitar después de un breve delay
      setTimeout(() => {
        isNavigating = false;
        nextDateBtn.disabled = false;
      }, 500);
    });
  } else {
    console.warn('Botón next-date no encontrado');
  }

  // Event listener para cambio directo de fecha
  dateInput.addEventListener("change", function() {
    console.log('Fecha cambiada a:', this.value);
    loadReservations(this.value);
  });
  // Agregar teclas de navegación con teclado
  document.addEventListener("keydown", function(event) {
    // Solo si no hay un modal abierto o input enfocado
    if (document.querySelector('.modal[style*="display: block"]') || 
        document.activeElement.tagName === 'INPUT' || 
        document.activeElement.tagName === 'TEXTAREA' ||
        isNavigating) {
      return;
    }
    
    if (event.key === 'ArrowLeft') {
      event.preventDefault();
      if (!isNavigating) {
        isNavigating = true;
        navigateDate('prev');
        setTimeout(() => { isNavigating = false; }, 500);
      }
    } else if (event.key === 'ArrowRight') {
      event.preventDefault();
      if (!isNavigating) {
        isNavigating = true;
        navigateDate('next');
        setTimeout(() => { isNavigating = false; }, 500);
      }
    }
  });

  // Inicializar fecha
  initializeDateInput();

  // ----------------------------
  // FUNCIONES DE UTILIDAD
  // ----------------------------

  function formatDate(date) {
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
  }

  function formatDateTime(date) {
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const year = date.getFullYear();
      const hours = String(date.getHours()).padStart(2, "0");
      const minutes = String(date.getMinutes()).padStart(2, "0");
      return `${day}/${month}/${year} ${hours}:${minutes}`;
  }
  function showToast(message, type = 'success') {
      const toast = document.getElementById('status-confirmation');
      if (!toast) {
          console.warn('Toast element not found');
          return;
      }
      
      // Limpiar clases anteriores
      toast.className = 'toast';
      
      const toastContent = toast.querySelector('.toast-content span');
      if (toastContent) {
          toastContent.textContent = message;
      } else {
          toast.textContent = message;
      }
      
      toast.className = 'toast ' + type;
      toast.style.display = 'block';
      
      setTimeout(() => {
          toast.style.display = 'none';
      }, 3000);
  }

  // ----------------------------
  // FUNCIONES DE ACTUALIZACIÓN DE UI
  // ----------------------------

  function updateReservationsTable(reservaciones) {
      const tbody = document.querySelector(".reservations-table tbody");
      tbody.innerHTML = '';

      if (!reservaciones || reservaciones.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No hay reservaciones para esta fecha</td></tr>';
          return;
      }

      reservaciones.forEach(reserva => {
          const row = document.createElement('tr');
          
          // Mapear estado a clases CSS
          let estadoClass = '';
          let estadoText = '';
          switch(reserva.estado) {
              case 'pendiente':
                  estadoClass = 'pending';
                  estadoText = 'Pendiente';
                  break;
              case 'completada':
                  estadoClass = 'completed';
                  estadoText = 'Completada';
                  break;
              case 'cancelada':
                  estadoClass = 'cancelled';
                  estadoText = 'Cancelada';
                  break;
          }

          row.innerHTML = `
              <td>${reserva.codigo_reserva}</td>
              <td>${reserva.nombre}</td>
              <td>${reserva.hora}</td>
              <td>${reserva.porciones}</td>
              <td>
                  <button class="view-menu-btn" data-reserva-id="${reserva.id}">Ver Menú</button>
              </td>
              <td>
                  <span class="status-badge ${estadoClass}">${estadoText}</span>
              </td>
              <td>
                  <div class="action-buttons">
                      <button class="action-btn view-btn" title="Ver detalles" data-reserva-id="${reserva.id}">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                              <circle cx="12" cy="12" r="3"></circle>
                          </svg>
                      </button>
                      ${reserva.estado === 'pendiente' ? `
                      
                      <button class="action-btn cancel-btn" title="Cancelar reservación" data-reserva-id="${reserva.id}">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <circle cx="12" cy="12" r="10"></circle>
                              <line x1="15" y1="9" x2="9" y2="15"></line>
                              <line x1="9" y1="9" x2="15" y2="15"></line>
                          </svg>
                      </button>
                      ` : `
                      <button class="action-btn ${reserva.estado === 'completada' ? 'print-btn' : 'restore-btn'}" 
                          title="${reserva.estado === 'completada' ? 'Imprimir comprobante' : 'Restaurar reservación'}" 
                          data-reserva-id="${reserva.id}">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              ${reserva.estado === 'completada' ? `
                              <polyline points="6 9 6 2 18 2 18 9"></polyline>
                              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                              <rect x="6" y="14" width="12" height="8"></rect>
                              ` : `
                              <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                              <path d="M3 3v5h5"></path>
                              `}
                          </svg>
                      </button>
                      `}
                  </div>
              </td>
          `;
          
          tbody.appendChild(row);
      });

      // Añadir event listeners a los nuevos botones
      addEventListeners();
  }

  function updateSummaryCounts(reservaciones) {
      let pendingCount = 0;
      let completedCount = 0;
      let cancelledCount = 0;
      let totalPortions = 0;

      reservaciones.forEach(reserva => {
          if (reserva.estado === 'pendiente') pendingCount++;
          else if (reserva.estado === 'completada') completedCount++;
          else if (reserva.estado === 'cancelada') cancelledCount++;

          if (reserva.estado !== 'cancelada|') {
              totalPortions += parseInt(reserva.porciones);
          }
      });

      // Actualizar las tarjetas de resumen
      document.querySelector(".summary-card:nth-child(1) .summary-count").textContent = pendingCount;
      document.querySelector(".summary-card:nth-child(2) .summary-count").textContent = completedCount;
      document.querySelector(".summary-card:nth-child(3) .summary-count").textContent = cancelledCount;
      document.querySelector(".summary-card:nth-child(4) .summary-count").textContent = totalPortions;
  }

  function updatePagination(totalPages, currentPage) {
      const paginationContainer = document.querySelector('.pagination-pages');
      paginationContainer.innerHTML = '';
      
      for (let i = 1; i <= totalPages; i++) {
          const pageBtn = document.createElement('button');
          pageBtn.className = `pagination-page ${i == currentPage ? 'active' : ''}`;
          pageBtn.textContent = i;
          pageBtn.addEventListener('click', () => {
              loadReservations(dateInput.value, i);
          });
          paginationContainer.appendChild(pageBtn);
      }
  }

  // ----------------------------
  // FUNCIONES PRINCIPALES
  // ----------------------------

  function loadReservations(date, page = 1) {
      console.log(`Cargando reservaciones para ${date}, página ${page}`);
      
      fetch(`obtener_reservaciones.php?fecha=${date}&page=${page}`)
          .then(response => {
              console.log("Respuesta recibida del servidor");
              if (!response.ok) {
                  throw new Error(`Error HTTP! estado: ${response.status}`);
              }
              return response.json();
          })
          .then(data => {
              console.log("Datos recibidos:", data);
              if (data.success) {
                  updateReservationsTable(data.data);
                  updateSummaryCounts(data.data);
                  if (data.totalPages) {
                      updatePagination(data.totalPages, page);
                  }
                  showToast(`${data.data.length} reservaciones cargadas`, 'success');
              } else {
                  throw new Error(data.message || 'Error al obtener datos');
              }
          })
          .catch(error => {
              console.error('Error al cargar reservaciones:', error);
              showToast(error.message, 'error');
              document.querySelector(".reservations-table tbody").innerHTML = 
                  '<tr><td colspan="7" style="text-align:center;">Error al cargar reservaciones</td></tr>';
          });
  }

  function updateReservationStatus(reservaId, nuevoEstado, fromModal = false) {
      const confirmMessage = nuevoEstado === 'completada' ? 
          '¿Está seguro que desea marcar esta reservación como completada?' :
          nuevoEstado === 'cancelada' ? 
          '¿Está seguro que desea cancelar esta reservación?' :
          '¿Está seguro que desea restaurar esta reservación?';
      
      if (!confirm(confirmMessage)) return;

      const formData = new FormData();
      formData.append('id', reservaId);
      formData.append('estado', nuevoEstado);

      fetch('actualizar_estado_reserva.php', {
          method: 'POST',
          body: formData
      })
      .then(response => {
          if (!response.ok) {
              throw new Error(`Error HTTP! estado: ${response.status}`);
          }
          return response.json();
      })
      .then(data => {
          if (data.success) {
              showToast(`Reservación ${nuevoEstado} correctamente`);
              loadReservations(dateInput.value);
              
              if (fromModal) {
                  document.getElementById("reservation-details-modal").style.display = "none";
                  document.body.style.overflow = "auto";
              }
          } else {
              throw new Error(data.message || 'No se pudo actualizar el estado');
          }
      })
      .catch(error => {
          console.error('Error al actualizar estado:', error);
          showToast(error.message, 'error');
      });
  }

  function showReservationDetails(reservaId) {
      fetch(`obtener_detalle_reserva.php?id=${reservaId}`)
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  const modal = document.getElementById('reservation-details-modal');
                  
                  // Actualizar header
                  modal.querySelector('.code-value').textContent = data.reserva.codigo_reserva;
                  modal.querySelector('.status-badge').className = 'status-badge ' + data.reserva.estado;
                  modal.querySelector('.status-badge').textContent = data.reserva.estado.charAt(0).toUpperCase() + data.reserva.estado.slice(1);
                  
                  // Actualizar información
                  modal.querySelector('.detail-item:nth-child(1) .detail-value').textContent = data.reserva.nombre;
                  modal.querySelector('.detail-item:nth-child(2) .detail-value').textContent = data.reserva.telefono || 'N/A';
                  modal.querySelector('.detail-item:nth-child(3) .detail-value').textContent = data.reserva.direccion || 'N/A';
                  
                  // Actualizar detalles de reservación
                  modal.querySelector('.detail-item:nth-child(4) .detail-value').textContent = formatDate(new Date(data.reserva.fecha));
                  modal.querySelector('.detail-item:nth-child(5) .detail-value').textContent = data.reserva.hora;
                  modal.querySelector('.detail-item:nth-child(6) .detail-value').textContent = data.reserva.porciones;
                  modal.querySelector('.detail-item:nth-child(7) .detail-value').textContent = formatDateTime(new Date(data.reserva.fecha_registro));
                  
                  // Actualizar menú seleccionado
                  modal.querySelector('.menu-selection-item:nth-child(1) .menu-item-name').textContent = data.platillos.primer_tiempo || 'N/A';
                  modal.querySelector('.menu-selection-item:nth-child(2) .menu-item-name').textContent = data.platillos.plato_principal || 'N/A';
                  modal.querySelector('.menu-selection-item:nth-child(3) .menu-item-name').textContent = data.platillos.bebida || 'N/A';
                  
                  // Actualizar notas
                  modal.querySelector('.detail-notes').textContent = data.reserva.notas || 'No hay notas adicionales';
                  
                  // Actualizar botón de acción
                  const completeBtn = modal.querySelector('.complete-action');
                  if (data.reserva.estado === 'pendiente') {
                      completeBtn.innerHTML = `
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                              <polyline points="22 4 12 14.01 9 11.01"></polyline>
                          </svg>
                          Marcar como Completada
                      `;
                      completeBtn.className = 'primary-btn complete-action';
                      completeBtn.onclick = () => updateReservationStatus(reservaId, 'completada', true);
                  } else {
                      completeBtn.innerHTML = `
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <polyline points="6 9 6 2 18 2 18 9"></polyline>
                              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                              <rect x="6" y="14" width="12" height="8"></rect>
                          </svg>
                          Imprimir Comprobante
                      `;
                      completeBtn.className = 'primary-btn';
                      completeBtn.onclick = () => printReservation(reservaId);
                  }
                  
                  // Mostrar modal
                  modal.style.display = "flex";
                  document.body.style.overflow = "hidden";
              } else {
                  throw new Error(data.message || 'Error al cargar detalles');
              }
          })
          .catch(error => {
              console.error('Error:', error);
              showToast('Error al cargar detalles: ' + error.message, 'error');
          });
  }

  // ----------------------------
  // MANEJADORES DE EVENTOS
  // ----------------------------

  function addEventListeners() {
      // Botones de vista
      document.querySelectorAll('.view-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              const reservaId = this.dataset.reservaId;
              showReservationDetails(reservaId);
          });
      });

      // Botones de completar
      document.querySelectorAll('.complete-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              const reservaId = this.dataset.reservaId;
              updateReservationStatus(reservaId, 'completada');
          });
      });

      // Botones de cancelar
      document.querySelectorAll('.cancel-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              const reservaId = this.dataset.reservaId;
              updateReservationStatus(reservaId, 'cancelada');
          });
      });

      // Botones de restaurar
      document.querySelectorAll('.restore-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              const reservaId = this.dataset.reservaId;
              updateReservationStatus(reservaId, 'pendiente');
          });
      });

      // Botones de menú
      document.querySelectorAll('.view-menu-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              const reservaId = this.dataset.reservaId;
              showMenuDetails(reservaId);
          });
      });
  }

  // ----------------------------
  // INICIALIZACIÓN
  // ----------------------------
  // Inicialización ya se hace en initializeDateInput() arriba
  // Los event listeners de navegación ya están configurados arriba con debouncing

  // Cambio de fecha
  dateInput.addEventListener("change", () => {
      loadReservations(dateInput.value);
  });

  // Filtros
  document.getElementById("status-filter").addEventListener("change", applyFilters);
  document.getElementById("time-filter").addEventListener("change", applyFilters);
  document.getElementById("search-btn").addEventListener("click", applyFilters);
  document.getElementById("search-input").addEventListener("keypress", (e) => {
      if (e.key === "Enter") applyFilters();
  });
  function applyFilters() {
      const status = document.getElementById("status-filter").value;
      const time = document.getElementById("time-filter").value;
      const search = document.getElementById("search-input").value.toLowerCase();
      
      console.log('Aplicando filtros:', { status, time, search });
      
      const rows = document.querySelectorAll(".reservations-table tbody tr");
      let visibleCount = 0;
      
      rows.forEach(row => {
          // Skip the "no data" row
          if (row.querySelector('td[colspan]')) {
              return;
          }
          
          const statusCell = row.querySelector(".status-badge").textContent.toLowerCase();
          const timeCell = row.querySelector("td:nth-child(3)").textContent;
          const nameCell = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
          const codeCell = row.querySelector("td:nth-child(1)").textContent.toLowerCase();
          
          // Map English filter values to Spanish status text
          let statusMatch = false;
          if (status === "all") {
              statusMatch = true;
          } else if (status === "pending" && statusCell === "pendiente") {
              statusMatch = true;
          } else if (status === "completed" && statusCell === "completada") {
              statusMatch = true;
          } else if (status === "cancelled" && statusCell === "cancelada") {
              statusMatch = true;
          }
          
          const timeMatch = time === "all" || timeCell === time;
          const searchMatch = search === "" || nameCell.includes(search) || codeCell.includes(search);
          
          const shouldShow = statusMatch && timeMatch && searchMatch;
          row.style.display = shouldShow ? "" : "none";
          
          if (shouldShow) {
              visibleCount++;
          }
          
          console.log('Fila procesada:', {
              codigo: codeCell,
              nombre: nameCell,
              estado: statusCell,
              statusMatch,
              timeMatch,
              searchMatch,
              shouldShow
          });
      });
      
      console.log(`Filtros aplicados: ${visibleCount} reservaciones visibles`);
      
      // Show message if no results
      if (visibleCount === 0 && rows.length > 0) {
          showToast('No se encontraron reservaciones con los filtros aplicados', 'info');
      }
  }

  // Limpiar filtros
  document.getElementById("clear-filters").addEventListener("click", () => {
      document.getElementById("status-filter").value = "all";
      document.getElementById("time-filter").value = "all";
      document.getElementById("search-input").value = "";
      loadReservations(dateInput.value);
  });



  function exportReservations() {
      const fecha = dateInput.value;
      const estado = document.getElementById("status-filter").value;
      const hora = document.getElementById("time-filter").value;
      
      window.open(`exportar_reservaciones.php?fecha=${fecha}&estado=${estado}&hora=${hora}`, '_blank');
  }
  // Imprimir
  document.getElementById("print-btn")?.addEventListener("click", () => {
      window.print();
  });
  // Cargar reservaciones iniciales
  console.log('Iniciando carga de reservaciones iniciales con fecha:', dateInput.value);
  loadReservations(dateInput.value);
});