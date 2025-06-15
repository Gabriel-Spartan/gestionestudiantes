<?php
$pageTitle = "Iniciar Sesión";
include 'includes/header.php';
include 'includes/nav.php';

// Inicializar variable de error
$error = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Aquí iría la lógica de validación y autenticación
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Por ahora solo mostraremos un error de ejemplo
    $error = 'Usuario o contraseña incorrectos';
}
?>

<div class="container-ls">
    <div class="card-ls">
        <!-- Mensaje de error -->
        <?php if ($error): ?>
            <div class="error-ls">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Encabezado -->
        <div class="header-ls">
            <h1 class="title-ls">Bienvenido</h1>
            <p class="subtitle-ls">Ingresa a tu cuenta para continuar</p>
        </div>

        <!-- Formulario de login -->
        <form method="POST" action="login.php" class="form-ls">
            <div class="group-ls">
                <label for="username" class="label-ls">Usuario</label>
                <input type="text" id="username" name="username" class="input-ls" placeholder="Ingresa tu usuario"
                    required>
            </div>

            <div class="group-ls">
                <label for="password" class="label-ls">Contraseña</label>
                <input type="password" id="password" name="password" class="input-ls"
                    placeholder="Ingresa tu contraseña" required>
                <i class="fas fa-eye password-toggle-ls"></i>
            </div> <button type="submit" class="button-ls">
                Iniciar Sesión
            </button>
        </form>

        <!-- Spinner de carga (oculto por defecto) -->
        <div class="loading-ls" style="display: none;">
            <div class="spinner-ls"></div>
        </div>
    </div>
</div>

<!-- Scripts específicos de login -->
<script src="/assets/js/login.js"></script>

<?php include 'includes/footer.php'; ?>