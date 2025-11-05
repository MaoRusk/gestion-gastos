<?php
// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is already logged in, if yes then redirect him to dashboard
if (isLoggedIn()) {
    header("location: dashboard-gastos.php");
    exit;
}

// Define variables and initialize with empty values
$nombre = $email = $password = $confirm_password = $telefono = $fecha_nacimiento = "";
$nombre_err = $email_err = $password_err = $confirm_password_err = "";
$register_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingresa tu nombre.";
    } else {
        $nombre = sanitizeInput($_POST["nombre"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor ingresa tu email.";
    } elseif (!validateEmail($_POST["email"])) {
        $email_err = "Formato de email inválido";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingresa una contraseña.";
    } elseif (!validatePassword($_POST["password"])) {
        $password_err = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Por favor confirma tu contraseña.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }

    // Optional fields
    $telefono = !empty($_POST["telefono"]) ? sanitizeInput($_POST["telefono"]) : null;
    $fecha_nacimiento = !empty($_POST["fecha_nacimiento"]) ? $_POST["fecha_nacimiento"] : null;

    // Check input errors before inserting in database
    if (empty($nombre_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        $result = registerUser($nombre, $email, $password, $telefono, $fecha_nacimiento);
        
        if ($result['success']) {
            $register_message = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            // Clear form
            $nombre = $email = $password = $confirm_password = $telefono = $fecha_nacimiento = "";
        } else {
            $register_message = $result['message'];
        }
    }
}
?>
<?php include 'layouts/head-main.php'; ?>

    <head>
        
        <title>Registro | FIME - Gestión de Gastos Personales</title>
        <?php include 'layouts/title-meta.php'; ?>

        <?php include 'layouts/head-css.php'; ?>

    </head>

    <?php include 'layouts/body.php'; ?>

        <div class="auth-page-wrapper pt-5">
            <!-- auth page bg -->
            <div class="auth-one-bg-position auth-one-bg"  id="auth-particles">
                <div class="bg-overlay"></div>
                
                <div class="shape">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                        <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                    </svg>
                </div>
            </div>

            <!-- auth page content -->
            <div class="auth-page-content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center mt-sm-5 mb-4 text-white-50">
                                <div>
                                    <a href="index.php" class="d-inline-block auth-logo">
                                        <img src="assets/images/logo-light.png" alt="" height="20">
                                    </a>
                                </div>
                                <p class="mt-3 fs-15 fw-medium">Sistema de Gestión de Gastos Personales</p>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->

                    <div class="row justify-content-center">
                        <div class="col-md-8 col-lg-6 col-xl-5">
                            <div class="card mt-4">
                            
                                <div class="card-body p-4"> 
                                    <div class="text-center mt-2">
                                        <h5 class="text-primary">Crear Nueva Cuenta</h5>
                                        <p class="text-muted">Obtén tu cuenta gratuita de gestión de gastos</p>
                                    </div>
                                    <div class="p-2 mt-4">
                                        <form class="needs-validation" novalidate action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
                                            <?php if (!empty($register_message)): ?>
                                                <div class="alert <?php echo strpos($register_message, 'exitoso') !== false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                                    <?php echo $register_message; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3 <?php echo (!empty($nombre_err)) ? 'has-error' : ''; ?>">
                                                <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="nombre" value="<?php echo $nombre; ?>" id="nombre" placeholder="Ingresa tu nombre completo" required>
                                                <span class="text-danger"><?php echo $nombre_err; ?></span>
                                                <div class="invalid-feedback">
                                                    Por favor ingresa tu nombre
                                                </div>
                                            </div>

                                            <div class="mb-3 <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="email" value="<?php echo $email; ?>" id="email" placeholder="Ingresa tu email" required>
                                                <span class="text-danger"><?php echo $email_err; ?></span>
                                                <div class="invalid-feedback">
                                                    Por favor ingresa un email válido
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" name="telefono" value="<?php echo $telefono; ?>" id="telefono" placeholder="Ingresa tu teléfono (opcional)">
                                            </div>

                                            <div class="mb-3">
                                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                                <input type="date" class="form-control" name="fecha_nacimiento" value="<?php echo $fecha_nacimiento; ?>" id="fecha_nacimiento">
                                            </div>

                                            <div class="mb-3 <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                                                <label class="form-label" for="password-input">Contraseña <span class="text-danger">*</span></label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5 password-input" name="password" value="<?php echo $password; ?>" placeholder="Ingresa tu contraseña" id="password-input" required>
                                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                                    <span class="text-danger"><?php echo $password_err; ?></span>
                                                </div>
                                            </div>

                                            <div class="mb-3 <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                                                <label class="form-label" for="confirm-password-input">Confirmar Contraseña <span class="text-danger">*</span></label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5 password-input" name="confirm_password" value="<?php echo $confirm_password; ?>" placeholder="Confirma tu contraseña" id="confirm-password-input" required>
                                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="confirm-password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                                    <span class="text-danger"><?php echo $confirm_password_err; ?></span>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <p class="mb-0 fs-12 text-muted fst-italic">Al registrarte aceptas los <a href="#" class="text-primary text-decoration-underline fst-normal fw-medium">Términos de Uso</a> del sistema</p>
                                            </div>

                                            <div class="mt-4">
                                                <button class="btn btn-success w-100" type="submit">Registrarse</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->

                            <div class="mt-4 text-center">
                                <p class="mb-0">¿Ya tienes una cuenta? <a href="auth-signin-basic.php" class="fw-semibold text-primary text-decoration-underline"> Iniciar Sesión </a> </p>
                            </div>

                        </div>
                    </div>
                    <!-- end row -->
                </div>
                <!-- end container -->
            </div>
            <!-- end auth page content -->

            <!-- footer -->
            <footer class="footer">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->
        </div>
        <!-- end auth-page-wrapper -->

        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- particles js -->
        <script src="assets/libs/particles.js/particles.js"></script>
        <!-- particles app js -->
        <script src="assets/js/pages/particles.app.js"></script>
        <!-- validation init -->
        <script src="assets/js/pages/form-validation.init.js"></script>
        <!-- password create init -->
        <script src="assets/js/pages/passowrd-create.init.js"></script>
    </body>

</html>