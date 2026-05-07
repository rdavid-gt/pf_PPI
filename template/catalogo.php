<?php

// Configuración simple
$host = 'db';
$user = 'usuario';
$pass = '12345';
$db = 'proyectoFinal';

// Conexión (con manejo básico de error)
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die("Conexión fallida: " . $mysqli->connect_error);

// Conseguir todos los productos del catálogo
$sql = "SELECT id, nombre, descripcion, f_public, cantidad, compania, plataforma, precio, imagen FROM producto ORDER BY id ASC";
$resultado = $mysqli->query($sql);
$productos = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = $row;
    }
}

// Conseguir las imágenes de los productos
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

// Verificar si hay sesión iniciada y que es el administrador
session_start();
if(!isset($_SESSION['id'])){
    header("Location: inicio.php");
    exit();
}
include "admin.php";

// Actualiza el producto seleccionado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
        $file = $_FILES['imagen'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/avif'];
        if (!in_array($mime, $allowed)) {
            $mensaje = "Solo se permiten AVIF, JPG, PNG o JPEG (tipo detectado: $mime)";
        } elseif ($file['size'] > 2 * 1024 * 1024) { // 2 MB máximo
            $mensaje = "El archivo es demasiado grande (máx. 2 MB)";
        } else {
            // Leer binario y preparar inserción
            $data = file_get_contents($file['tmp_name']);
            $stmt = $mysqli->prepare("UPDATE producto SET imagen = ? WHERE id = ?");
            if (!$stmt) {
                $mensaje = "Error en la preparación: " . $mysqli->error;
            } else {
                $stmt->bind_param("si", $data, $_POST['id_prod']);
                if ($stmt->execute()) {
                    $mensaje = "Producto guardado con ID: " . $mysqli->insert_id;
                } else {
                    $mensaje = "Error al guardar: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }

    $query = "UPDATE producto SET nombre = ?, descripcion = ?, f_public = ?, cantidad = ?, compania = ?, plataforma = ?, precio = ?  WHERE id = ?";
    $mysqli->execute_query($query, [$_POST['nombre'],$_POST['desc'],$_POST['f_public'],$_POST['almacen'],$_POST['company'],$_POST['plat'],$_POST['precio'],$_POST['id_prod']]);
    header("Location: catalogo.php#producto".$_POST['id_prod']);
    exit();
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
    <title>Proyecto Final - Catálogo</title>
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_historial.php">Historial</a></li>
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
                <h1 class="display-4 fw-bolder">Catálogo</h1>
                <p class="lead fw-normal text-white-50 mb-0">Si no se ingresa ninguna imagen, se mantendrá la antigua</p>
            </div>
        </div>
    </header>
    <!-- Section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <div class="mt-3 mb-4">
                <?php include 'alertas.php'; ?>
            </div>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php if (empty($productos)): ?>
                    <p><img src="../imagenes/no_products.avif" alt="No hay productos registrados"></p>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <div class="col mb-5" id="producto<?= $producto['id'] ?>">
                            <div class="card h-100" data-bs-toggle="modal" data-bs-target="#modalProducto<?= $producto['id'] ?>">
                                <!-- Product image-->
                                <img class="card-img-top" src="?id=<?= $producto['id'] ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                <!-- Product details-->
                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <!-- Product name-->
                                        <h5 class="fw-bolder"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                        <!-- Product price-->
                                        $<?= htmlspecialchars($producto['precio']) ?><br>
                                        <?= htmlspecialchars($producto['compania']) ?><br>
                                        <?= htmlspecialchars($producto['plataforma']) ?><br>
                                    </div>
                                </div>
                                <!-- Product actions-->
                                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                    <div class="text-center"><a class="btn btn-outline-dark mt-auto">Modificar</a></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="modalProducto<?= $producto['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <form method="post" enctype="multipart/form-data">
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
                                                    <div class="mb-3">
                                                        <label for="nombre" class="form-label fw-bold">Nombre del producto:</label><br>
                                                        <input type="text" class="form-control" name="nombre" id="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" placeholder="Ej. Super Mario Bros." required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="desc" class="form-label fw-bold">Descripcion:</label><br>
                                                        <textarea style="resize: none;" class="form-control" name="desc" id="desc" rows="5" placeholder="Descripción detallada del producto" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="f_public" class="form-label fw-bold">Fecha de publicación:</label><br>
                                                        <input type="date" class="form-control" name="f_public" id="f_public" value="<?= htmlspecialchars($producto['f_public']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="almacen" class="form-label fw-bold">Cantidad en almacén:</label><br>
                                                        <input type="number" class="form-control" name="almacen" min="1" id="almacen" value="<?= htmlspecialchars($producto['cantidad']) ?>" placeholder="Ej. 5" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="company" class="form-label fw-bold">Compañía:</label><br>
                                                        <input type="text" class="form-control" name="company" id="company" placeholder="Ej. Nintendo" value="<?= htmlspecialchars($producto['compania']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="plat" class="form-label fw-bold">Plataforma:</label><br>
                                                        <input type="text" class="form-control" name="plat" id="plat" value="<?= htmlspecialchars($producto['plataforma']) ?>" placeholder="Ej. Xbox One" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="precio" class="form-label fw-bold">Precio:</label><br>
                                                        <input type="number" step="0.01" class="form-control" name="precio" id="precio" value="<?= htmlspecialchars($producto['precio']) ?>" placeholder="Ej. 100" min="0.01" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="imagen" class="form-label fw-bold">Selecciona una imagen para mostrar (AVIF, JPG, JPEG o PNG):</label><br>
                                                        <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.avif">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="hidden" name="id_prod" value="<?php echo $producto['id']; ?>">
                                            <button type="submit" class="btn btn-primary">Modificar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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