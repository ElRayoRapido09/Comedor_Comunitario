document.addEventListener("DOMContentLoaded", () => {
  // Configuración inicial
  let charts = {
    reservations: null,
    users: null,
    sales: null
  };
  
  // Función para formatear fechas de la base de datos (con ajuste de zona horaria)
  function formatDbDate(dateString) {
    if (!dateString) return '--/--/----';
    
    if (typeof dateString === 'string' && dateString.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
      return dateString;
    }
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '--/--/----';
    
    // Ajuste específico para fechas de la base de datos
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}/${month}/${year}`;
  }
  
  // Función para formatear fechas generales (sin ajuste de zona horaria)
  function formatGeneralDate(dateInput) {
    if (!dateInput) return '--/--/----';
    
    if (typeof dateInput === 'string' && dateInput.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
      return dateInput;
    }
    
    const date = dateInput instanceof Date ? dateInput : new Date(dateInput);
    if (isNaN(date.getTime())) return '--/--/----';
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}/${month}/${year}`;
  }

  // Inicializar gráficos
  function initCharts() {
    const reservationsCtx = document.getElementById('reservations-by-day-chart')?.getContext('2d');
    const usersCtx = document.getElementById('users-distribution-chart')?.getContext('2d');
    const salesCtx = document.getElementById('sales-period-chart')?.getContext('2d');
    
    if (reservationsCtx) {
      charts.reservations = new Chart(reservationsCtx, {
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
              title: {
                display: true,
                text: 'Cantidad'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Fecha'
              }
            }
          }
        }
      });
    }
    
    if (usersCtx) {
      charts.users = new Chart(usersCtx, {
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
          plugins: {
            legend: {
              position: 'right'
            }
          }
        }
      });
    }
    
    if (salesCtx) {
      charts.sales = new Chart(salesCtx, {
        type: 'bar',
        data: { 
          labels: [], 
          datasets: [{
            label: 'Ventas',
            data: [],
            backgroundColor: '#c69c6d',
            borderColor: '#a67c4e',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { 
              beginAtZero: true, 
              title: { 
                display: true, 
                text: 'Monto ($)' 
              } 
            },
            x: { 
              title: { 
                display: true, 
                text: 'Período' 
              } 
            }
          }
        }
      });
    }
  }
  
  // Cargar datos iniciales
  function loadInitialData() {
    initCharts();
    
    // Configurar fechas por defecto
    const defaultStartDate = new Date();
    defaultStartDate.setMonth(defaultStartDate.getMonth() - 1);
    document.getElementById("start-date").valueAsDate = defaultStartDate;
    document.getElementById("end-date").valueAsDate = new Date();
    
    loadReportData('month');
    
    // Inicializar gráfico de ventas con datos vacíos
    if (charts.sales) {
      charts.sales.data.labels = ['Seleccione un período'];
      charts.sales.data.datasets[0].data = [0];
      charts.sales.update();
    }
  }
  
  // Configurar event listeners
  function setupEventListeners() {
    document.getElementById("date-range").addEventListener("change", function() {
      if (this.value === "custom") {
        document.getElementById("custom-date-range").style.display = "flex";
      } else {
        document.getElementById("custom-date-range").style.display = "none";
        loadReportData(this.value);
      }
    });
    
    document.getElementById("start-date").addEventListener("change", updateCustomDateRange);
    document.getElementById("end-date").addEventListener("change", updateCustomDateRange);
    
    document.getElementById("update-report-btn").addEventListener("click", handleUpdateClick);
    
    document.querySelectorAll(".period-btn").forEach(btn => {
      btn.addEventListener("click", function() {
        document.querySelectorAll(".period-btn").forEach(b => b.classList.remove("active"));
        this.classList.add("active");
        updateSalesChart(this.getAttribute("data-period"));
      });
    });
  }
  
  function updateCustomDateRange() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    
    if (startDate && endDate) {
      loadReportData('custom', startDate, endDate);
    }
  }
  
  function handleUpdateClick() {
    const range = document.getElementById("date-range").value;
    
    if (range === "custom") {
      const startDate = document.getElementById("start-date").value;
      const endDate = document.getElementById("end-date").value;
      loadReportData('custom', startDate, endDate);
    } else {
      loadReportData(range);
    }
  }
  
  // Función principal para cargar datos
  function loadReportData(range, startDate, endDate) {
    showLoadingState();
    
    const { start, end } = calculateDateRange(range, startDate, endDate);
    const url = buildApiUrl(start, end);
    
    fetch(url)
      .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta de la red');
        return response.json();
      })
      .then(data => {
        if (data.success) {
          updateUI(data.data);
          updateCharts(data.data);
          // Activar el botón de día por defecto
          const dayBtn = document.querySelector('.period-btn[data-period="day"]');
          if (dayBtn) {
            dayBtn.classList.add("active");
            updateSalesChart('day');
          }
        } else {
          throw new Error(data.message || "Datos no válidos");
        }
      })
      .catch(error => {
        console.error("Error al cargar datos:", error);
        showToast("Error: " + error.message);
      });
  }
  
  function calculateDateRange(range, startDate, endDate) {
    const now = new Date();
    let start, end;
    
    now.setHours(0, 0, 0, 0);
    
    switch(range) {
      case 'week':
        start = new Date(now);
        start.setDate(now.getDate() - 6);
        end = new Date(now);
        break;
      case 'month':
        start = new Date(now);
        start.setMonth(now.getMonth() - 1);
        start.setDate(1);
        end = new Date(now);
        break;
      case 'quarter':
        start = new Date(now);
        start.setMonth(now.getMonth() - 3);
        end = new Date(now);
        break;
      case 'year':
        start = new Date(now);
        start.setFullYear(now.getFullYear() - 1);
        end = new Date(now);
        break;
      case 'custom':
        start = new Date(startDate);
        end = new Date(endDate);
        break;
      default:
        start = new Date(now);
        start.setMonth(now.getMonth() - 1);
        end = new Date(now);
    }
    
    start.setHours(0, 0, 0, 0);
    end.setHours(23, 59, 59, 999);
    
    return { start, end };
  }

  function buildApiUrl(start, end) {
    const formatDateForAPI = date => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };
    
    return `api.php?action=get_report_data&startDate=${formatDateForAPI(start)}&endDate=${formatDateForAPI(end)}`;
  }
  
  function showLoadingState() {
    document.querySelectorAll(".summary-count").forEach(el => {
      el.textContent = "...";
    });
  }
  
  function updateUI(data) {
    if (!data) {
      console.error("No se recibieron datos para actualizar la UI");
      return;
    }
    
    updateSummaryCards(data.summary);
    updateRecentActivityTable(data.recent_activity);
  }
  
  function updateSummaryCards(summary) {
    const cards = document.querySelectorAll(".summary-card");
    
    if (cards.length >= 4 && summary) {
      cards[0].querySelector(".summary-count").textContent = summary.total_beneficiarios ?? "0";
      cards[1].querySelector(".summary-count").textContent = summary.total_reservaciones ?? "0";
      cards[2].querySelector(".summary-count").textContent = summary.total_porciones ?? "0";
      
      const costo = parseFloat(summary.costo_promedio) || 0;
      cards[3].querySelector(".summary-count").textContent = `$${costo.toFixed(2)}`;
    }
  }
  
  function updateRecentActivityTable(activities) {
    const tableBody = document.querySelector(".report-table tbody");
    if (!tableBody) return;
    
    if (activities && activities.length > 0) {
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
      tableBody.innerHTML = `<tr><td colspan="6" style="text-align: center;">No hay datos de actividad reciente</td></tr>`;
    }
  }
  
  function updateCharts(data) {
    updateReservationsChart(data.reservations_by_day);
    updateUsersChart(data.users_distribution?.sexo);
  }
  
  function updateReservationsChart(reservationsData) {
    if (!charts.reservations) return;
    
    if (reservationsData && reservationsData.length > 0) {
      charts.reservations.data.labels = reservationsData.map(item => formatDbDate(item.dia));
      charts.reservations.data.datasets[0].data = reservationsData.map(item => item.cantidad);
    } else {
      charts.reservations.data.labels = ['No hay datos'];
      charts.reservations.data.datasets[0].data = [0];
    }
    
    charts.reservations.update();
  }
  
  function updateUsersChart(usersData) {
    if (!charts.users) return;
    
    if (usersData && usersData.length > 0) {
      charts.users.data.labels = usersData.map(item => {
        switch(item.sexo) {
          case 'masculino': return 'Hombres';
          case 'femenino': return 'Mujeres';
          default: return 'Otros';
        }
      });
      charts.users.data.datasets[0].data = usersData.map(item => item.cantidad);
    } else {
      charts.users.data.labels = ['No hay datos'];
      charts.users.data.datasets[0].data = [1];
    }
    
    charts.users.update();
  }
  
  function updateSalesChart(period) {
    if (!charts.sales) return;
    
    const salesData = getSalesDataForPeriod(period);
    
    if (salesData.labels.length === 0) {
      charts.sales.data.labels = ['No hay datos disponibles'];
      charts.sales.data.datasets[0].data = [0];
    } else {
      charts.sales.data.labels = salesData.labels.map(label => formatGeneralDate(label));
      charts.sales.data.datasets[0].data = salesData.values;
    }
    
    let periodLabel = '';
    switch(period) {
      case 'day': periodLabel = 'Día'; break;
      case 'week': periodLabel = 'Semana'; break;
      case 'month': periodLabel = 'Mes'; break;
      default: periodLabel = 'Período';
    }
    
    charts.sales.data.datasets[0].label = `Ventas por ${periodLabel}`;
    charts.sales.options.scales.x.title.text = periodLabel;
    
    charts.sales.update();
  }
  
  function getSalesDataForPeriod(period) {
    // Obtener datos del gráfico de reservaciones como base
    const reservationsData = charts.reservations?.data;
    
    if (!reservationsData || !reservationsData.labels || reservationsData.labels.length === 0) {
      return { labels: [], values: [] };
    }
    
    // Simular datos de ventas (10 pesos por cada reservación)
    const values = reservationsData.datasets[0].data.map(count => count * 10);
    
    return {
      labels: [...reservationsData.labels], // Copia de las etiquetas
      values: values
    };
  }
  
  function showToast(message) {
    const toast = document.getElementById("export-confirmation");
    if (toast) {
      toast.querySelector("span").textContent = message;
      toast.style.display = "block";
      setTimeout(() => { toast.style.display = "none"; }, 3000);
    }
  }
  
  // Iniciar la aplicación
  loadInitialData();
  setupEventListeners();
});