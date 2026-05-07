<?php
// Configuración simple
$host = 'db';
$user = 'usuario';
$pass = '12345';
$db = 'proyectoFinal';

// Conexión (con manejo básico de error)
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die("Conexión fallida: " . $mysqli->connect_error);

// Credenciales del usuario
$correo = $password = "";

// Limpieza de las credenciales
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
    // Query para conseguir el id del usuario 
    $query = "SELECT id, password FROM usuario WHERE email = ?;";
    $result = $mysqli->execute_query($query, [$correo]);
    $result = $result->fetch_assoc();

    // Lógica para verificar si existe el usuario
    if ($result) {
        // Lógica para verificar la contraseña
        if (password_verify($password, $result['password'])) {
            session_start();
            $_SESSION["id"] = $result['id'];

            // Lógica para definir el tipo de usuario
            if($_SESSION["id"] == 1){
                header("Location: catalogo.php?msg=login_ok");
                exit();
            }

            header("Location: inicio.php?msg=login_ok");
            exit();
        } else {
            header("Location: iniciar_sesion.php?msg=error_pw");
            exit();
        }
    } else {
        header("Location: iniciar_sesion.php?msg=error_mail");
        exit();
    }
}

session_start();
include "usuario.php";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Proyecto Final - Inicio de sesión</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light bg-secondary sticky-top">
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
            <div class="mt-3 mb-4">
                <?php include 'alertas.php'; ?>
            </div>
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
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer-->
    <?php include "footer.php" ?>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
</body>

</html>