<?php
if (isset($_GET['msg'])) {
    $mensaje = "";
    $clase = "";

    switch ($_GET['msg']) {
        case 'success':
            $mensaje = "¡Producto añadido al carrito con éxito!";
            $clase = "alert-success";
            break;
        case 'success-buy':
            $mensaje = "¡Compra realizada con éxito!";
            $clase = "alert-success";
            break;
        case 'error_stock':
            $mensaje = "Lo sentimos, no hay suficiente stock disponible.";
            $clase = "alert-danger";
            break;
        case 'error_pw':
            $mensaje = "Contraseña incorrecta";
            $clase = "alert-danger";
            break;
        case 'error_mail':
            $mensaje = "El correo ingresado no está asociado a ninguna cuenta de este sitio web";
            $clase = "alert-danger";
            break;
        case 'error_prod':
            $mensaje = "Lo sentimos, no se pudo agregar el producto al catálogo";
            $clase = "alert-danger";
            break;
        case 'error_compra':
            $mensaje = "Lo sentimos, no hay suficiente stock de algún producto.";
            $clase = "alert-danger";
            break;
        case 'login_ok':
            $mensaje = "¡Bienvenido de nuevo!";
            $clase = "alert-primary";
            break;
        case 'success-mc':
            $mensaje = "Carrito modficado con éxito";
            $clase = "alert-success";
            break;
        case 'success-prod':
            $mensaje = "Producto añadido al catálogo con éxito!";
            $clase = "alert-success";
            break;
    }

    if ($mensaje !== "") {
        echo '
        <div class="alert ' . $clase . ' alert-dismissible fade show" role="alert">
            ' . $mensaje . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}