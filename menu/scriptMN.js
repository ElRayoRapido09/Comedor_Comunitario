document.addEventListener("DOMContentLoaded", function() {
    // 1. Configuración inicial y helpers
    console.log("Script cargado correctamente"); // Para depuración
    
    // Función formatDate corregida
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // 2. Configuración de fecha actual
    try {
        const currentDate = new Date();
        document.getElementById("current-date").textContent = formatDate(currentDate);
    } catch (e) {
        console.error("Error al configurar fecha:", e);
    }

    // 3. Menú desplegable de usuario (tu código existente)
    const userMenu = document.getElementById('user-menu');
    const dropdownMenu = document.getElementById('dropdown-menu');
    
    if (userMenu && dropdownMenu) {
        userMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });
        
        document.addEventListener('click', () => {
            dropdownMenu.style.display = 'none';
        });
    }

    // 4. Manejo del botón de reserva - Versión definitiva
    const reserveButton = document.getElementById('reserve-button');
    
    if (reserveButton) {
        reserveButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log("Botón de reserva clickeado"); // Confirmación en consola
            showReservationModal();
        });
    } else {
        console.error("Botón de reserva no encontrado");
    }

    // 5. Funciones para el modal de reserva
    function showReservationModal() {
        // Verificar si menuInicial está disponible
        if (!window.menuInicial) {
        console.error("Error: menuInicial no está definido");
        alert("Error: No se encontró información del menú. Recarga la página.");
        return;
    }

    if (!menuInicial.id_menu) {
        console.error("Error: menuInicial.id_menu es null o no existe");
        alert("Error: No hay un menú disponible para reservar hoy.");
        return;
    }

        // Crear modal
        const modalHTML = `
            <div class="modal" id="reservation-modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Reservar Comida</h2>
                    <form id="reservation-form">
                        <div class="form-group">
                            <label for="hora-recogida">Hora de Recogida:</label>
                            <select id="hora-recogida" required>
                                <option value="12:00">12:00 PM</option>
                                <option value="12:30">12:30 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="13:30">1:30 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="14:30">2:30 PM</option>
                                <option value="15:00">3:00 PM</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="num-porciones">Número de Porciones (Máx. 3):</label>
                            <input type="number" id="num-porciones" min="1" max="3" value="1" required>
                        </div>
                        <div class="form-group">
                            <label for="notas">Notas Adicionales:</label>
                            <textarea id="notas" rows="3"></textarea>
                        </div>
                        <div class="button-group">
                            <button type="button" class="cancel-button">Cancelar</button>
                            <button type="submit" class="submit-button">Confirmar Reserva</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        setupModalEvents();
    }

    function setupModalEvents() {
        const modal = document.getElementById('reservation-modal');
        if (!modal) return;

        // Cerrar modal
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        modal.querySelector('.cancel-button').addEventListener('click', () => modal.remove());
        modal.addEventListener('click', (e) => e.target === modal && modal.remove());

        // Enviar formulario
        const form = document.getElementById('reservation-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                processReservation();
            });
        }
    }

    function processReservation() {
        const submitBtn = document.querySelector('#reservation-form .submit-button');
        if (!submitBtn) return;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';

        const reservationData = {
            id_menu: window.menuInicial.id_menu,
            hora_recogida: document.getElementById('hora-recogida').value,
            num_porciones: document.getElementById('num-porciones').value,
            notas: document.getElementById('notas').value
        };

        console.log("Enviando datos:", reservationData);

        fetch('procesar_reserva.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(reservationData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showConfirmationModal(data.codigo_reservacion);
                document.getElementById('reservation-modal').remove();
            } else {
                alert(data.error || 'Error al procesar la reserva');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmar Reserva';
        });
    }

    function showConfirmationModal(codigo) {
        const modalHTML = `
            <div class="modal" id="confirmation-modal">
                <div class="modal-content confirmation">
                    <div class="success-icon">✓</div>
                    <h2>¡Reserva Confirmada!</h2>
                    <div class="confirmation-code">${codigo}</div>
                    <button class="submit-button" id="close-confirmation">Aceptar</button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.getElementById('close-confirmation').addEventListener('click', () => {
            document.getElementById('confirmation-modal').remove();
        });
    }

    // 6. Código para el menú semanal (opcional)
    const daysContainer = document.querySelector(".days-container");
    if (daysContainer) {
        daysContainer.addEventListener("click", (e) => {
            const dayItem = e.target.closest(".day-item");
            if (!dayItem) return;

            const fecha = dayItem.getAttribute("data-fecha");
            if (!fecha) return;

            dayItem.classList.add("loading");
            
            fetch(`get_menu_dia.php?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    updateMenuDisplay(data);
                    document.querySelectorAll(".day-item").forEach(d => d.classList.remove("active"));
                    dayItem.classList.add("active");
                })
                .catch(console.error)
                .finally(() => dayItem.classList.remove("loading"));
        });
    }

    function updateMenuDisplay(menuData) {
        // Tu implementación existente
    }
});