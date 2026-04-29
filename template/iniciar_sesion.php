<?php
// Configuración simple
$host = 'db';
$user = 'usuario';
$pass = '12345';
$db = 'proyectoFinal';

// Conexión (con manejo básico de error)
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die("Conexión fallida: " . $mysqli->connect_error);

// Mensaje para mostrar al usuario
$mensaje = '';

// Variables
// Usuario
$correo = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = clean($_POST["mail"]);
    $password = clean($_POST["contra"]);
}

function clean($data)
{
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = "SELECT id, password FROM usuario WHERE email = ?;";
    $result = $mysqli->execute_query($query, [$correo]);
    $result = $result->fetch_assoc();

    if ($result) {
        if (password_verify($password, $result['password'])) {
            session_start();
            $_SESSION["id"] = $result['id'];

            header("Location: inicio.php?msg=login_ok");
            exit();
        } else {
            $mensaje = "Contraseña incorrecta";
        }
    } else {
        $mensaje = "El correo ingresado no está asociado a ninguna cuenta";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto Final - Inicio de sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container px-4 px-lg-5">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="inicio.php" aria-current="page">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="nav-link" href="crear_usuario.php">Registrarse</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Header-->
    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Iniciar sesión</h1>
                <p class="lead fw-normal text-white-50 mb-0">Si aún no tienes una cuenta</p>
                <p class="lead fw-normal text-white-50 mb-0"><a href="crear_usuario.php">Da click aquí</a></p>
            </div>
        </div>
    </header>

    <!-- Section-->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <form method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="mail" class="form-label fw-bold">Correo electrónico:</label><br>
                                    <input type="text" class="form-control" name="mail" id="mail" placeholder="Ej. david@gmail.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contra" class="form-label fw-bold">Contraseña:</label><br>
                                    <input type="password" class="form-control" name="contra" id="contra" placeholder="***********" required>
                                </div>
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-dark text-white">Iniciar sesión</button>
                                    <button type="reset" class="btn btn-outline-dark">Limpiar Formulario</button>
                                </div>
                            </form>
                        </div>
                        <?php if ($mensaje): ?>
                            <div class="card-body p-4 bg-danger text-white">
                                <?= $mensaje?>
                                <!--<?= htmlspecialchars($mensaje) ?>-->
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer-->
    <footer class="py-5 bg-dark">
        <div class="container">
            <p class="m-0 text-center text-white">Copyright &copy; Your Website 2023</p>
        </div>
    </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
</body>

</html>