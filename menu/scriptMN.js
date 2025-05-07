document.addEventListener("DOMContentLoaded", () => {
  const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaHoy = hoy.toISOString().split('T')[0];
    
    // Marcar el día actual como activo al cargar
    document.querySelectorAll('.day-item').forEach(day => {
        if (day.getAttribute('data-fecha') === fechaHoy) {
            day.classList.add('active');
            day.querySelector('.day-name').textContent = 'Hoy';
        }
    });

    // Control del menú desplegable de usuario
const userMenu = document.getElementById('user-menu');
const dropdownMenu = document.getElementById('dropdown-menu');

// Opcional: Si quieres mantener el menú abierto al hacer clic
userMenu.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = dropdownMenu.style.display === 'block';
    dropdownMenu.style.display = isOpen ? 'none' : 'block';
});

// Cerrar el menú al hacer clic fuera de él
document.addEventListener('click', (e) => {
    if (!userMenu.contains(e.target)) {
        dropdownMenu.style.display = 'none';
    }
});

  // Set current date
  const currentDate = new Date();
  const formattedDate = formatDate(currentDate);
  document.getElementById("current-date").textContent = formattedDate;

  // Modal functionality (mantén el código existente)
  // ...

  // Weekly menu day selection - versión mejorada
  const daysContainer = document.querySelector(".days-container");
  
  // En la función que maneja el clic en los días, modifica así:
daysContainer.addEventListener("click", (e) => {
  const dayItem = e.target.closest(".day-item");
  if (!dayItem) return;
  
  const fecha = dayItem.getAttribute("data-fecha");
  const diaSemana = dayItem.getAttribute("data-dia-semana"); // Nuevo
  if (!fecha) return;
  
  // Mostrar estado de carga
  dayItem.classList.add("loading");
  daysContainer.style.pointerEvents = "none";
  
  fetch(`get_menu_dia.php?fecha=${fecha}`)
      .then(response => response.json())
      .then(data => {
          updateMenuDisplay(data);
          
          // Actualizar clases activas
          document.querySelectorAll(".day-item").forEach((d) => {
              d.classList.remove("active");
          });
          dayItem.classList.add("active");
          
          // Actualizar el nombre del día correctamente
          const dayName = dayItem.querySelector(".day-name");
          const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
          const hoy = new Date();
          hoy.setHours(0, 0, 0, 0);
          const fechaSeleccionada = new Date(fecha);         
      })
      .catch(error => {
          console.error("Error:", error);
      })
      .finally(() => {
          dayItem.classList.remove("loading");
          daysContainer.style.pointerEvents = "auto";
      });
});

  // Helper functions
  function formatDate(date) {
      const day = String(date.getDate()).padStart(2, "0");
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const year = date.getFullYear();

      return `${day}/${month}/${year}`;
  }

  function generateReservationCode() {
      const prefix = "CC";
      const numbers = Math.floor(1000 + Math.random() * 9000);
      const suffix = Math.floor(10 + Math.random() * 90);

      return `${prefix}-${numbers}-${suffix}`;
  }
  
  function updateMenuDisplay(menuData) {
      // Actualizar el precio
      const priceElement = document.querySelector(".menu-price");
      if (priceElement && menuData.precio !== undefined) {
          priceElement.textContent = `$${menuData.precio}`;
      }
      
      // Actualizar las secciones y platillos
      const menuContent = document.querySelector(".menu-content");
      if (menuContent && menuData.secciones) {
          let htmlContent = '';
          
          menuData.secciones.forEach(seccion => {
              htmlContent += `
                  <div class="menu-section">
                      <h3 class="section-title">${seccion.nombre_seccion}</h3>
              `;
              
              seccion.platillos.forEach(platillo => {
                  htmlContent += `
                      <div class="menu-item ${platillo.disponible ? 'available' : 'unavailable'}">
                          <div class="item-info">
                              <h4 class="item-name">${platillo.nombre_platillo}</h4>
                              <p class="item-description">${platillo.descripcion || ''}</p>
                          </div>
                          <div class="item-status">
                              <span class="status-indicator ${platillo.disponible ? 'available' : 'unavailable'}"></span>
                              <span class="status-text">${platillo.disponible ? 'Disponible' : 'Agotado'}</span>
                          </div>
                      </div>
                  `;
              });
              
              htmlContent += `</div>`;
          });
          
          menuContent.innerHTML = htmlContent;
      }
      
      // Actualizar notas
      const menuNotes = document.querySelector(".menu-notes");
      if (menuNotes) {
          let notesHtml = '';
          
          if (menuData.notas) {
              notesHtml += `<p>${menuData.notas.replace(/\n/g, '<br>')}</p>`;
          }
          notesHtml += '<p>* Menú sujeto a disponibilidad.</p>';
          
          menuNotes.innerHTML = notesHtml;
      }
  }
});