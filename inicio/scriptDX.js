document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Limpiar mensajes anteriores
    clearErrors();
    
    // Obtener valores
    const correo = document.getElementById('correo').value.trim();
    const contrasena = document.getElementById('contrasena').value.trim();
    const tipoUsuario = document.querySelector('input[name="tipo-usuario"]:checked').value;
    const recordar = document.getElementById('recordar').checked;
    
    // Validación frontend
    if (!validateInputs(correo, contrasena)) return;
    
    // Mostrar loading
    const submitBtn = document.querySelector('.login-button');
    toggleLoading(submitBtn, true);
    
    try {
      const response = await fetch('validar_login.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({correo, contrasena, tipoUsuario, recordar})
      });
      
      const data = await response.json();
      
      if (data.success) {
        redirectUser(data.tipo_usuario);
      } else {
        showError(data.message, data.debug);
      }
    } catch (error) {
      showError('Error de conexión con el servidor');
    } finally {
      toggleLoading(submitBtn, false);
    }
  });
  
  // Funciones auxiliares
  function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
      el.textContent = '';
      el.classList.remove('show');
    });
  }
  
  function validateInputs(correo, contrasena) {
    let isValid = true;
    
    if (!correo) {
      showFieldError('correo-error', 'Ingrese su correo electrónico');
      isValid = false;
    }
    
    if (!contrasena) {
      showFieldError('contrasena-error', 'Ingrese su contraseña');
      isValid = false;
    }
    
    return isValid;
  }
  
  function showFieldError(id, message) {
    const element = document.getElementById(id);
    element.textContent = message;
    element.classList.add('show');
  }
  
  function toggleLoading(button, isLoading) {
    if (isLoading) {
      button.innerHTML = '<span class="loading-spinner"></span> Validando...';
      button.disabled = true;
    } else {
      button.textContent = 'Ingresar';
      button.disabled = false;
    }
  }
  
  function redirectUser(tipoUsuario) {
    if (tipoUsuario === 'beneficiario') {
      window.location.href = '../menu/menu.php';
    } else {
      window.location.href = '../empleado/empleado.php';
    }
  }
  
  function showError(message, debug = null) {
    if (message.includes('contraseña')) {
      showFieldError('contrasena-error', message);
    } else if (message.includes('Usuario') || message.includes('correo')) {
      showFieldError('correo-error', message);
    } else {
      const messageContainer = document.getElementById('form-messages');
      messageContainer.innerHTML = `<div class="error-message show">${message}</div>`;
    }
    
    if (debug) console.debug('Error debug:', debug);
  }
  
  // Toggle password visibility
  // Reemplaza la función existente del toggle-password
// Función para mostrar/ocultar contraseña (mejorada)
document.getElementById('toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('contrasena');
    const eyeIcon = this.querySelector('.eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('closed');
        // Animación de apertura
        eyeIcon.style.transform = 'scaleY(1)';
        eyeIcon.style.opacity = '1';
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.add('closed');
        // Animación de cierre
        eyeIcon.style.transform = 'scaleY(0.5) translateY(1px)';
        eyeIcon.style.opacity = '0.8';
    }
  });
  
  // Asegurar que el ojo empiece cerrado al cargar la página
  document.addEventListener('DOMContentLoaded', function() {
    const eyeIcon = document.querySelector('.eye-icon');
    if (eyeIcon && document.getElementById('contrasena').type === 'password') {
        eyeIcon.classList.add('closed');
    }
  });