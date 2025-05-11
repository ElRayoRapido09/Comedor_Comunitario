document.addEventListener("DOMContentLoaded", function() {
  console.log("Sistema de reservaciones cargado correctamente");

  // Variables globales
  let dateInput = document.getElementById("reservations-date");
  
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
      toast.textContent = message;
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
                      <button class="action-btn complete-btn" title="Marcar como completada" data-reserva-id="${reserva.id}">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                              <polyline points="22 4 12 14.01 9 11.01"></polyline>
                          </svg>
                      </button>
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

  // Configurar fecha inicial
  const currentDate = new Date();
  document.getElementById("current-date").textContent = formatDate(currentDate);
  
  // Formatear input de fecha
  const year = currentDate.getFullYear();
  const month = String(currentDate.getMonth() + 1).padStart(2, "0");
  const day = String(currentDate.getDate()).padStart(2, "0");
  dateInput.value = `${year}-${month}-${day}`;

  // Navegación de fechas
  document.getElementById("prev-date").addEventListener("click", () => {
      const date = new Date(dateInput.value);
      date.setDate(date.getDate() - 1);
      updateDateInput(date);
  });

  document.getElementById("next-date").addEventListener("click", () => {
      const date = new Date(dateInput.value);
      date.setDate(date.getDate() + 1);
      updateDateInput(date);
  });

  function updateDateInput(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      dateInput.value = `${year}-${month}-${day}`;
      loadReservations(dateInput.value);
  }

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
      
      const rows = document.querySelectorAll(".reservations-table tbody tr");
      
      rows.forEach(row => {
          const statusCell = row.querySelector(".status-badge").textContent.toLowerCase();
          const timeCell = row.querySelector("td:nth-child(3)").textContent;
          const nameCell = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
          const codeCell = row.querySelector("td:nth-child(1)").textContent.toLowerCase();
          
          const statusMatch = status === "all" || statusCell === status;
          const timeMatch = time === "all" || timeCell === time;
          const searchMatch = search === "" || nameCell.includes(search) || codeCell.includes(search);
          
          row.style.display = (statusMatch && timeMatch && searchMatch) ? "" : "none";
      });
  }

  // Limpiar filtros
  document.getElementById("clear-filters").addEventListener("click", () => {
      document.getElementById("status-filter").value = "all";
      document.getElementById("time-filter").value = "all";
      document.getElementById("search-input").value = "";
      loadReservations(dateInput.value);
  });

  // Exportar
  document.getElementById("export-btn").addEventListener("click", exportReservations);

  function exportReservations() {
      const fecha = dateInput.value;
      const estado = document.getElementById("status-filter").value;
      const hora = document.getElementById("time-filter").value;
      
      window.open(`exportar_reservaciones.php?fecha=${fecha}&estado=${estado}&hora=${hora}`, '_blank');
  }

  // Imprimir
  document.getElementById("print-btn").addEventListener("click", () => {
      window.print();
  });

  // Cargar reservaciones iniciales
  loadReservations(dateInput.value);
});