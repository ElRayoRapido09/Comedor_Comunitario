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
        direccion: document.getElementById("direccion"),
        edad: document.getElementById("edad"),
        sexo: document.getElementById("sexo"),
        clearFormBtn: document.getElementById("clear-form"),
        successModal: document.getElementById("success-modal"),
        printReservationBtn: document.getElementById("print-reservation"),
        newReservationBtn: document.getElementById("new-reservation"),
        statusNotification: document.getElementById("status-notification"),
        notificationMessage: document.getElementById("notification-message"),
        saveBtn: document.getElementById("saveBtn")
    };

    // Verificar elementos faltantes
Object.entries(elements).forEach(([key, element]) => {
    if (!element) {
        console.warn(`Elemento no encontrado: ${key}`);
    }
});

    // 4. Inicialización de eventos
    function initEventListeners() {
    // Verificar existencia de elementos antes de agregar listeners
    const addListener = (element, event, handler) => {
        if (element) {
            element.addEventListener(event, handler);
        } else {
            console.warn(`Elemento no encontrado para evento ${event}`);
        }
    };

    // Botón de escaneo
    addListener(elements.startScanBtn, "click", toggleScanner);

    // Cambiar cámara
    addListener(elements.switchCameraBtn, "click", switchCamera);

    // Limpiar formulario
    addListener(elements.clearFormBtn, "click", clearForm);

    // Envío de formulario
    if (elements.reservationForm) {

        // Envío de formulario - Validación y envío AJAX
        if (elements.reservationForm) {
            elements.reservationForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                
                const reservationCode = elements.codigoReservacion.value.trim();
                if (!reservationCode) {
                    showNotification("Ingrese un código de reservación válido", "error");
                    return;
                }

                try {
                    // Mostrar estado de carga
                    elements.saveBtn.disabled = true;
                    elements.saveBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                        </svg>
                        Procesando...
                    `;

                    const response = await fetch('actualizar_reservacion.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'codigo_reservacion': reservationCode,
                            'id_usuario_atendio': '<?php echo $_SESSION["usuario"]["id_usuario"] ?? ""; ?>'
                        })
                    });
                

                    const result = await response.json();
                        if (result.success) {
                            // Mostrar modal de éxito
                            document.getElementById("reservation-code").textContent = reservationCode;
                            elements.successModal.style.display = "flex";
                            document.body.style.overflow = "hidden";
                        } else {
                            showNotification(result.message || "Error al completar el pedido", "error");
                        }
                } catch (error) {
                    console.error("Error:", error);
                    showNotification("Error procesando la respuesta del servidor", "error");
                } finally {
                    // Restaurar estado del botón
                    if (elements.saveBtn) {
                        elements.saveBtn.disabled = false;
                        elements.saveBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Completar Pedido
                        `;
                    }
                }
            });
        }

        // Modal de éxito
        addListener(elements.printReservationBtn, "click", () => window.print());
        addListener(elements.newReservationBtn, "click", () => {
        elements.successModal.style.display = "none";
        document.body.style.overflow = "auto";
        clearForm();

            });
        }
    }

    // 5. Funciones principales del escáner
    async function toggleScanner() {
        if (scanning) {
            await stopScanner();
        } else {
            await startScanner();
        }
    }

    async function startScanner() {
        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }

            // Configuración de la cámara
            const constraints = {
                video: {
                    facingMode: "environment",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            // Obtener cámaras disponibles
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
                console.warn("Error al obtener cámaras, usando configuración por defecto:", err);
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

    // 6. Manejo de escaneos
    function onScanSuccess(decodedText, decodedResult) {
        html5QrCode.pause(true);
        updateScanStatus("success", "¡Código QR detectado!");
        showNotification("Código QR escaneado correctamente");
    
        try {
            // Verificar si el texto comienza con { y termina con } (indicador de JSON)
            if (decodedText.trim().startsWith('{') && decodedText.trim().endsWith('}')) {
                const userData = JSON.parse(decodedText);
                populateForm(userData);
            } else {
                // Si no es JSON, asumir que es solo el código de reservación
                elements.codigoReservacion.value = decodedText;
                showNotification("Código de reservación escaneado");
            }
            
            setTimeout(() => {
                if (html5QrCode) {
                    html5QrCode.resume();
                    updateScanStatus("scanning", "Escaneando...");
                }
            }, 3000);
    
        } catch (error) {
            console.error("Error al procesar QR:", error);
            // Si falla el parseo, usar el texto directamente como código
            elements.codigoReservacion.value = decodedText;
            showNotification("Código escaneado (formato no JSON)", "warning");
            
            setTimeout(() => {
                if (html5QrCode) {
                    html5QrCode.resume();
                    updateScanStatus("scanning", "Escaneando...");
                }
            }, 2000);
        }
    }

    function onScanFailure(error) {
        console.log("Error de escaneo:", error);
        // No mostrar notificación para no saturar al usuario
    }

    // 7. Funciones auxiliares
    function populateForm(data) {
        if (data.nombre) elements.nombre.value = data.nombre;
        if (data.correo) elements.correo.value = data.correo;
        if (data.direccion) elements.direccion.value = data.direccion;
        if (data.edad) elements.edad.value = data.edad;
        if (data.sexo) elements.sexo.value = data.sexo.toLowerCase();
        if (data.codigo_reservacion) elements.codigoReservacion.value = data.codigo_reservacion;
    }

    function clearForm() {
        elements.nombre.value = "";
        elements.correo.value = "";
        elements.direccion.value = "";
        elements.edad.value = "";
        elements.sexo.value = "";
        elements.codigoReservacion.value = "";
        
        if (scanning) {
            stopScanner();
        }
    }

    function updateScannerUI(isScanning) {
        elements.startScanBtn.disabled = isScanning;
        elements.switchCameraBtn.disabled = !isScanning || cameras.length <= 1;
        updateScanStatus(isScanning ? "scanning" : "ready", 
                        isScanning ? "Escaneando..." : "Listo para escanear");
    }

    function updateScanStatus(status, text) {
        if (!elements.scanStatus) return;
        
        elements.scanStatus.className = "status-indicator";
        elements.scanStatus.classList.add(status);
        const statusText = elements.scanStatus.querySelector(".status-text");
        if (statusText) statusText.textContent = text;
    }

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

    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // 8. Mostrar modal de éxito si hay un código en la URL
    function checkForSuccess() {
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const code = urlParams.get('code');
        
        if (success && code) {
            document.getElementById("reservation-code").textContent = code;
            elements.successModal.style.display = "flex";
            document.body.style.overflow = "hidden";
        }
    }

    // 9. Inicialización y limpieza
    initEventListeners();
    checkForSuccess();

    window.addEventListener("beforeunload", () => {
        if (scanning) {
            stopScanner();
        }
    });
});