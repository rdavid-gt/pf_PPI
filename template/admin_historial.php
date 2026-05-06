<?php

// Configuración simple
$host = 'db';
$user = 'usuario';
$pass = '12345';
$db = 'proyectoFinal';

// Conexión (con manejo básico de error)
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die("Conexión fallida: " . $mysqli->connect_error);

$sql = "SELECT * FROM historial ORDER BY id ASC";
$resultado = $mysqli->query($sql);
$historial = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $historial[] = $row;
    }
}

$sql = "SELECT * FROM detalle_compra ORDER BY id ASC";
$resultado = $mysqli->query($sql);
$detalles = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $detalles[] = $row;
    }
}

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

session_start();
if(!isset($_SESSION['id'])){
    header("Location: inicio.php");
    exit();
}
include "admin.php";

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Proyecto Final - Historial</title>
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
                    <li class="nav-item"><a class="nav-link" href="catalogo.php">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page">Historial</a></li>
                    <li class="nav-item"><a class="nav-link" href="crear_producto.php">Agregar producto</a></li>
                </ul>
                <form class="d-flex" action="cerrar_sesion.php">
                    <button class="btn btn-outline-danger mb-1 me-1" type="submit">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <!-- Header-->
    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Historial</h1>
            </div>
        </div>
    </header>
    <!-- Section-->
    <section class="py-5">
        <div class="container my-5">
            <div class="mt-3 mb-4">
                <?php include 'alertas.php'; ?>
            </div>
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4"># de Compra</th>
                                    <th>Fecha</th>
                                    <th># de Usuario</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($historial)): ?>
                                    <p><img src="../imagenes/no_products.avif" alt="No hay productos registrados"></p>
                                <?php else: ?>
                                    <?php foreach ($historial as $compra): ?>
                                        <tr>
                                            <td class="px-4 fw-bold"><?= htmlspecialchars($compra['idCompra']) ?></td>
                                            <td><?= htmlspecialchars($compra['f_compra']) ?></td>
                                            <td><?= htmlspecialchars($compra['idUsuario']) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetalles<?= $compra['id'] ?>">
                                                    <i class="bi bi-eye-fill me-1"></i> Detalles
                                                </button>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="modalDetalles<?= $compra['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="tituloProducto">Detalles de la compra</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="cart-items-container">
                                                            <?php foreach ($detalles as $detalle): ?>
                                                                <?php
                                                                    $mysqli = new mysqli($host, $user, $pass, $db);
                                                                    $sql = "SELECT * FROM producto WHERE id = ? ORDER BY id ASC";
                                                                    $producto = $mysqli->execute_query($sql, [$detalle['idProducto']]);
                                                                    $producto = $producto->fetch_assoc();

                                                                    $sql = "SELECT total FROM compra WHERE id = ? ORDER BY id ASC";
                                                                    $total = $mysqli->execute_query($sql, [$compra['idCompra']]);
                                                                    $total = $total->fetch_assoc();
                                                                    $total = $total['total'];
                                                                    $mysqli->close();
                                                                ?>
                                                                <div class="row align-items-center mb-4 pb-3 border-bottom">
                                                                    <!-- Columna 1: Imagen del producto -->
                                                                    <div class="col-3 col-md-2">
                                                                        <!-- Imagen con clase thumbnail para marco suave -->
                                                                        <img src="?id=<?= $producto['id'] ?>" alt="<?= $producto['nombre'] ?>" class="img-thumbnail img-fluid rounded">
                                                                    </div>

                                                                    <!-- Columna 2: Detalles del producto -->
                                                                    <div class="col-9 col-md-10">
                                                                        <div class="d-flex justify-content-between align-items-start">
                                                                            <div>
                                                                                <!-- Nombre del Juego destacado -->
                                                                                <h6 class="fw-bolder mb-1"><?= htmlspecialchars($producto['nombre']) ?></h6>
                                                                                <!-- Plataforma en texto secundario -->
                                                                                <p class="text-muted small mb-2"><?= htmlspecialchars($producto['plataforma']) ?></p>
                                                                            </div>
                                                                            <!-- Precio Total del artículo alineado a la derecha -->
                                                                            <div class="text-end">
                                                                                <span class="fw-bold fs-5 text-success">$<?= number_format(htmlspecialchars($detalle['precio_unitario'] * $detalle['cantidad']), 2)  ?></span>
                                                                                <div class="text-muted small">Total</div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Detalles de cantidad y precio unitario organizados -->
                                                                        <div class="d-flex justify-content-start align-items-center bg-light p-2 rounded small">
                                                                            <span class="me-4"><strong>Cantidad:</strong> <?= htmlspecialchars($detalle['cantidad']) ?></span>
                                                                            <span><strong>Precio unitario: </strong>$<?= htmlspecialchars($producto['precio']) ?></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                            <div class="row mt-4 pt-3 border-top border-2">
                                                                <div class="col-12 text-end">
                                                                    <h5 class="fw-bolder">Total de la Compra: 
                                                                        <span class="text-success ms-2">$<?= number_format(htmlspecialchars($total,2))  ?></span>
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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