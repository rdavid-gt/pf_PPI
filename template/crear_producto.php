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
$nombre = $descripcion = $f_public = $cantidad = $compania = $plataforma = $precio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = clean($_POST["nombre"]);
    $descripcion = clean($_POST["desc"]);
    $f_public = clean($_POST["f_public"]);
    $cantidad = clean($_POST["almacen"]);
    $compania = clean($_POST["company"]);
    $plataforma = clean($_POST["plat"]);
    $precio = clean($_POST["precio"]);
}

function clean($data)
{
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    $file = $_FILES['imagen'];
    
    // Validar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $mensaje = "Error al subir el archivo (código: {$file['error']})";
    } else {
        // Validar tipo MIME (no solo extensión)
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
            $stmt = $mysqli->prepare("INSERT INTO producto (nombre, descripcion, f_public, cantidad, compania, plataforma, precio, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $mensaje = "Error en la preparación: " . $mysqli->error;
            } else {
                $stmt->bind_param("sssissds", $nombre, $descripcion, $f_public, $cantidad, $compania, $plataforma, $precio, $data);
                if ($stmt->execute()) {
                    header("Location: crear_producto.php?msg=success-prod");
                } else {
                    header("Location: crear_producto.php?msg=error_prod");
                }
                exit();
                $stmt->close();
            }
        }
    }
}

session_start();
if(!isset($_SESSION['id'])){
    header("Location: inicio.php");
    exit();
}
include "admin.php"
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Proyecto Final - Registro de producto</title>
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
                    <li class="nav-item"><a class="nav-link" href="catalogo.php">Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_historial.php">Historial</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page">Agregar producto</a></li>
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
                <h1 class="display-4 fw-bolder">Registro de productos</h1>
                <p class="lead fw-normal text-white-50 mb-0">Aquí se registran productos nuevos</p>
            </div>
        </div>
    </header>

    <!-- Section-->
    <section class="py-5">
        <div class="container">
            <div class="container mt-3">
                <?php include 'alertas.php'; ?>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <form method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre del producto:</label><br>
                                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej. Super Mario Bros." required>
                                </div>
                                <div class="mb-3">
                                    <label for="desc" class="form-label fw-bold">Descripcion:</label><br>
                                    <textarea style="resize: none;" class="form-control" name="desc" id="desc" rows="5" placeholder="Descripción detallada del producto" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="f_public" class="form-label fw-bold">Fecha de publicación:</label><br>
                                    <input type="date" class="form-control" name="f_public" id="f_public" required>
                                </div>
                                <div class="mb-3">
                                    <label for="almacen" class="form-label fw-bold">Cantidad en almacén:</label><br>
                                    <input type="number" class="form-control" name="almacen" min="1" id="almacen" placeholder="Ej. 5" required>
                                </div>
                                <div class="mb-3">
                                    <label for="company" class="form-label fw-bold">Compañía:</label><br>
                                    <input type="text" class="form-control" name="company" id="company" placeholder="Ej. Nintendo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="plat" class="form-label fw-bold">Plataforma:</label><br>
                                    <input type="text" class="form-control" name="plat" id="plat" placeholder="Ej. Xbox One" required>
                                </div>
                                <div class="mb-3">
                                    <label for="precio" class="form-label fw-bold">Precio:</label><br>
                                    <input type="number" step="0.01" class="form-control" name="precio" id="precio" placeholder="Ej. 100" min="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="imagen" class="form-label fw-bold">Selecciona una imagen para mostrar (AVIF, JPG, JPEG o PNG):</label><br>
                                    <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.avif" required>
                                </div>
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-dark text-white">Registrar producto</button>
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