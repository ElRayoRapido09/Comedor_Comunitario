console.log("Script de recuperación cargado.");
document.addEventListener("DOMContentLoaded", () => {
  // Elementos del DOM
  const recoveryForm = document.getElementById("recovery-form");
  const verificationForm = document.getElementById("verification-form");
  const newPasswordForm = document.getElementById("new-password-form");
  const verificationStep = document.getElementById("verification-step");
  const newPasswordStep = document.getElementById("new-password-step");
  const successMessage = document.getElementById("success-message");
  const closeSuccessBtn = document.getElementById("close-success");
  const passwordInput1 = document.getElementById("nueva-contrasena");
  const passwordInput2 = document.getElementById("confirmar-contrasena");
  const togglePasswordBtn1 = document.getElementById("toggle-password-1");
  const togglePasswordBtn2 = document.getElementById("toggle-password-2");
  const verificationInputs = document.querySelectorAll(".verification-input");
  const resendBtn = document.getElementById("resend-btn");
  const strengthMeter = document.querySelector(".strength-meter");
  const strengthText = document.querySelector(".strength-text");
  const strengthSegments = document.querySelectorAll(".strength-segment");

  // Mostrar/ocultar contraseña
  togglePasswordBtn1.addEventListener("click", () => togglePasswordVisibility(passwordInput1, togglePasswordBtn1));
  togglePasswordBtn2.addEventListener("click", () => togglePasswordVisibility(passwordInput2, togglePasswordBtn2));

  function togglePasswordVisibility(input, button) {
    const type = input.getAttribute("type") === "password" ? "text" : "password";
    input.setAttribute("type", type);
    button.querySelector(".eye-icon").textContent = type === "password" ? "👁️" : "🔒";
  }

  // Manejo de código de verificación
  verificationInputs.forEach((input, index) => {
    input.addEventListener("input", (e) => {
      if (e.target.value.length === 1 && index < verificationInputs.length - 1) {
        verificationInputs[index + 1].focus();
      }
    });

    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace" && e.target.value === "" && index > 0) {
        verificationInputs[index - 1].focus();
      }
    });
  });

  // Reenviar código
  resendBtn.addEventListener("click", async () => {
    const email = passwordInput1.dataset.email;
    if (!email) {
      showError("codigo-error", "No se encontró el correo electrónico");
      return;
    }

    try {
      const response = await fetch('recuperar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}`
      });

      const data = await response.json();
      if (data.success) {
        resendBtn.textContent = "Enviado";
        resendBtn.disabled = true;
        setTimeout(() => {
          resendBtn.textContent = "Reenviar";
          resendBtn.disabled = false;
        }, 30000);
      } else {
        showError("codigo-error", data.message);
      }
    } catch (error) {
      showError("codigo-error", "Error al conectar con el servidor");
    }
  });

  // Indicador de fortaleza de contraseña
  passwordInput1.addEventListener("input", updatePasswordStrength);

  function updatePasswordStrength() {
    const password = passwordInput1.value;
    const strength = calculatePasswordStrength(password);
    
    strengthSegments.forEach((segment, index) => {
      segment.className = "strength-segment";
      if (index < strength) {
        segment.classList.add(
          strength === 1 ? "weak" :
          strength <= 3 ? "medium" : "strong"
        );
      }
    });

    strengthText.textContent = 
      strength === 0 ? "Seguridad: Débil" :
      strength === 1 ? "Seguridad: Débil" :
      strength === 2 ? "Seguridad: Media" :
      strength === 3 ? "Seguridad: Buena" : "Seguridad: Excelente";
  }

  function calculatePasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^A-Za-z0-9]/)) strength++;
    return strength;
  }

  // Formulario de recuperación
  recoveryForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = document.getElementById("correo").value.trim();

    if (!isValidEmail(email)) {
      showError("correo-error", "Correo electrónico inválido");
      return;
    }

    try {
      const response = await fetch('recuperar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}`
      });

      const data = await response.json();
      if (data.success) {
        passwordInput1.dataset.email = data.email;
        passwordInput2.dataset.email = data.email;
        recoveryForm.closest(".form-card").style.display = "none";
        verificationStep.style.display = "block";
        verificationInputs[0].focus();
      } else {
        showError("correo-error", data.message);
      }
    } catch (error) {
      showError("correo-error", "Error de conexión");
    }
  });

  // Formulario de verificación
  verificationForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    let verificationCode = "";
    let isComplete = true;

    verificationInputs.forEach(input => {
      verificationCode += input.value;
      if (!input.value) isComplete = false;
    });

    if (!isComplete) {
      showError("codigo-error", "Ingrese el código completo");
      return;
    }

    const email = passwordInput1.dataset.email;
    try {
      const response = await fetch('recuperar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `codigo=${encodeURIComponent(verificationCode)}&email=${encodeURIComponent(email)}`
      });

      const data = await response.json();
      if (data.success) {
        verificationStep.style.display = "none";
        newPasswordStep.style.display = "block";
        verificationInputs.forEach(input => input.value = "");
      } else {
        showError("codigo-error", data.message);
      }
    } catch (error) {
      showError("codigo-error", "Error de conexión");
    }
  });

  // Formulario de nueva contraseña
  newPasswordForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const newPassword = passwordInput1.value;
    const confirmPassword = passwordInput2.value;
    const email = passwordInput1.dataset.email;

    if (newPassword.length < 8) {
      showError("nueva-contrasena-error", "Mínimo 8 caracteres");
      return;
    }

    if (newPassword !== confirmPassword) {
      showError("confirmar-contrasena-error", "Las contraseñas no coinciden");
      return;
    }

    try {
      const response = await fetch('recuperar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `nueva-contrasena=${encodeURIComponent(newPassword)}&confirmar-contrasena=${encodeURIComponent(confirmPassword)}&email=${encodeURIComponent(email)}`
      });

      const data = await response.json();
      if (data.success) {
        successMessage.style.display = "flex";
        passwordInput1.value = "";
        passwordInput2.value = "";
      } else {
        showError("nueva-contrasena-error", data.message || "Error al actualizar contraseña");
      }
    } catch (error) {
      showError("nueva-contrasena-error", "Error de conexión");
    }
  });

  // Cerrar mensaje de éxito
  closeSuccessBtn.addEventListener("click", () => {
    successMessage.style.display = "none";
    window.location.href = "../inicio/index.html";
  });

  // Funciones auxiliares
  function showError(id, message) {
    const errorElement = document.getElementById(id);
    errorElement.textContent = message;
    errorElement.style.display = "block";
    setTimeout(() => errorElement.style.display = "none", 3000);
  }

  function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
});