document.addEventListener('DOMContentLoaded', function () {
    // Funcionalidad para mostrar/ocultar contraseña
    const passwordToggle = document.querySelector('.password-toggle-ls');
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function () {
            const passwordInput = document.querySelector('#password');
            const icon = this;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    // Manejo del formulario de login
    const loginForm = document.querySelector('.form-ls');
    const loadingSpinner = document.querySelector('.loading-ls');

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            // Mostrar spinner de carga
            if (loadingSpinner) {
                loadingSpinner.style.display = 'flex';
            }

            // El formulario se enviará normalmente
            // El spinner se ocultará cuando la página se recargue
        });
    }
});
