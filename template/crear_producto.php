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
                    $mensaje = "Producto guardado con ID: " . $mysqli->insert_id;
                } else {
                    $mensaje = "Error al guardar: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de producto</title>
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
                    <li class="nav-item"><a class="nav-link" href="../iniciar_sesion.html">Mi cuenta</a></li>
                </ul>
                <form class="d-flex" action="../carrito.html">
                    <button class="btn btn-outline-dark" type="submit">
                        <i class="bi-cart-fill me-1"></i>
                        Carrito
                        <span class="badge bg-dark text-white ms-1 rounded-pill">0</span>
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