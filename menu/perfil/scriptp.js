document.addEventListener("DOMContentLoaded", () => {
    // 1. Función para formatear fecha actual
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // 2. Mostrar fecha actual en el header
    document.getElementById("current-date").textContent = formatDate(new Date());

    // 3. Función para mostrar notificaciones tipo Toast
    function showToast(message, isError = false) {
        const toast = document.createElement("div");
        toast.className = `toast ${isError ? 'error' : ''}`;
        toast.innerHTML = `
            <div class="toast-content">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="${isError ? 'M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z' : 'M22 11.08V12a10 10 0 1 1-5.93-9.14'}"></path>
                    <path d="${isError ? 'M12 9v4' : ''}"></path>
                    <path d="${isError ? 'M12 17h.01' : 'M22 4 12 14.01 9 11.01'}"></path>
                </svg>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // 4. Función principal para generar el QR
    function generateProfileQR() {
        try {
            // Obtener datos del DOM
            const userData = {
                nombre: document.getElementById("user-name").textContent.trim(),
                correo: document.getElementById("user-email").textContent.trim(),
                codigo_reservacion: extractReservationCode()
            };
    
            console.log("Datos para QR:", userData); // Para depuración
    
            const qrContainer = document.getElementById("profile-qr");
            
            // Limpiar contenedor
            qrContainer.innerHTML = "";
    
            // Validación básica
            if (!userData.nombre) {
                throw new Error("Se requiere nombre completo");
            }
    
            // Crear instancia QR
            const qr = qrcode(0, 'H'); // Tipo automático, corrección alta
            qr.addData(JSON.stringify(userData));
            qr.make();
    
            // Generar imagen QR
            const qrImg = document.createElement('img');
            qrImg.src = qr.createDataURL(4, 0); // Tamaño 4px, sin margen
            qrImg.alt = `Código QR de ${userData.nombre}`;
            qrImg.style.width = '180px';
            qrImg.style.height = '180px';
            
            // Agregar al DOM
            qrContainer.appendChild(qrImg);
    
        } catch (error) {
            console.error("Error al generar QR:", error);
            
            // Mostrar mensaje de error en el contenedor
            document.getElementById("profile-qr").innerHTML = `
                <div class="qr-fallback">
                    <p>Error generando QR</p>
                    <small>${error.message}</small>
                </div>
            `;
            
            // Mostrar notificación
            showToast(`Error: ${error.message}`, true);
        }
    }

    // 5. Función para extraer el código de reservación del texto
    function extractReservationCode() {
        const reservationText = document.getElementById("user-reservation").textContent.trim();
        if (reservationText.includes("Código:")) {
            return reservationText.split("Código: ")[1].split(" - ")[0];
        }
        return "Sin reservación";
    }

    // 6. Inicialización de la aplicación
    function initApp() {
        try {
            // Generar QR al cargar
            generateProfileQR();
            
            // Mostrar mensaje de bienvenida
            const nombre = document.getElementById("user-name").textContent.trim();
            if (nombre && nombre !== "Usuario Ejemplo") {
                showToast(`Bienvenido ${nombre.split(' ')[0]}`, false);
            }
            
            // Verificar si hay reservación pendiente y mostrar notificación
            const reservationCode = extractReservationCode();
            if (reservationCode !== "Sin reservación") {
                showToast(`Tiene una reservación pendiente (${reservationCode})`, false);
            }
            
        } catch (error) {
            console.error("Error en initApp:", error);
            showToast("Error al cargar la aplicación", true);
        }
    }

    // Iniciar la aplicación
    initApp();
});