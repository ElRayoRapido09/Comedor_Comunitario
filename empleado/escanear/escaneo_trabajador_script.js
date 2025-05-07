console.log("Script de escaneo de trabajador cargado.");
document.addEventListener("DOMContentLoaded", () => {
    // Set current date
    const currentDate = new Date();
    const formattedDate = formatDate(currentDate);
    document.getElementById("current-date").textContent = formattedDate;
  
    // Initialize QR Scanner
    let html5QrCode;
    let cameraId;
    let scanning = false;
    let devices = [];
  
    // Get DOM elements
    const startScanBtn = document.getElementById("start-scan");
    const switchCameraBtn = document.getElementById("switch-camera");
    const scanStatus = document.getElementById("scan-status");
    const clearFormBtn = document.getElementById("clear-form");
    const decreaseRacionesBtn = document.getElementById("decrease-raciones");
    const increaseRacionesBtn = document.getElementById("increase-raciones");
    const racionesInput = document.getElementById("raciones");
    const reservationForm = document.getElementById("reservation-form");
    const searchUserModal = document.getElementById("search-user-modal");
    const closeSearchModal = searchUserModal.querySelector(".close-modal");
    const successModal = document.getElementById("success-modal");
    const printReservationBtn = document.getElementById("print-reservation");
    const newReservationBtn = document.getElementById("new-reservation");
    const statusNotification = document.getElementById("status-notification");
    const notificationMessage = document.getElementById("notification-message");
    const manualEntryBtn = document.getElementById("manual-entry-btn");
  
    // Manual entry button
    manualEntryBtn.addEventListener("click", () => {
        searchUserModal.style.display = "flex";
        document.body.style.overflow = "hidden";
    });
  
    // Start QR Scanner - Improved version
    startScanBtn.addEventListener("click", async () => {
        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("qr-reader");
            }
  
            if (scanning) {
                showNotification("La cámara ya está en uso", "error");
                return;
            }
  
            // Enhanced configuration for cross-browser compatibility
            const constraints = {
                video: {
                    facingMode: "environment", // Prefer rear camera
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };
  
            // Check for browser support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showNotification("Tu navegador no soporta acceso a la cámara o no tiene los permisos necesarios", "error");
                return;
            }
  
            // Test camera access
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            stream.getTracks().forEach(track => track.stop());
  
            // Get available cameras with better error handling
            try {
                devices = await Html5Qrcode.getCameras();
                
                if (devices.length === 0) {
                    showNotification("No se encontraron cámaras disponibles", "error");
                    return;
                }
  
                // Prefer rear camera if available
                cameraId = devices.find(device => 
                    device.label.toLowerCase().includes('back') || 
                    device.label.toLowerCase().includes('rear')
                )?.id || devices[0].id;
                
                // Start scanner with selected camera
                await startScanner(cameraId);
                
            } catch (err) {
                console.warn("Error getting camera list, trying default configuration:", err);
                // Fallback to basic configuration if camera enumeration fails
                await startScanner(null, constraints);
            }
            
        } catch (err) {
            console.error("Error accessing camera:", err);
            
            // Enhanced error handling
            let errorMessage = "Error al acceder a la cámara";
            if (err.name === 'NotAllowedError') {
                errorMessage = "Permiso de cámara denegado. Por favor habilita el acceso a la cámara en la configuración de tu navegador.";
            } else if (err.name === 'NotFoundError') {
                errorMessage = "No se encontró ninguna cámara en el dispositivo";
            } else if (err.name === 'NotReadableError') {
                errorMessage = "La cámara no puede ser accedida (¿está siendo usada por otra aplicación?)";
            } else if (err.name === 'OverconstrainedError') {
                errorMessage = "Configuración de cámara no compatible. Intenta con otro navegador.";
            } else if (err.message.includes('Permission dismissed')) {
                errorMessage = "Debes permitir el acceso a la cámara para escanear códigos QR";
            }
            
            showNotification(errorMessage, "error");
            
            // Try basic configuration as fallback
            if (err.name !== 'NotAllowedError' && !scanning) {
                try {
                    await startScanner(null, { video: true });
                } catch (fallbackErr) {
                    console.error("Fallback mode error:", fallbackErr);
                }
            }
        }
    });
  
    // Switch Camera Button
    switchCameraBtn.addEventListener("click", async () => {
        if (!devices || devices.length < 2) return;
        
        try {
            // Get current camera index
            const currentIndex = devices.findIndex(device => device.id === cameraId);
            const nextIndex = (currentIndex + 1) % devices.length;
            cameraId = devices[nextIndex].id;
            
            // Restart scanner with new camera
            await stopScanner();
            await startScanner(cameraId);
            
            showNotification(`Cambiado a cámara: ${devices[nextIndex].label || 'Cámara ' + (nextIndex + 1)}`);
        } catch (err) {
            console.error("Error switching camera:", err);
            showNotification("Error al cambiar de cámara", "error");
        }
    });
  
    // Close Search Modal
    closeSearchModal.addEventListener("click", () => {
        searchUserModal.style.display = "none";
        document.body.style.overflow = "auto";
    });
  
    // Select User from Search Results
    const selectUserBtns = document.querySelectorAll(".select-user-btn");
    selectUserBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            const row = this.closest("tr");
            const userName = row.cells[1].textContent;
            const userEmail = row.cells[2].textContent;
    
            // Fill form with selected user data
            document.getElementById("nombre").value = userName;
            document.getElementById("correo").value = userEmail;
    
            // Close modal
            searchUserModal.style.display = "none";
            document.body.style.overflow = "auto";
    
            // Show success notification
            showNotification("Usuario seleccionado correctamente");
        });
    });
  
    // Clear Form Button
    clearFormBtn.addEventListener("click", clearForm);
  
    // Decrease Raciones Button
    decreaseRacionesBtn.addEventListener("click", () => {
        const currentValue = Number.parseInt(racionesInput.value);
        if (currentValue > 1) {
            racionesInput.value = currentValue - 1;
        }
    });
  
    // Increase Raciones Button
    increaseRacionesBtn.addEventListener("click", () => {
        const currentValue = Number.parseInt(racionesInput.value);
        if (currentValue < 10) {
            racionesInput.value = currentValue + 1;
        }
    });
  
    // Form Submission
    reservationForm.addEventListener("submit", (e) => {
        e.preventDefault();
    
        // Generate random reservation code
        const randomCode = `CC-${Math.floor(Math.random() * 10000)
            .toString()
            .padStart(4, "0")}`;
        document.getElementById("reservation-code").textContent = randomCode;
    
        // Show success modal
        successModal.style.display = "flex";
        document.body.style.overflow = "hidden";
    });
  
    // Print Reservation Button
    printReservationBtn.addEventListener("click", () => {
        window.print();
    });
  
    // New Reservation Button
    newReservationBtn.addEventListener("click", () => {
        // Close modal and clear form
        successModal.style.display = "none";
        document.body.style.overflow = "auto";
        clearForm();
    });
  
    // Close modals when clicking outside
    window.addEventListener("click", (event) => {
        if (event.target === searchUserModal) {
            searchUserModal.style.display = "none";
            document.body.style.overflow = "auto";
        }
        if (event.target === successModal) {
            successModal.style.display = "none";
            document.body.style.overflow = "auto";
        }
    });
  
    // Function to start QR scanner
    async function startScanner(cameraId, constraints = null) {
        const qrConfig = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
            rememberLastUsedCamera: true
        };
  
        try {
            if (constraints) {
                // Compatibility mode for browsers that don't support camera enumeration well
                await html5QrCode.start(
                    constraints,
                    qrConfig,
                    onScanSuccess,
                    onScanFailure
                );
            } else {
                // Normal mode with camera ID
                await html5QrCode.start(
                    cameraId,
                    qrConfig,
                    onScanSuccess,
                    onScanFailure
                );
            }
  
            scanning = true;
            startScanBtn.disabled = true;
            switchCameraBtn.disabled = devices.length <= 1;
            scanStatus.classList.add("scanning");
            scanStatus.querySelector(".status-text").textContent = "Escaneando...";
            
            showNotification("Cámara iniciada correctamente");
        } catch (err) {
            console.error("Error starting scanner", err);
            
            // Try more basic configuration as fallback
            if (!constraints && err.message.includes('Could not start video stream')) {
                showNotification("Intentando modo de compatibilidad...", "warning");
                await startScanner(null, { video: true });
                return;
            }
            
            let errorMsg = "Error al iniciar el escáner";
            if (err.message.includes('No camera found')) {
                errorMsg = "No se encontró cámara compatible";
            }
            showNotification(errorMsg, "error");
        }
    }
  
    // Function to stop QR scanner
    async function stopScanner() {
        try {
            if (html5QrCode && scanning) {
                await html5QrCode.stop();
                scanning = false;
                startScanBtn.disabled = false;
                scanStatus.classList.remove("scanning", "success", "error");
                scanStatus.querySelector(".status-text").textContent = "Listo para escanear";
            }
        } catch (err) {
            console.error("Error stopping scanner:", err);
        }
    }
  
    // Function to handle successful QR scan
    function onScanSuccess(decodedText, decodedResult) {
        // Pause scanning after successful scan
        if (html5QrCode && html5QrCode.getState() !== Html5Qrcode.SCANNING_STATE_PAUSED) {
            html5QrCode.pause(true);
        }
    
        // Update scan status UI
        scanStatus.classList.remove("scanning", "error");
        scanStatus.classList.add("success");
        scanStatus.querySelector(".status-text").textContent = "¡Código QR detectado!";
    
        // Show success notification
        showNotification("Código QR escaneado correctamente");
    
        try {
            // Parse the QR code data (assuming it's JSON)
            const userData = JSON.parse(decodedText);
    
            // Fill the form with user data
            if (userData.nombre) {
                document.getElementById("nombre").value = userData.nombre;
            }
            if (userData.correo) {
                document.getElementById("correo").value = userData.correo;
            }
            if (userData.direccion) {
                document.getElementById("direccion").value = userData.direccion;
            }
            if (userData.sexo) {
                document.getElementById("sexo").value = userData.sexo.toLowerCase();
            }
            if (userData.edad) {
                document.getElementById("edad").value = userData.edad;
            }
    
            // Resume scanning after 3 seconds
            setTimeout(() => {
                if (html5QrCode && html5QrCode.getState() === Html5Qrcode.SCANNING_STATE_PAUSED) {
                    html5QrCode.resume();
                }
                scanStatus.classList.remove("success");
                scanStatus.classList.add("scanning");
                scanStatus.querySelector(".status-text").textContent = "Escaneando...";
            }, 3000);
        } catch (error) {
            console.error("Error parsing QR code data", error);
            showNotification("Error al procesar los datos del código QR", "error");
    
            // Resume scanning after 2 seconds on error
            setTimeout(() => {
                if (html5QrCode && html5QrCode.getState() === Html5Qrcode.SCANNING_STATE_PAUSED) {
                    html5QrCode.resume();
                }
                scanStatus.classList.remove("success");
                scanStatus.classList.add("scanning");
                scanStatus.querySelector(".status-text").textContent = "Escaneando...";
            }, 2000);
        }
    }
  
    // Function to handle scan failure
    function onScanFailure(error) {
        console.log("QR code scan failed", error);
        // Don't show notification for every failure to avoid spamming user
    }
  
    // Function to clear form
    function clearForm() {
        document.getElementById("nombre").value = "";
        document.getElementById("correo").value = "";
        document.getElementById("direccion").value = "";
        document.getElementById("edad").value = "";
        document.getElementById("sexo").value = "";
        document.getElementById("raciones").value = "1";
        
        // Also stop scanner if active
        if (scanning) {
            stopScanner();
        }
    }
  
    // Function to format date as DD/MM/YYYY
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }
  
    // Function to show notifications
    function showNotification(message, type = "success") {
        notificationMessage.textContent = message;
        statusNotification.className = "toast";
        
        if (type === "error") {
            statusNotification.style.backgroundColor = "var(--error-color)";
        } else if (type === "warning") {
            statusNotification.style.backgroundColor = "var(--warning-color)";
        } else {
            statusNotification.style.backgroundColor = "var(--success-color)";
        }
    
        statusNotification.style.display = "block";
        setTimeout(() => {
            statusNotification.style.display = "none";
        }, 3000);
    }
  
    // Clean up on page unload
    window.addEventListener("beforeunload", () => {
        if (scanning) {
            stopScanner();
        }
    });
  });