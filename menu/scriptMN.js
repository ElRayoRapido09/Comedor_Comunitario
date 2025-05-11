document.addEventListener("DOMContentLoaded", function() {
    // 1. Función para formatear fechas
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // 2. Configuración inicial
    console.log("Script cargado correctamente");
    
    // Establecer fecha actual
    const currentDate = new Date();
    const dateDisplay = document.getElementById("current-date");
    if (dateDisplay) {
        dateDisplay.textContent = formatDate(currentDate);
    }

    // 3. Menú desplegable de usuario
    const userMenu = document.getElementById('user-menu');
    const dropdownMenu = document.getElementById('dropdown-menu');
    
    if (userMenu && dropdownMenu) {
        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function() {
            dropdownMenu.style.display = 'none';
        });
    }

    // 4. Manejo del botón de reserva
    const reserveButton = document.getElementById('reserve-button');
    
    if (reserveButton) {
        reserveButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log("Botón de reserva clickeado");
            
            // Verificación de menuInicial
            if (!window.menuInicial || !window.menuInicial.id_menu) {
                console.error("No hay información de menú disponible");
                alert("Error: No se encontró información del menú. Por favor recarga la página.");
                return;
            }
            
            // Validar que se hayan seleccionado platillos en todas las secciones
            const selectedPlatillos = getSelectedPlatillos();
            const totalSections = document.querySelectorAll('.menu-section').length;
            
            if (selectedPlatillos.length < totalSections) {
                alert('Por favor selecciona un platillo de cada sección disponible');
                return;
            }
            
            showReservationModal();
        });
    }

    // 5. Función para obtener platillos seleccionados
    function getSelectedPlatillos() {
        const selected = [];
        const radioGroups = document.querySelectorAll('input[type="radio"].platillo-radio:checked');
        
        radioGroups.forEach(radio => {
            const seccionId = radio.getAttribute('data-seccion');
            const seccionElement = document.querySelector(`.section-title[data-seccion-id="${seccionId}"]`);
            const seccionName = seccionElement ? seccionElement.textContent : 'Sección ' + seccionId;
            const platilloName = radio.closest('.menu-item').querySelector('.item-name').textContent.replace(/^\s*[\u25CF\u25CB]\s*/, '').trim();
            
            selected.push({
                id_seccion: seccionId,
                seccion: seccionName,
                id_platillo: radio.value,
                platillo: platilloName
            });
        });
        
        return selected;
    }

    // 6. Mostrar modal de reserva
    function showReservationModal() {
        console.log("Mostrando modal para menú ID:", window.menuInicial.id_menu);

        // Obtener platillos seleccionados
        const selectedPlatillos = getSelectedPlatillos();

        // Eliminar modal existente si hay uno
        const existingModal = document.getElementById('reservation-modal');
        if (existingModal) existingModal.remove();

        // Crear HTML del modal con estilos inline
        const modalHTML = `
        <div class="modal" id="reservation-modal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        ">
            <div class="modal-content" style="
                background: white;
                padding: 25px;
                border-radius: 10px;
                width: 90%;
                max-width: 500px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            ">
                <span class="close-modal" style="
                    position: absolute;
                    top: 15px;
                    right: 20px;
                    font-size: 24px;
                    cursor: pointer;
                    color: #666;
                ">&times;</span>
                
                <h2 style="color: #9e1c3f; margin-bottom: 20px; text-align: center;">Reservar Comida</h2>
                
                <div class="selection-summary" style="
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border-left: 3px solid #c69c6d;
                ">
                    <h3 style="font-size: 16px; margin-bottom: 10px; color: #9e1c3f;">Tu selección:</h3>
                    <ul id="selected-items-list" style="list-style: none; padding-left: 0;">
                        ${selectedPlatillos.map(item => `
                            <li style="margin-bottom: 5px; font-size: 14px;">
                                <strong>${item.seccion}:</strong> ${item.platillo}
                                <input type="hidden" name="platillos[${item.id_seccion}]" value="${item.id_platillo}">
                            </li>
                        `).join('')}
                    </ul>
                </div>
                
                <form id="reservation-form">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Hora de Recogida:</label>
                        <select id="hora-recogida" style="
                            width: 100%;
                            padding: 12px 15px;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            font-family: inherit;
                        " required>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Número de Porciones (Máx. 3):</label>
                        <input type="number" id="num-porciones" min="1" max="3" value="1" style="
                            width: 100%;
                            padding: 12px 15px;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                        " required>
                    </div>
                    
                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Notas Adicionales:</label>
                        <textarea id="notas" rows="3" style="
                            width: 100%;
                            padding: 12px 15px;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            resize: vertical;
                        "></textarea>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 15px;">
                        <button type="button" class="cancel-button" style="
                            padding: 10px 20px;
                            background: #f5f5f5;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        ">Cancelar</button>
                        
                        <button type="submit" class="submit-button" style="
                            padding: 10px 20px;
                            background: #9e1c3f;
                            color: white;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            font-weight: 500;
                        ">Confirmar Reserva</button>
                    </div>
                </form>
            </div>
        </div>`;

        // Insertar el modal en el DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Configurar eventos del modal
        const modal = document.getElementById('reservation-modal');
        const closeBtn = modal.querySelector('.close-modal');
        const cancelBtn = modal.querySelector('.cancel-button');

        closeBtn.onclick = function() {
            modal.remove();
        };

        cancelBtn.onclick = function() {
            modal.remove();
        };

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Manejar envío del formulario
        document.getElementById('reservation-form').addEventListener('submit', function(e) {
            e.preventDefault();
            processReservation();
        });
    }

    // 7. Procesar la reserva
    function processReservation() {
        console.log("Procesando reserva...");
        
        const submitBtn = document.querySelector('#reservation-form .submit-button');
        if (!submitBtn) return;
    
        // Deshabilitar botón durante el procesamiento
        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';
    
        // Obtener platillos seleccionados
        const selectedPlatillos = getSelectedPlatillos();
        
        // Convertir a formato para enviar al servidor
        const platillosData = {};
        selectedPlatillos.forEach(item => {
            platillosData[item.id_seccion] = item.id_platillo;
        });
    
        // Obtener datos del formulario
        const reservationData = {
            id_menu: window.menuInicial.id_menu,
            hora_recogida: document.getElementById('hora-recogida').value,
            num_porciones: document.getElementById('num-porciones').value,
            notas: document.getElementById('notas').value,
            platillos: platillosData
        };
    
        console.log("Datos de reserva:", reservationData);
    
        // Enviar datos al servidor
        fetch('procesar_reserva.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(reservationData)
        })
        .then(response => {
            // Primero verificar si la respuesta es JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error(`Respuesta no JSON: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log("Respuesta del servidor:", data);
            
            if (data.success) {
                showConfirmationModal(data.codigo_reservacion);
                document.getElementById('reservation-modal').remove();
            } else {
                throw new Error(data.error || 'Error al procesar la reserva');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
            
            // Mostrar más detalles en consola para depuración
            if (error.response) {
                error.response.text().then(text => {
                    console.error('Respuesta del servidor:', text);
                });
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmar Reserva';
        });
    }

    // 8. Mostrar modal de confirmación
    function showConfirmationModal(codigoReservacion) {
        const modalHTML = `
        <div class="modal" id="confirmation-modal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        ">
            <div class="modal-content" style="
                background: white;
                padding: 30px;
                border-radius: 10px;
                width: 90%;
                max-width: 400px;
                text-align: center;
            ">
                <div style="
                    width: 60px;
                    height: 60px;
                    background-color: #4CAF50;
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    font-size: 30px;
                    margin: 0 auto 20px;
                ">✓</div>
                
                <h2 style="color: #9e1c3f; margin-bottom: 15px;">¡Reserva Confirmada!</h2>
                <p style="margin-bottom: 20px;">Tu reserva ha sido registrada exitosamente.</p>
                
                <div style="
                    background-color: #f5f5f5;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 25px;
                    font-family: monospace;
                ">
                    <p style="margin-bottom: 5px; font-weight: 500;">Código de Reserva:</p>
                    <div style="font-size: 18px; font-weight: bold;">${codigoReservacion}</div>
                </div>
                
                <button id="close-confirmation" style="
                    padding: 10px 20px;
                    background: #9e1c3f;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: 500;
                ">Aceptar</button>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        document.getElementById('close-confirmation').addEventListener('click', function() {
            document.getElementById('confirmation-modal').remove();
        });
    }

    // 9. Manejo del menú semanal (opcional)
    const daysContainer = document.querySelector(".days-container");
    if (daysContainer) {
        daysContainer.addEventListener("click", function(e) {
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

    // 10. Función para actualizar la visualización del menú
    function updateMenuDisplay(menuData) {
        console.log("Actualizando menú con:", menuData);
        // Implementación según tus necesidades
    }

    // 11. Manejar cambios en la selección de platillos
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches('input[type="radio"].platillo-radio')) {
            const menuItem = e.target.closest('.menu-item');
            if (menuItem) {
                // Resaltar el ítem seleccionado
                document.querySelectorAll('.menu-item').forEach(item => {
                    item.style.backgroundColor = '';
                });
                menuItem.style.backgroundColor = 'rgba(198, 156, 109, 0.1)';
            }
        }
    });
});