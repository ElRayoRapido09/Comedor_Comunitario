document.addEventListener("DOMContentLoaded", () => {
  // Objeto para almacenar instancias de gráficos
  const appCharts = {
    reservations: null,
    users: null
  };

  // Función para formatear fechas
  function formatDbDate(dateString) {
    if (!dateString) return '--/--/----';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '--/--/----';
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}/${month}/${year}`;
  }

  // Inicializar gráficos
  function initializeCharts() {
    // Gráfico de reservaciones
    const reservationsCtx = document.getElementById('reservations-by-day-chart')?.getContext('2d');
    if (reservationsCtx) {
      if (appCharts.reservations) {
        appCharts.reservations.destroy();
      }
      appCharts.reservations = new Chart(reservationsCtx, {
        type: 'bar',
        data: { 
          labels: [], 
          datasets: [{
            label: 'Reservaciones por día',
            data: [],
            backgroundColor: '#9e1c3f',
            borderColor: '#7a1530',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { 
            y: { 
              beginAtZero: true,
              title: { text: 'Cantidad' }
            },
            x: { 
              title: { text: 'Fecha' }
            }
          }
        }
      });
    }

    // Gráfico de usuarios
    const usersCtx = document.getElementById('users-distribution-chart')?.getContext('2d');
    if (usersCtx) {
      if (appCharts.users) {
        appCharts.users.destroy();
      }
      appCharts.users = new Chart(usersCtx, {
        type: 'pie',
        data: { 
          labels: [], 
          datasets: [{
            data: [],
            backgroundColor: ['#9e1c3f', '#c13c5e', '#c69c6d', '#d8b48e'],
            borderWidth: 1
          }]
        },
        options: { 
          responsive: true, 
          maintainAspectRatio: false,
          plugins: { legend: { position: 'right' } }
        }
      });
    }
  }

  // Actualizar gráfico de reservaciones
  function updateReservationsChart(reservationsData) {
    if (!appCharts.reservations) return;
    
    if (reservationsData?.length > 0) {
      appCharts.reservations.data.labels = reservationsData.map(item => formatDbDate(item.dia));
      appCharts.reservations.data.datasets[0].data = reservationsData.map(item => item.cantidad);
    } else {
      appCharts.reservations.data.labels = ['No hay datos'];
      appCharts.reservations.data.datasets[0].data = [0];
    }
    appCharts.reservations.update();
  }

  // Actualizar gráfico de usuarios
  function updateUsersChart(usersData) {
    if (!appCharts.users) return;
    
    if (usersData?.length > 0) {
      appCharts.users.data.labels = usersData.map(item => {
        switch(item.sexo) {
          case 'masculino': return 'Hombres';
          case 'femenino': return 'Mujeres';
          default: return 'Otros';
        }
      });
      appCharts.users.data.datasets[0].data = usersData.map(item => item.cantidad);
    } else {
      appCharts.users.data.labels = ['No hay datos'];
      appCharts.users.data.datasets[0].data = [1];
    }
    appCharts.users.update();
  }

  // Mostrar notificación
  function showToast(message) {
    const toast = document.getElementById("export-confirmation");
    if (toast) {
      toast.querySelector("span").textContent = message;
      toast.style.display = "block";
      setTimeout(() => { toast.style.display = "none"; }, 3000);
    }
  }

  // Cargar datos del reporte
  function loadReportData(range, startDate, endDate) {
    showLoadingState();
    
    const { start, end } = calculateDateRange(range, startDate, endDate);
    const url = `api.php?action=get_report_data&startDate=${formatDateForAPI(start)}&endDate=${formatDateForAPI(end)}`;
    
    fetch(url)
      .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta de la red');
        return response.json();
      })
      .then(data => {
        if (data.success) {
          updateUI(data.data);
          updateCharts(data.data);
        } else {
          throw new Error(data.message || "Datos no válidos");
        }
      })
      .catch(error => {
        console.error("Error al cargar datos:", error);
        showToast("Error: " + error.message);
      });
  }

  // Funciones auxiliares
  function calculateDateRange(range, customStart, customEnd) {
    const now = new Date();
    let start = new Date(now);
    let end = new Date(now);
    
    now.setHours(0, 0, 0, 0);
    
    if (range === 'custom' && customStart && customEnd) {
      start = new Date(customStart);
      end = new Date(customEnd);
    } else {
      switch(range) {
        case 'week':
          start.setDate(now.getDate() - 6);
          break;
        case 'month':
          start.setMonth(now.getMonth() - 1);
          break;
        case 'quarter':
          start.setMonth(now.getMonth() - 3);
          break;
        case 'year':
          start.setFullYear(now.getFullYear() - 1);
          break;
        default: // Último mes por defecto
          start.setMonth(now.getMonth() - 1);
      }
    }
    
    start.setHours(0, 0, 0, 0);
    end.setHours(23, 59, 59, 999);
    
    return { start, end };
  }

  function formatDateForAPI(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function showLoadingState() {
    document.querySelectorAll(".summary-count").forEach(el => {
      el.textContent = "...";
    });
  }

  function updateUI(data) {
    if (!data) return;
    
    // Actualizar tarjetas de resumen
    const cards = document.querySelectorAll(".summary-card");
    if (cards.length >= 4 && data.summary) {
      cards[0].querySelector(".summary-count").textContent = data.summary.total_beneficiarios ?? "0";
      cards[1].querySelector(".summary-count").textContent = data.summary.total_reservaciones ?? "0";
      cards[2].querySelector(".summary-count").textContent = data.summary.total_porciones ?? "0";
      
      const costo = parseFloat(data.summary.costo_promedio) || 0;
      cards[3].querySelector(".summary-count").textContent = `$${costo.toFixed(2)}`;
    }
    
    // Actualizar tabla de actividad
    updateRecentActivityTable(data.recent_activity);
  }

  function updateRecentActivityTable(activities) {
    const tableBody = document.querySelector(".report-table tbody");
    if (!tableBody) return;
    
    if (activities?.length > 0) {
      tableBody.innerHTML = activities.map(activity => `
        <tr>
          <td>${formatDbDate(activity.fecha)}</td>
          <td>${activity.reservaciones ?? 0}</td>
          <td>${activity.porciones ?? 0}</td>
          <td>${activity.completadas ?? 0}</td>
          <td>${activity.canceladas ?? 0}</td>
          <td>
            <div class="progress-bar">
              <div class="progress" style="width: ${activity.tasa_asistencia ?? 0}%"></div>
              <span>${activity.tasa_asistencia ?? 0}%</span>
            </div>
          </td>
        </tr>
      `).join("");
    } else {
      tableBody.innerHTML = `<tr><td colspan="6">No hay datos de actividad reciente</td></tr>`;
    }
  }

  function updateCharts(data) {
    updateReservationsChart(data.reservations_by_day);
    updateUsersChart(data.users_distribution?.sexo);
  }

  // Configurar event listeners
  function setupEventListeners() {
    // Selector de rango de fechas
    document.getElementById("date-range")?.addEventListener("change", function() {
      if (this.value === "custom") {
        document.getElementById("custom-date-range").style.display = "flex";
      } else {
        document.getElementById("custom-date-range").style.display = "none";
        loadReportData(this.value);
      }
    });
    
    // Fechas personalizadas
    document.getElementById("start-date")?.addEventListener("change", function() {
      if (document.getElementById("date-range").value === "custom") {
        loadReportData('custom', this.value, document.getElementById("end-date").value);
      }
    });
    
    document.getElementById("end-date")?.addEventListener("change", function() {
      if (document.getElementById("date-range").value === "custom") {
        loadReportData('custom', document.getElementById("start-date").value, this.value);
      }
    });
    
    // Botón de actualizar
    document.getElementById("update-report-btn")?.addEventListener("click", function() {
      const range = document.getElementById("date-range").value;
      if (range === "custom") {
        loadReportData('custom', 
          document.getElementById("start-date").value, 
          document.getElementById("end-date").value);
      } else {
        loadReportData(range);
      }
    });
  }

  // Limpiar gráficos al salir
  window.addEventListener('beforeunload', () => {
    if (appCharts.reservations) appCharts.reservations.destroy();
    if (appCharts.users) appCharts.users.destroy();
  });

  // Inicialización
  function init() {
    initializeCharts();
    
    // Establecer fechas por defecto (último mes)
    const defaultStartDate = new Date();
    defaultStartDate.setMonth(defaultStartDate.getMonth() - 1);
    document.getElementById("start-date").valueAsDate = defaultStartDate;
    document.getElementById("end-date").valueAsDate = new Date();
    
    // Cargar datos iniciales
    loadReportData('month');
    setupEventListeners();
  }

  // Iniciar la aplicación
  init();
});