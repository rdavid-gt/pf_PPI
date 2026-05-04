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
$nombre = $apellidos = $email = $password = $tarjeta = $nac = "";

// Domicilio
$calle = $colonia = $next = $nint = $pc = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = clean($_POST["nombre"]);
    $apellidos = clean($_POST["apellidos"]);
    $email = clean($_POST["email"]);
    $password = clean($_POST["contra"]);
    $password = password_hash($password, PASSWORD_DEFAULT);
    $tarjeta = clean($_POST["tarjeta"]);
    $nac = clean($_POST["nac"]);
    $calle = clean($_POST["calle"]);
    $colonia = clean($_POST["colonia"]);
    $next = clean($_POST["next"]);
    $nint = clean($_POST["nint"]);
    $pc = clean($_POST["cp"]);
}

function clean($data){
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer binario y preparar inserción
    $stmt = $mysqli->prepare("INSERT INTO usuario (nombre, apellido, password, email, f_nac, tarjeta, calle, colonia, n_exterior, n_interior, cp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $mensaje = "Error en la preparación: " . $mysqli->error;
    } else {
        $stmt->bind_param("ssssssssiis", $nombre, $apellidos, $password, $email, $nac, $tarjeta, $calle, $colonia, $next, $nint, $pc);
        if ($stmt->execute()) {
            $id = $mysqli->insert_id;
            $mensaje = "Cliente guardado con ID: " . $id;
            $stmt1 = $mysqli->prepare("INSERT INTO carrito_cliente (idUsuario) VALUES (?)");
            if (!$stmt1) {
                $mensaje = "Error en la preparación del carrito: " . $mysqli->error;
            } else {
                $stmt1->bind_param("i", $id);
                if ($stmt1->execute()) {
                    $mensaje = $mensaje . "y carrito guardado con ID: " . $mysqli->insert_id;
                    session_start();

                    $_SESSION["id"] = $id['id'];

                    header("Location: inicio.php");
                    exit();
                }else{
                    $mensaje = "Error al guardar carrito: " . $stmt1->error;
                }
                $stmt1->close();
            }
        } else {
            $mensaje = "Error al guardar cliente: " . $stmt->error;
        }
    }
        
    $stmt->close();
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
    <title>Proyecto Final - Registro de cliente</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light bg-secondary fixed-top">
        <div class="container px-4 px-lg-5">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="inicio.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="iniciar_sesion.php">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page">Registrarse</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Header-->
    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Registro</h1>
                <p class="lead fw-normal text-white-50 mb-0">Aquí se da de alta su cuenta</p>
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
                                <h4>Cliente</h4>
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre(s):</label><br>
                                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej. Ricardo David" required>
                                </div>
                                <div class="mb-3">
                                    <label for="apellidos" class="form-label fw-bold">Apellidos:</label><br>
                                    <input type="text" class="form-control" name="apellidos" id="apellidos" placeholder="Ej. García Trabanco" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">Correo electrónico:</label><br>
                                    <input type="mail" class="form-control" name="email" id="email" placeholder="Ej. david@gmail.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contra" class="form-label fw-bold">Contraseña:</label><br>
                                    <input type="password" class="form-control" name="contra" id="contra" minlength="6" maxlength="20" placeholder="***********" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tarjeta" class="form-label fw-bold">Tarjeta TDD/TDC:</label><br>
                                    <input type="text" class="form-control" name="tarjeta" id="tarjeta" minlength="16" maxlength="16" placeholder="Ej. 1234 5678 9012 3456" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nac" class="form-label fw-bold">Fecha de nacimiento:</label><br>
                                    <input type="date" class="form-control" name="nac" id="nac" required>
                                </div>
                                <h4>Domicilio de entrega</h4>
                                <div class="mb-3">
                                    <label for="calle" class="form-label fw-bold">Calle:</label><br>
                                    <input type="text" class="form-control" name="calle" id="calle" placeholder="Ej. Calle Canario" required>
                                </div>
                                <div class="mb-3">
                                    <label for="colonia" class="form-label fw-bold">Colonia:</label><br>
                                    <input type="text" class="form-control" name="colonia" id="colonia" placeholder="Ej. Miguel Hidalgo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="next" class="form-label fw-bold">Número exterior:</label><br>
                                    <input type="number" class="form-control" name="next" id="next" placeholder="Ej. 123" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nint" class="form-label fw-bold">Número interior:</label><br>
                                    <input type="number" class="form-control" name="nint" id="nint" placeholder="Ej. 123" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cp" class="form-label fw-bold">Código Postal:</label><br>
                                    <input type="text" class="form-control" name="cp" id="cp" placeholder="Ej. 53200" required>
                                </div>
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-dark text-white">Registrarse</button>
                                    <button type="reset" class="btn btn-outline-dark">Limpiar Formulario</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($mensaje): ?>
        <div class="container mt-2">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 bg-success text-white">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer-->
    <?php include "footer.php" ?>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>

</body>

</html>