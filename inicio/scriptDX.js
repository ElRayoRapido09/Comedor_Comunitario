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
  
  // Función para mostrar/ocultar contraseña (mejorada con iconos SVG)
document.getElementById('toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('contrasena');
    const button = this;
    const eyeOpen = button.querySelector('.eye-open');
    const eyeClosed = button.querySelector('.eye-closed');
    
    // Agregar clase de animación
    button.classList.add('changing');
    
    // Cambiar el tipo de input y mostrar/ocultar iconos
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        button.setAttribute('data-state', 'visible');
        button.setAttribute('aria-label', 'Ocultar contraseña');
        
        // Mostrar ojo cerrado (contraseña visible)
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        passwordInput.type = 'password';
        button.setAttribute('data-state', 'hidden');
        button.setAttribute('aria-label', 'Mostrar contraseña');
        
        // Mostrar ojo abierto (contraseña oculta)
        eyeClosed.style.display = 'none';
        eyeOpen.style.display = 'block';
    }
    
    // Remover clase de animación después de la transición
    setTimeout(() => {
        button.classList.remove('changing');
    }, 300);
});

// Asegurar que el botón tenga el estado inicial correcto
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('contrasena');
    
    if (toggleButton && passwordInput) {
        // Estado inicial: contraseña oculta, mostrar ojo abierto
        toggleButton.setAttribute('data-state', 'hidden');
        toggleButton.setAttribute('aria-label', 'Mostrar contraseña');
        
        const eyeOpen = toggleButton.querySelector('.eye-open');
        const eyeClosed = toggleButton.querySelector('.eye-closed');
        
        if (eyeOpen && eyeClosed) {
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        }
    }
});