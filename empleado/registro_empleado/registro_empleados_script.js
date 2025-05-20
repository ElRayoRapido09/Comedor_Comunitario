document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registro-empleado-form")
  const passwordInput = document.getElementById("contrasena")
  const confirmPasswordInput = document.getElementById("confirmar-contrasena")
  const successMessage = document.getElementById("success-message")
  const closeModalBtn = document.getElementById("close-modal")

  // Form validation and submission
  form.addEventListener("submit", async (e) => {
      e.preventDefault()

      let isValid = true
      const formData = new FormData(form)

      // Reset all error messages
      document.querySelectorAll(".error-message").forEach((el) => {
          el.style.display = "none"
      })

      // Validate name
      const nombre = document.getElementById("nombre").value.trim()
      if (nombre === "") {
          showError("nombre-error", "Por favor, ingrese el nombre")
          isValid = false
      }

      // Validate last name
      const apellidos = document.getElementById("apellidos").value.trim()
      if (apellidos === "") {
          showError("apellidos-error", "Por favor, ingrese los apellidos")
          isValid = false
      }

      // Validate address
      const direccion = document.getElementById("direccion").value.trim()
      if (direccion === "") {
          showError("direccion-error", "Por favor, ingrese la dirección")
          isValid = false
      }

      // Validate age
      const edad = document.getElementById("edad").value.trim()
      if (edad === "" || edad < 18) {
          showError("edad-error", "El empleado debe ser mayor de 18 años")
          isValid = false
      }

      // Validate email
      const correo = document.getElementById("correo").value.trim()
      if (correo === "" || !isValidEmail(correo)) {
          showError("correo-error", "Por favor, ingrese un correo electrónico válido")
          isValid = false
      }

      // Validate password
      const password = passwordInput.value
      if (password === "") {
          showError("contrasena-error", "Por favor, ingrese una contraseña")
          isValid = false
      } else if (password.length < 8) {
          showError("contrasena-error", "La contraseña debe tener al menos 8 caracteres")
          isValid = false
      }

      // Validate confirm password
      const confirmPassword = confirmPasswordInput.value
      if (confirmPassword === "") {
          showError("confirmar-contrasena-error", "Por favor, confirme la contraseña")
          isValid = false
      } else if (confirmPassword !== password) {
          showError("confirmar-contrasena-error", "Las contraseñas no coinciden")
          isValid = false
      }

      // Validate terms
      const terminos = document.getElementById("terminos").checked
      if (!terminos) {
          showError("terminos-error", "Debe aceptar los términos y condiciones")
          isValid = false
      }

      // If form is valid, submit to server
      if (isValid) {
          try {
              const response = await fetch('registrar_empleado.php', {
                  method: 'POST',
                  body: formData
              })
              
              const result = await response.json()
              
              if (result.success) {
                  successMessage.classList.add("show")
              } else {
                  // Mostrar errores del servidor
                  result.errors.forEach(error => {
                      // Mostrar el primer error en un lugar visible o manejar cada tipo de error
                      alert(error) // Esto es temporal, puedes mejorarlo
                  })
              }
          } catch (error) {
              console.error("Error:", error)
              alert("Ocurrió un error al enviar el formulario")
          }
      }
  })

  // Close modal
  closeModalBtn.addEventListener("click", () => {
      successMessage.classList.remove("show")
      form.reset()
  })

  // Helper functions
  function showError(id, message) {
      const errorElement = document.getElementById(id)
      if (errorElement) {
          errorElement.textContent = message
          errorElement.style.display = "block"
      } else {
          // Si no existe el elemento de error, lo creamos (opcional)
          const input = document.querySelector(`[aria-describedby="${id}"]`)
          if (input) {
              const errorDiv = document.createElement('div')
              errorDiv.id = id
              errorDiv.className = 'error-message'
              errorDiv.textContent = message
              errorDiv.style.display = 'block'
              input.parentNode.insertBefore(errorDiv, input.nextSibling)
          }
      }
  }

  function isValidEmail(email) {
      const re =
          /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      return re.test(email.toLowerCase())
  }
})