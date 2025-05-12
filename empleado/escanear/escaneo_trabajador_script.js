document.addEventListener("DOMContentLoaded", () => {
    // 1. Configuración inicial
    console.log("Script de escaneo QR cargado correctamente");
    const currentDate = new Date();
    document.getElementById("current-date").textContent = formatDate(currentDate);

    // 2. Variables de estado del escáner
    let html5QrCode = null;
    let cameraId = null;
    let scanning = false;
    let cameras = [];

    // 3. Elementos del DOM
    const elements = {
        startScanBtn: document.getElementById("start-scan"),
        switchCameraBtn: document.getElementById("switch-camera"),
        scanStatus: document.getElementById("scan-status"),
        reservationForm: document.getElementById("reservation-form"),
        codigoReservacion: document.getElementById("codigo-reservacion"),
        nombre: document.getElementById("nombre"),
        correo: document.getElementById("correo"),
        clearFormBtn: document.getElementById("clear-form"),
        successModal: document.getElementById("success-modal"),
        printReservationBtn: document.getElementById("print-reservation"),
        newReservationBtn: document.getElementById("new-reservation"),
        statusNotification: document.getElementById("status-notification"),
        notificationMessage: document.getElementById("notification-message"),
        saveBtn: document.getElementById("saveBtn")
    };

    // 4. Función para verificar y parsear JSON de forma segura
    function safeJsonParse(str) {
        try {
            if (typeof str !== 'string') return { isValid: false, data: null };
            
            const cleanedStr = str.trim();
            if ((cleanedStr.startsWith('{') && cleanedStr.endsWith('}')) || 
                (cleanedStr.startsWith('[') && cleanedStr.endsWith(']'))) {
                return { isValid: true, data: JSON.parse(cleanedStr) };
            }
            return { isValid: false, data: cleanedStr };
        } catch (e) {
            console.error("Error en safeJsonParse:", e);
            return { isValid: false, data: str };
        }
    }

    // 5. Función mejorada para manejar el escaneo exitoso
    function onScanSuccess(decodedText) {
        if (!html5QrCode) return;

        html5QrCode.pause(true);
        updateScanStatus("success", "¡Código QR detectado!");

        const result = safeJsonParse(decodedText);
        
        if (result.isValid && typeof result.data === 'object') {
            populateForm(result.data);
            showNotification("Datos de usuario cargados correctamente");
        } else {
            elements.codigoReservacion.value = result.data;
            showNotification("Código de reservación escaneado", "info");
        }

        setTimeout(() => {
            if (html5QrCode) {
                html5QrCode.resume();
                updateScanStatus("scanning", "Escaneando...");
            }
        }, 3000);
    }

    // 6. Función para manejar errores de escaneo
    function onScanFailure(error) {
        console.log("Error de escaneo:", error);
    }

    // 7. Función para llenar el formulario con datos
    function populateForm(data) {
        if (typeof data !== 'object') return;
        
        if (data.nombre) elements.nombre.value = data.nombre || "";
        if (data.correo) elements.correo.value = data.correo || "";
        if (data.codigo_reservacion) {
            elements.codigoReservacion.value = data.codigo_reservacion || "";
        }
    }

    // 8. Función para limpiar el formulario
    function clearForm() {
        elements.nombre.value = "";
        elements.correo.value = "";
        elements.codigoReservacion.value = "";
        
        if (scanning) {
            stopScanner();
        }
    }

    // 9. Función para iniciar/detener el escáner
    async function toggleScanner() {
        if (scanning) {
            await stopScanner();
        } else {
            await startScanner();
        }
    }

    // 10. Función para iniciar el escáner
    async function startScanner() {
        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }

            const constraints = {
                video: {
                    facingMode: "environment",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            try {
                cameras = await Html5Qrcode.getCameras();
                if (cameras.length === 0) throw new Error("No se encontraron cámaras");

                cameraId = cameras.find(cam => 
                    cam.label.toLowerCase().includes('back') || 
                    cam.label.toLowerCase().includes('rear')
                )?.id || cameras[0].id;

                await html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    onScanSuccess,
                    onScanFailure
                );

                updateScannerUI(true);
                showNotification("Escáner iniciado correctamente");

            } catch (err) {
                console.warn("Error al obtener cámaras:", err);
                await html5QrCode.start(
                    constraints,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    onScanSuccess,
                    onScanFailure
                );
                updateScannerUI(true);
            }

            scanning = true;

        } catch (err) {
            console.error("Error al iniciar escáner:", err);
            handleScannerError(err);
        }
    }

    // 11. Función para detener el escáner
    async function stopScanner() {
        try {
            if (html5QrCode && scanning) {
                await html5QrCode.stop();
                scanning = false;
                updateScannerUI(false);
                showNotification("Escáner detenido");
            }
        } catch (err) {
            console.error("Error al detener escáner:", err);
            showNotification("Error al detener el escáner", "error");
        }
    }

    // 12. Función para cambiar de cámara
    async function switchCamera() {
        if (cameras.length < 2) return;

        try {
            await stopScanner();
            const currentIndex = cameras.findIndex(cam => cam.id === cameraId);
            cameraId = cameras[(currentIndex + 1) % cameras.length].id;
            await startScanner();
            showNotification(`Cámara cambiada a: ${cameras.find(cam => cam.id === cameraId).label || 'Cámara'}`);
        } catch (err) {
            console.error("Error al cambiar cámara:", err);
            showNotification("Error al cambiar de cámara", "error");
        }
    }

    // 13. Función para actualizar la UI del escáner
    function updateScannerUI(isScanning) {
        if (elements.startScanBtn) elements.startScanBtn.disabled = isScanning;
        if (elements.switchCameraBtn) elements.switchCameraBtn.disabled = !isScanning || cameras.length <= 1;
        updateScanStatus(isScanning ? "scanning" : "ready", 
                        isScanning ? "Escaneando..." : "Listo para escanear");
    }

    // 14. Función para actualizar el estado del escaneo
    function updateScanStatus(status, text) {
        if (!elements.scanStatus) return;
        
        elements.scanStatus.className = "status-indicator";
        elements.scanStatus.classList.add(status);
        const statusText = elements.scanStatus.querySelector(".status-text");
        if (statusText) statusText.textContent = text;
    }

    // 15. Función para mostrar notificaciones
    function showNotification(message, type = "success") {
        if (!elements.notificationMessage || !elements.statusNotification) return;
        
        elements.notificationMessage.textContent = message;
        elements.statusNotification.className = "toast";
        elements.statusNotification.style.backgroundColor = 
            type === "error" ? "var(--error-color)" :
            type === "warning" ? "var(--warning-color)" :
            "var(--success-color)";
        
        elements.statusNotification.style.display = "block";
        setTimeout(() => {
            elements.statusNotification.style.display = "none";
        }, 3000);
    }

    // 16. Función para manejar errores del escáner
    function handleScannerError(err) {
        let errorMessage = "Error al acceder a la cámara";
        
        if (err.name === 'NotAllowedError') {
            errorMessage = "Permiso de cámara denegado. Por favor habilite el acceso.";
        } else if (err.name === 'NotFoundError') {
            errorMessage = "No se encontró ninguna cámara";
        } else if (err.name === 'NotReadableError') {
            errorMessage = "La cámara no está disponible";
        } else if (err.message.includes('Permission dismissed')) {
            errorMessage = "Debe permitir el acceso a la cámara";
        }
        
        showNotification(errorMessage, "error");
    }

    // 17. Función para formatear fecha
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // 18. Función mejorada para manejar el envío del formulario
    async function handleFormSubmit(e) {
    e.preventDefault();
    
    const reservationCode = elements.codigoReservacion.value.trim();
    if (!reservationCode) {
        showNotification("Ingrese un código de reservación válido", "error");
        return;
    }

    try {
        // Mostrar estado de carga
        elements.saveBtn.disabled = true;
        elements.saveBtn.innerHTML = `Procesando...`;

        const response = await fetch('actualizar_reservacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'codigo_reservacion': reservationCode
            })
        });

        // Primero obtener el texto de la respuesta para depuración
        const responseText = await response.text();
        console.log("Respuesta del servidor:", responseText);

        // Intentar parsear como JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error("Error al parsear JSON:", parseError);
            throw new Error(`La respuesta del servidor no es válida: ${responseText.substring(0, 100)}...`);
        }

        // Verificar estructura básica de la respuesta
        if (!result || typeof result !== 'object') {
            throw new Error("Formato de respuesta incorrecto");
        }

        if (result.success) {
            document.getElementById("reservation-code").textContent = reservationCode;
            elements.successModal.style.display = "flex";
            document.body.style.overflow = "hidden";
        } else {
            showNotification(result.message || "Error al completar el pedido", "error");
        }
    } catch (error) {
        console.error("Error en handleFormSubmit:", error);
        showNotification(error.message || "Error al procesar la respuesta del servidor", "error");
    } finally {
        if (elements.saveBtn) {
            elements.saveBtn.disabled = false;
            elements.saveBtn.innerHTML = `Completar Pedido`;
            }
        }
    }

    // 19. Inicialización de eventos
    function initEventListeners() {
        // Botón de escaneo
        if (elements.startScanBtn) {
            elements.startScanBtn.addEventListener("click", toggleScanner);
        }

        // Cambiar cámara
        if (elements.switchCameraBtn) {
            elements.switchCameraBtn.addEventListener("click", switchCamera);
        }

        // Limpiar formulario
        if (elements.clearFormBtn) {
            elements.clearFormBtn.addEventListener("click", clearForm);
        }

        // Envío de formulario
        if (elements.reservationForm) {
            elements.reservationForm.addEventListener("submit", handleFormSubmit);
        }

        // Modal de éxito
        if (elements.printReservationBtn) {
            elements.printReservationBtn.addEventListener("click", () => window.print());
        }
        if (elements.newReservationBtn) {
            elements.newReservationBtn.addEventListener("click", () => {
                elements.successModal.style.display = "none";
                document.body.style.overflow = "auto";
                clearForm();
            });
        }
    }

    // 20. Inicialización
    initEventListeners();

    // 21. Limpieza al salir
    window.addEventListener("beforeunload", () => {
        if (scanning) {
            stopScanner();
        }
    });
});