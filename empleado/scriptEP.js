document.addEventListener('DOMContentLoaded', function() {

    // Configurar eventos primero
    setupEventListeners();
    
    // Luego cargar los datos
    loadCurrentDateMenu();
    
    // Actualizar fecha actual
    updateCurrentDate();

});

function updateCurrentDate() {
    const currentDate = new Date();
    const formattedDate = formatDate(currentDate);
    document.getElementById("current-date").textContent = formattedDate;
}

function formatDate(date) {
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

function loadSecciones() {
    fetch('menu_operations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_platillos'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Aquí puedes cargar las secciones si es necesario
            console.log('Secciones cargadas:', data.platillos);
        }
    });
}

function loadCurrentDateMenu() {
    const fechaInput = document.getElementById('menu-date');
    const fecha = fechaInput.value;
    
    fetch('menu_operations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_menu&fecha=${fecha}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderMenu(data.menu);
        }
    });
}

function renderMenu(menu) {
    // Actualizar precio y notas
    document.getElementById('menu-price').value = menu.precio || '11';
    document.getElementById('menu-notes').value = menu.notas || '';
    
    // Contenedor principal de secciones
    const sectionsContainer = document.querySelector('.menu-sections');
    
    // Eliminar todas las secciones excepto el botón de añadir sección
    const addSectionBtn = document.getElementById('add-section-btn');
    sectionsContainer.innerHTML = '';
    sectionsContainer.appendChild(addSectionBtn);
    
    // Ordenar las secciones por orden alfabético
    const seccionesOrdenadas = Object.keys(menu.secciones).sort();
    
    // Crear cada sección dinámicamente
    seccionesOrdenadas.forEach(seccionNombre => {
        const platillos = menu.secciones[seccionNombre];
        const sectionId = seccionNombre.toLowerCase().replace(/\s+/g, '-');
        
        const sectionElement = document.createElement('div');
        sectionElement.className = 'menu-section';
        sectionElement.id = `section-${sectionId}`;
        
        sectionElement.innerHTML = `
            <div class="section-header">
                <h3>${seccionNombre}</h3>
                <button class="add-item-btn" data-section="${sectionId}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Añadir Platillo
                </button>
            </div>
            <div class="menu-items"></div>
        `;
        
        const itemsContainer = sectionElement.querySelector('.menu-items');
        
        // Añadir platillos a la sección
        platillos.forEach(platillo => {
            const itemElement = createMenuItemElement(platillo);
            itemsContainer.appendChild(itemElement);
        });
        
        // Insertar la sección antes del botón de añadir sección
        sectionsContainer.insertBefore(sectionElement, addSectionBtn);
    });
    
    // Configurar eventos para los nuevos elementos
    setupItemEventListeners();
}

function createSectionElement(nombreSeccion) {
    const sectionId = nombreSeccion.toLowerCase().replace(' ', '-');
    
    const sectionElement = document.createElement('div');
    sectionElement.className = 'menu-section';
    sectionElement.id = `section-${sectionId}`;
    
    sectionElement.innerHTML = `
        <div class="section-header">
            <h3>${nombreSeccion}</h3>
            <button class="add-item-btn" data-section="${sectionId}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Añadir Platillo
            </button>
        </div>
        <div class="menu-items"></div>
    `;
    
    return sectionElement;
}

function createMenuItemElement(platillo) {
    const itemElement = document.createElement('div');
    itemElement.className = 'menu-item-editor';
    itemElement.dataset.id = platillo.id;
    itemElement.dataset.seccionId = platillo.id_seccion;
    
    itemElement.innerHTML = `
        <div class="item-controls">
            <div class="drag-handle">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="12" r="1"></circle>
                    <circle cx="9" cy="5" r="1"></circle>
                    <circle cx="9" cy="19" r="1"></circle>
                    <circle cx="15" cy="12" r="1"></circle>
                    <circle cx="15" cy="5" r="1"></circle>
                    <circle cx="15" cy="19" r="1"></circle>
                </svg>
            </div>
            <button class="delete-item-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>
        </div>
        <div class="item-fields">
            <div class="field-group">
                <input type="text" class="item-name" value="${escapeHtml(platillo.nombre || '')}" placeholder="Nombre del platillo">
                <textarea class="item-description" placeholder="Descripción del platillo">${escapeHtml(platillo.descripcion || '')}</textarea>
            </div>
            <div class="item-status">
                <label class="checkbox-label">
                    <input type="checkbox" class="item-available" ${platillo.disponible ? 'checked' : ''}>
                    <span class="checkbox-custom"></span>
                    Disponible
                </label>
            </div>
        </div>
    `;
    
    return itemElement;
}

// Función auxiliar para escapar HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function setupItemEventListeners() {
    // Eventos para botones de eliminar
    document.querySelectorAll('.delete-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemElement = this.closest('.menu-item-editor');
            const itemId = itemElement.dataset.id;
            deletePlatillo(itemId, itemElement);
        });
    });
    
    // Eventos para inputs (guardar automáticamente al cambiar)
    document.querySelectorAll('.item-name, .item-description, .item-available').forEach(input => {
        input.addEventListener('change', function() {
            const itemElement = this.closest('.menu-item-editor');
            updatePlatilloFromUI(itemElement);
        });
    });
}

function updatePlatilloFromUI(itemElement) {
    const platilloData = {
        id: itemElement.dataset.id,
        nombre: itemElement.querySelector('.item-name').value,
        descripcion: itemElement.querySelector('.item-description').value,
        disponible: itemElement.querySelector('.item-available').checked
    };
    
    fetch('menu_operations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_platillo&id_platillo=${platilloData.id}&nombre=${encodeURIComponent(platilloData.nombre)}&descripcion=${encodeURIComponent(platilloData.descripcion)}&disponible=${platilloData.disponible ? '1' : '0'}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error al actualizar platillo: ' + (data.message || ''));
        }
    });
}

function setupEventListeners() {
    // Cambio de fecha
    document.getElementById('menu-date').addEventListener('change', loadCurrentDateMenu);

    const saveBtn = document.getElementById('save-btn');
    saveBtn.removeEventListener('click', saveMenu); // Eliminar cualquier listener previo
    saveBtn.addEventListener('click', function(e) {
        e.preventDefault(); // Prevenir comportamiento por defecto
        saveMenu();
    });
    
    // Botón Guardar
    document.getElementById('save-btn').addEventListener('click', saveMenu);
    
    // Botón Añadir Sección
    document.getElementById('add-section-btn').addEventListener('click', showAddSectionModal);
    
    // Delegación de eventos para botones de añadir platillo
    document.querySelector('.menu-sections').addEventListener('click', function(e) {
        if (e.target.closest('.add-item-btn')) {
            const section = e.target.closest('.add-item-btn').dataset.section;
            showAddItemModal(section);
        }
        
        if (e.target.closest('.delete-item-btn')) {
            const itemElement = e.target.closest('.menu-item-editor');
            const itemId = itemElement.dataset.id;
            deletePlatillo(itemId, itemElement);
        }
    });  // Otros eventos...

}

function saveMenu() {
    const fecha = document.getElementById('menu-date').value;
    const precio = document.getElementById('menu-price').value;
    const notas = document.getElementById('menu-notes').value;
    
    const secciones = {};
    
    document.querySelectorAll('.menu-section').forEach(section => {
        const sectionName = section.querySelector('h3').textContent;
        const items = [];
        
        section.querySelectorAll('.menu-item-editor').forEach(item => {
            items.push({
                id: item.dataset.id,
                nombre: item.querySelector('.item-name').value,
                descripcion: item.querySelector('.item-description').value,
                disponible: item.querySelector('.item-available').checked
            });
        });
        
        secciones[sectionName] = items;
    });
    
    // Mostrar indicador de carga
    const saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
        </svg>
        Guardando...
    `;
    saveBtn.classList.add('saving');
    
    fetch('menu_operations.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=save_menu&fecha=${fecha}&precio=${precio}&notas=${encodeURIComponent(notas)}&secciones=${encodeURIComponent(JSON.stringify(secciones))}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Cambios realizados con éxito');
            saveBtn.classList.add('save-success');
            setTimeout(() => saveBtn.classList.remove('save-success'), 2000);
        } else {
            showToast('Error al guardar: ' + (data.message || ''), 'error');
        }
    })
    .catch(error => {
        showToast('Error de conexión: ' + error.message, 'error');
    })
    .finally(() => {
        // Restaurar botón
        saveBtn.disabled = false;
        saveBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Guardar Cambios
        `;
        saveBtn.classList.remove('saving');
    });
}

function showAddSectionModal() {
    // Implementar lógica para mostrar modal de añadir sección
}

function showAddItemModal(sectionId) {
    // Obtener el nombre de la sección desde el ID
    const sectionElement = document.getElementById(`section-${sectionId}`);
    const sectionName = sectionElement.querySelector('h3').textContent;
    
    // Mostrar el modal
    const modal = document.getElementById('add-item-modal');
    document.getElementById('section-id').value = sectionId;
    modal.style.display = 'block';
    
    // Configurar el formulario
    const form = document.getElementById('add-item-form');
    form.onsubmit = function(e) {
        e.preventDefault();
        
        const nombre = document.getElementById('item-name').value;
        const descripcion = document.getElementById('item-description').value;
        const disponible = document.getElementById('item-available').checked;
        
        fetch('menu_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add_platillo&nombre=${encodeURIComponent(nombre)}&descripcion=${encodeURIComponent(descripcion)}&id_seccion=${sectionId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Crear y añadir el nuevo platillo
                const platillo = {
                    id: data.id,
                    nombre: nombre,
                    descripcion: descripcion,
                    disponible: disponible,
                    id_seccion: sectionId
                };
                
                const itemElement = createMenuItemElement(platillo);
                sectionElement.querySelector('.menu-items').appendChild(itemElement);
                
                // Cerrar modal y resetear formulario
                modal.style.display = 'none';
                form.reset();
                
                showToast('Platillo añadido correctamente');
            } else {
                alert('Error al añadir platillo: ' + (data.message || ''));
            }
        });
    };
}

function deletePlatillo(id, element) {
    if (confirm('¿Estás seguro de que quieres eliminar este platillo?')) {
        fetch('menu_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_platillo&id_platillo=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.remove();
                showToast('Platillo eliminado correctamente');
            } else {
                alert('Error al eliminar platillo: ' + data.message);
            }
        });
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('save-confirmation');
    const toastMessage = document.getElementById('toast-message');
    
    // Configurar según el tipo
    toast.className = 'toast';
    toast.classList.add(type);
    
    // Establecer mensaje
    toastMessage.textContent = message;
    
    // Mostrar toast
    toast.classList.add('show');
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}