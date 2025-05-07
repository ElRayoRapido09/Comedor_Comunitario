document.addEventListener("DOMContentLoaded", () => {
  // Configuración inicial
  const currentDate = new Date();
  document.getElementById("current-date").textContent = formatDate(currentDate);
  
  // Inicializar gráficos
  let charts = {
    reservations: null,
    users: null,
    sales: null
  };
  
  initCharts();
  
  // Cargar datos iniciales
  loadReportData('month');
  
  // Manejo de pestañas
  const tabBtns = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");
  
  tabBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const tabId = btn.getAttribute("data-tab");
      
      tabBtns.forEach((b) => b.classList.remove("active"));
      tabContents.forEach((c) => c.classList.remove("active"));
      
      btn.classList.add("active");
      document.getElementById(`${tabId}-tab`).classList.add("active");
    });
  });
  
  // Selector de rango de fechas
  const dateRangeSelect = document.getElementById("date-range");
  const customDateRange = document.getElementById("custom-date-range");
  const startDateInput = document.getElementById("start-date");
  const endDateInput = document.getElementById("end-date");
  
  // Configurar fechas por defecto
  const defaultStartDate = new Date();
  defaultStartDate.setMonth(defaultStartDate.getMonth() - 1);
  startDateInput.valueAsDate = defaultStartDate;
  endDateInput.valueAsDate = new Date();
  
  dateRangeSelect.addEventListener("change", () => {
    if (dateRangeSelect.value === "custom") {
      customDateRange.style.display = "flex";
    } else {
      customDateRange.style.display = "none";
      loadReportData(dateRangeSelect.value);
    }
  });
  
  // Manejar cambios en fechas personalizadas
  startDateInput.addEventListener("change", updateCustomDateRange);
  endDateInput.addEventListener("change", updateCustomDateRange);
  
  function updateCustomDateRange() {
    if (startDateInput.value && endDateInput.value) {
      loadReportData('custom', startDateInput.value, endDateInput.value);
    }
  }
  
  // Botón actualizar
  const updateReportBtn = document.getElementById("update-report-btn");
  updateReportBtn.addEventListener("click", () => {
    if (dateRangeSelect.value === "custom") {
      loadReportData('custom', startDateInput.value, endDateInput.value);
    } else {
      loadReportData(dateRangeSelect.value);
    }
  });
  
  // Botón imprimir
  const printReportBtn = document.getElementById("print-report-btn");
  printReportBtn.addEventListener("click", () => {
    window.print();
  });
  
  // Exportar reporte
  const exportReportBtn = document.getElementById("export-report-btn");
  const exportModal = document.getElementById("export-modal");
  const closeExportModal = exportModal.querySelector(".close-modal");
  const cancelExportBtn = document.getElementById("cancel-export");
  const exportForm = document.getElementById("export-form");
  
  exportReportBtn.addEventListener("click", () => {
    exportModal.style.display = "flex";
    document.body.style.overflow = "hidden";
  });
  
  closeExportModal.addEventListener("click", () => {
    exportModal.style.display = "none";
    document.body.style.overflow = "auto";
  });
  
  cancelExportBtn.addEventListener("click", () => {
    exportModal.style.display = "none";
    document.body.style.overflow = "auto";
  });
  
  exportForm.addEventListener("submit", (e) => {
    e.preventDefault();
    
    const format = document.getElementById("export-format").value;
    const fileName = document.getElementById("export-name").value || "Reporte_Comedor";
    
    // Simular exportación
    showToast("Reporte exportado correctamente");
    exportModal.style.display = "none";
    document.body.style.overflow = "auto";
  });
  
  // Selector de período para gráficos
  const periodBtns = document.querySelectorAll(".period-btn");
  periodBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      periodBtns.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      updateSalesChart(btn.getAttribute("data-period"));
    });
  });
  
  // Función principal para cargar datos
  function loadReportData(range, startDate, endDate) {
    let url = 'api.php?action=get_report_data';
    
    // Configurar fechas según el rango seleccionado
    const now = new Date();
    let start, end;
    
    switch(range) {
      case 'week':
        start = new Date();
        start.setDate(start.getDate() - 7);
        end = new Date();
        break;
      case 'month':
        start = new Date();
        start.setMonth(start.getMonth() - 1);
        end = new Date();
        break;
      case 'quarter':
        start = new Date();
        start.setMonth(start.getMonth() - 3);
        end = new Date();
        break;
      case 'year':
        start = new Date();
        start.setFullYear(start.getFullYear() - 1);
        end = new Date();
        break;
      case 'custom':
        start = new Date(startDate);
        end = new Date(endDate);
        break;
      default:
        start = new Date();
        start.setMonth(start.getMonth() - 1);
        end = new Date();
    }
    
    // Formatear fechas para la API
    const formatDateForAPI = (date) => date.toISOString().split('T')[0];
    
    url += `&startDate=${formatDateForAPI(start)}&endDate=${formatDateForAPI(end)}`;
    
    console.log("Cargando datos desde:", url);
    
    fetch(url)
      .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta de la red');
        return response.json();
      })
      .then(data => {
        console.log("Datos recibidos:", data);
        if (data.success) {
          updateReportUI(data.data);
          updateCharts(data.data);
          updateSalesChart('day');
        } else {
          showToast("Error: " + (data.message || "Datos no válidos"));
        }
      })
      .catch(error => {
        console.error("Error al cargar datos:", error);
        showToast("Error de conexión con el servidor");
      });
  }
  
  // Actualizar la interfaz con los datos
  function updateReportUI(data) {
    if (!data) {
      console.error("No se recibieron datos para actualizar la UI");
      return;
    }
    
    console.log("Actualizando UI con:", data);
    
    // Actualizar tarjetas de resumen
    if (data.summary) {
      const summaryCards = document.querySelectorAll(".summary-card");
      
      if (summaryCards.length >= 4) {
        summaryCards[0].querySelector(".summary-count").textContent = 
          data.summary.total_beneficiarios ?? "0";
        
        summaryCards[1].querySelector(".summary-count").textContent = 
          data.summary.total_reservaciones ?? "0";
        
        summaryCards[2].querySelector(".summary-count").textContent = 
          data.summary.total_porciones ?? "0";
        
        const costoPromedio = parseFloat(data.summary.costo_promedio) || 0;
        summaryCards[3].querySelector(".summary-count").textContent = 
          `$${costoPromedio.toFixed(2)}`;
      }
    }
    
    // Actualizar tabla de actividad reciente
    if (data.recent_activity && data.recent_activity.length > 0) {
      const tableBody = document.querySelector(".report-table tbody");
      if (tableBody) {
        tableBody.innerHTML = data.recent_activity.map(activity => `
          <tr>
            <td>${formatDate(new Date(activity.fecha))}</td>
            <td>${activity.reservaciones}</td>
            <td>${activity.porciones}</td>
            <td>${activity.completadas}</td>
            <td>${activity.canceladas}</td>
            <td>
              <div class="progress-bar">
                <div class="progress" style="width: ${activity.tasa_asistencia}%"></div>
                <span>${activity.tasa_asistencia}%</span>
              </div>
            </td>
          </tr>
        `).join("");
      }
    }
  }
  
  // Inicializar gráficos
  function initCharts() {
    const reservationsCtx = document.getElementById('reservations-by-day-chart')?.getContext('2d');
    const usersCtx = document.getElementById('users-distribution-chart')?.getContext('2d');
    const salesCtx = document.getElementById('sales-period-chart')?.getContext('2d');
    
    if (reservationsCtx) {
      charts.reservations = new Chart(reservationsCtx, {
        type: 'bar',
        data: { labels: [], datasets: [{
          label: 'Reservaciones por día',
          data: [],
          backgroundColor: '#9e1c3f',
          borderColor: '#7a1530',
          borderWidth: 1
        }]},
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { y: { beginAtZero: true } }
        }
      });
    }
    
    if (usersCtx) {
      charts.users = new Chart(usersCtx, {
        type: 'pie',
        data: { labels: [], datasets: [{
          data: [],
          backgroundColor: ['#9e1c3f', '#c13c5e', '#c69c6d', '#d8b48e'],
          borderWidth: 1
        }]},
        options: { responsive: true, maintainAspectRatio: false }
      });
    }
    
    if (salesCtx) {
      charts.sales = new Chart(salesCtx, {
        type: 'bar',
        data: { labels: [], datasets: [{
          label: 'Ventas',
          data: [],
          backgroundColor: '#c69c6d',
          borderColor: '#a67c4e',
          borderWidth: 1
        }]},
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Monto ($)' } },
            x: { title: { display: true, text: 'Período' } }
          }
        }
      });
    }
  }
  
  // Actualizar gráficos con nuevos datos
  function updateCharts(data) {
    // Gráfico de reservaciones por día
    if (charts.reservations && data.reservations_by_day) {
      charts.reservations.data.labels = data.reservations_by_day.map(item => 
        formatDate(new Date(item.dia)));
      charts.reservations.data.datasets[0].data = data.reservations_by_day.map(item => item.cantidad);
      charts.reservations.update();
    }
    
    // Gráfico de distribución de usuarios (género)
    if (charts.users && data.users_distribution?.sexo) {
      charts.users.data.labels = data.users_distribution.sexo.map(item => {
        switch(item.sexo) {
          case 'masculino': return 'Hombres';
          case 'femenino': return 'Mujeres';
          default: return 'Otros';
        }
      });
      charts.users.data.datasets[0].data = data.users_distribution.sexo.map(item => item.cantidad);
      charts.users.update();
    }
  }
  
  // Actualizar gráfico de ventas por período
  function updateSalesChart(period) {
    if (!charts.sales) return;
    
    // Datos de prueba - en producción estos vendrían de la API
    const now = new Date();
    let labels, data;
    
    switch(period) {
      case 'day':
        labels = Array.from({length: 7}, (_, i) => {
          const d = new Date();
          d.setDate(d.getDate() - (6 - i));
          return formatDate(d);
        });
        data = labels.map(() => Math.floor(Math.random() * 2000) + 1000);
        charts.sales.data.datasets[0].label = 'Ventas por día';
        charts.sales.options.scales.x.title.text = 'Día';
        break;
        
      case 'week':
        labels = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'];
        data = labels.map(() => Math.floor(Math.random() * 10000) + 5000);
        charts.sales.data.datasets[0].label = 'Ventas por semana';
        charts.sales.options.scales.x.title.text = 'Semana';
        break;
        
      case 'month':
        labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        data = labels.map(() => Math.floor(Math.random() * 30000) + 20000);
        charts.sales.data.datasets[0].label = 'Ventas por mes';
        charts.sales.options.scales.x.title.text = 'Mes';
        break;
    }
    
    charts.sales.data.labels = labels;
    charts.sales.data.datasets[0].data = data;
    charts.sales.update();
  }
  
  // Funciones auxiliares
  function formatDate(date) {
    if (!(date instanceof Date)) date = new Date(date);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
  }
  
  function showToast(message) {
    const toast = document.getElementById("export-confirmation");
    if (toast) {
      toast.querySelector("span").textContent = message;
      toast.style.display = "block";
      setTimeout(() => { toast.style.display = "none"; }, 3000);
    }
  }
});