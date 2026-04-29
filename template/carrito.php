<?php

// Configuración simple
$host = 'db';
$user = 'usuario';
$pass = '12345';
$db = 'proyectoFinal';

session_start();

// Conexión (con manejo básico de error)
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die("Conexión fallida: " . $mysqli->connect_error);

$sql = "SELECT P.id, P.nombre, P.descripcion, P.f_public, P.cantidad, P.compania, P.plataforma, P.precio, P.imagen, CP.cantidad as CantCarrito FROM producto P, carrito_productos CP, carrito_cliente CC WHERE CC.idUsuario = ? AND CC.id = CP.idCarrito AND P.id = CP.idProducto ORDER BY P.id ASC;";
$resultado = $mysqli->execute_query($sql, [$_SESSION['id']]);
$productos = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = $row;
    }
}

$query = "SELECT SUM(cantidad) as Cuenta FROM carrito_productos CP, carrito_cliente CC WHERE CC.idUsuario = ? AND CC.id = CP.idCarrito;";
$prod_carrito = $mysqli->execute_query($query, [$_SESSION['id']]);
$prod_carrito = $prod_carrito->fetch_assoc();
$prod_carrito = $prod_carrito['Cuenta'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare("SELECT imagen FROM producto WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        header("Content-Type: " . "image/png");
        echo $row['imagen'];
    } else {
        // Imagen por defecto (pixel transparente en Base64)
        header("Content-Type: image/png");
        echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==");
    }
    $stmt->close();
    $mysqli->close();
    exit; // Detener ejecución para no enviar HTML
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer binario y preparar inserción

    if($_SERVER['action'] != 'delete'){
        $res_stock = $mysqli->execute_query("SELECT cantidad FROM producto WHERE id = ?", [$_POST['id_prod']]);
        $item = $res_stock->fetch_assoc();

        if ($item && $item['cantidad'] >= $_POST['cant'] && $item['cantidad'] > 0) {
            $query = "SELECT id FROM carrito_cliente WHERE idUsuario = ?";
            $id_carrito = $mysqli->execute_query($query, [$_SESSION['id']]);
            $id_carrito = $id_carrito->fetch_assoc();

            $query = "UPDATE carrito_productos SET cantidad = ? WHERE idCarrito = ? AND idProducto = ?";
            $mysqli->execute_query($query, [$_POST['cant'],$id_carrito['id'],$_POST['id_prod']]);

            header("Location: carrito.php?msg=success-mc");
            exit();
        } else {
            header("Location: carrito.php?msg=error_stock");
            exit();
        }
    }else{
        $query = "SELECT id FROM carrito_cliente WHERE idUsuario = ?";
        $id_carrito = $mysqli->execute_query($query, [$_SESSION['id']]);
        $id_carrito = $id_carrito->fetch_assoc();

        $query = "DELETE FROM carrito_productos WHERE idCarrito = ? AND idProducto = ?";
        $id_carrito = $mysqli->execute_query($query, [$_SESSION['id'],$_POST['id_p']]);
        $id_carrito = $id_carrito->fetch_assoc();

        header("Location: carrito.php?msg=success-mc");
        exit();
    }
}

$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Proyecto Final - Tienda</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container px-4 px-lg-5">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="inicio.php">Inicio</a></li>
                    <?php if (isset($_SESSION["id"])): ?>
                        <li class="nav-item"><a class="nav-link" href="mi_cuenta.php">Mi cuenta</a></li>
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
                    <form class="d-flex" action="../carrito.html">
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
                <h1 class="display-4 fw-bolder">Tu carrito</h1>
                <?php if (isset($_SESSION["id"])):  ?>
                    <p class="lead fw-normal text-white-50 mb-0">Contiene <?php echo $prod_carrito ?> productos</p>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <!-- Section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <div class="container mt-3">
                <?php include 'alertas.php'; ?>
            </div>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php if (empty($productos)): ?>
                    <p><img src="../imagenes/carrito_vacio.png" alt="No hay productos en el carrito"></p>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <div class="col mb-5">
                            <div class="card h-100" data-bs-toggle="modal" data-bs-target="#modalProducto<?= $producto['id'] ?>">
                                <!-- Product image-->
                                <img class="card-img-top" src="?id=<?= $producto['id'] ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" />
                                <!-- Product details-->
                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <!-- Product name-->
                                        <h5 class="fw-bolder"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                        <!-- Product price-->
                                        Precio unitario: $<?= htmlspecialchars($producto['precio']) ?><br>
                                        <?= htmlspecialchars($producto['compania']) ?><br>
                                        <?= htmlspecialchars($producto['plataforma']) ?><br>
                                        Cantidad en carrito: <?= htmlspecialchars($producto['CantCarrito']) ?><br>
                                        Precio total: $<?= htmlspecialchars($producto['precio'] * $producto['CantCarrito']) ?>.00<br>
                                    </div>
                                </div>
                                <!-- Product actions-->
                                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                    <div class="text-center">
                                        <a class="btn btn-outline-dark mt-auto my-1">Modificar</a>
                                        <form enctype="multipart/form-data" method="post">
                                            <input type="hidden" name="id_p" value="<?php echo $producto['id']; ?>">
                                            <button class="btn btn-outline-dark mt-auto" name="action" value="delete" type="submit">Eliminar producto(s)</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modalProducto<?= $producto['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="tituloProducto"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <img src="?id=<?= $producto['id'] ?>" id="imagenModal" class="img-fluid rounded" alt="Portada">
                                            </div>
                                            <div class="col-md-6">
                                                <h4 id="precioModal">$<?= htmlspecialchars($producto['precio']) ?></h4>
                                                <p id="descripcionModal">
                                                    <strong>Descripción: </strong><?= htmlspecialchars($producto['descripcion']) ?>
                                                </p>
                                                <p><strong>Compañía: </strong><?= htmlspecialchars($producto['compania']) ?></p>
                                                <p><strong>Plataforma: </strong><?= htmlspecialchars($producto['plataforma']) ?></p>
                                                <p><strong>Fecha de publicación: </strong><?= htmlspecialchars($producto['f_public']) ?></p>
                                                <p><strong>Cantidad en almacén: </strong><?= htmlspecialchars($producto['cantidad']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <?php if (isset($_SESSION["id"])):  ?>
                                            <form enctype="multipart/form-data" method="post">
                                                <input type="hidden" name="id_prod" value="<?php echo $producto['id']; ?>">
                                                <label for="cant">Nueva cantidad en carrito: </label>
                                                <input style="width: 50px;" type="number" name="cant" id="cant" min="1" max="<?php echo $producto['cantidad']; ?>">
                                                <button type="submit" class="btn btn-primary">Modificar carrito</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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