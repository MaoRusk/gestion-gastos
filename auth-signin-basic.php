<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is already logged in, if yes then redirect him to dashboard
if (isLoggedIn()) {
    header("location: dashboard-gastos.php");
    exit;
}

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = "";
$login_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor ingresa tu email.";
    } else {
        $email = trim($_POST["email"]);
        if (!validateEmail($email)) {
            $email_err = "Por favor ingresa un email válido.";
        }
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingresa tu contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            // Redirect to dashboard
            header("location: dashboard-gastos.php");
            exit;
        } else {
            $login_message = $result['message'];
        }
    }
}

?>
<?php include 'layouts/head-main.php'; ?>

    <head>
        
        <title>Iniciar Sesión | FIME - Gestión de Gastos Personales</title>
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
                                        <img src="assets/images/fime.png" alt="" height="82">
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
                                        <!-- <h5 class="text-primary">¡Bienvenido de vuelta!</h5> -->
                                        <!-- <p class="text-muted">Inicia sesión para continuar a tu panel de gastos.</p> -->
                                    </div>
                                    <div class="p-2 mt-4">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
                                            <?php if (!empty($_GET) && isset($_GET['registered']) && $_GET['registered'] == 1): ?>
                                                <div class="alert alert-success" role="alert">
                                                    Registro exitoso. Ya puedes iniciar sesión.
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($login_message)): ?>
                                                <div class="alert alert-danger" role="alert">
                                                    <?php echo $login_message; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3 <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" value="<?php echo $email; ?>" name="email" id="email" placeholder="Ingresa tu email">
                                                <span class="text-danger"><?php echo $email_err; ?></span>
                                            </div>
                    
                                            <div class="mb-3 <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                                                <!-- <div class="float-end">
                                                    <a href="auth-pass-reset-basic.php" class="text-muted">¿Olvidaste tu contraseña?</a>
                                                </div> -->
                                                <label class="form-label" for="password-input">Contraseña</label>
                                                <div class="position-relative auth-pass-inputgroup mb-3">
                                                    <input type="password" class="form-control pe-5 password-input" name="password" placeholder="Ingresa tu contraseña" id="password-input">
                                                    <span class="text-danger"><?php echo $password_err; ?></span>
                                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                                </div>
                                            </div>

                                            <!-- <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="auth-remember-check">
                                                <label class="form-check-label" for="auth-remember-check">Recordarme</label>
                                            </div> -->
                                            
                                            <div class="mt-4">
                                                <button class="btn btn-success w-100" type="submit">Iniciar Sesión</button>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->

                            <div class="mt-4 text-center">
                                <p class="mb-0">¿No tienes una cuenta? <a href="auth-signup-basic.php" class="fw-semibold text-primary text-decoration-underline"> Regístrate </a> </p>
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
                                <!-- <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p> -->
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
        <!-- password-addon init -->
        <script src="assets/js/pages/password-addon.init.js"></script>
    </body>

</html>