<?php

session_start();
if(isset($_SESSION['id']) && $_SESSION['id'] == 1){
    header("Location: catalogo.php");
    exit();
}

// Configuración simple
$host = 'db';
$user = 'usuario';
$pass = '12345';
$db = 'proyectoFinal';

// Conexión (con manejo básico de error)
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die("Conexión fallida: " . $mysqli->connect_error);

$query = "SELECT * FROM usuario WHERE id = ?";
$resultado = $mysqli->execute_query($query, [$_SESSION["id"]]);
$res = $resultado->fetch_assoc();

$nombre = $res["nombre"];
$apellidos = $res["apellido"];
$email = $res["email"];
$tarjeta = $res["tarjeta"];
$nac = $res["f_nac"];
$calle = $res["calle"];
$colonia = $res["colonia"];
$next = $res["n_exterior"];
$nint = $res["n_interior"];
$cp = $res["cp"];

$query = "SELECT SUM(cantidad) as Cuenta FROM carrito_productos CP, carrito_cliente CC WHERE CC.idUsuario = ? AND CC.id = CP.idCarrito;";
if(isset($_SESSION['id'])){
    $prod_carrito = $mysqli->execute_query($query, [$_SESSION['id']]);
    $prod_carrito = $prod_carrito->fetch_assoc();
    $prod_carrito = $prod_carrito['Cuenta'];

    if(is_null($prod_carrito)){
        $prod_carrito = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto Final - Mi Cuenta</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-secondary fixed-top">
        <div class="container px-4 px-lg-5">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" aria-current="page" href="inicio.php">Inicio</a></li>
                    <?php if(isset($_SESSION["id"])): ?>
                        <li class="nav-item"><a class="nav-link  active">Mi cuenta</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="iniciar_sesion.php">Iniciar Sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="crear_usuario.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
                <?php if (isset($_SESSION["id"])): ?>
                    <form class="d-flex" action="cerrar_sesion.php">
                        <button class="btn btn-outline-danger mb-1 me-1" type="submit">
                            Cerrar Sesión
                        </button>
                    </form>
                    <form class="d-flex" action="carrito.php">
                        <button class="btn btn-outline-dark mb-1" type="submit">
                            <i class="bi-cart-fill me-1"></i>
                            Carrito
                            <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $prod_carrito ?></span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header-->
    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Tu cuenta</h1>
            </div>
        </div>
    </header>

    <!-- Section-->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4">
                                <p><b>Nombre(s): </b><?php echo $nombre ?></p>
                                <p><b>Apellido(s): </b><?php echo $apellidos ?></p>
                                <p><b>Email: </b><?php echo $email ?></p>
                                <p><b>Fecha de nacimiento: </b><?php echo $nac ?></p>
                                <p><b>Calle: </b><?php echo $calle ?></p>
                                <p><b>Colonia: </b><?php echo $colonia ?></p>
                                <p><b>No. ext.: </b><?php echo $next ?></p>
                                <p><b>No. int.: </b><?php echo $nint ?></p>
                                <p><b>C.P.: </b><?php echo $cp ?></p>
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