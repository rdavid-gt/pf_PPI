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
    $query = "SELECT password FROM usuario WHERE email = ?;";
    $result = $mysqli->execute_query($query, [$correo]);

    /*
    if ($result) {
        if ($password == $result) {
            session_start();
        } else {
            $mensaje = "Contraseña incorrecta";
        }
    } else {
        $mensaje = "El correo ingresado no está asociado a ninguna cuenta";
    }
    */
}

$query = "SELECT password FROM usuario WHERE email = 'admin@admin.com';";
$result = $mysqli->execute_query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba</title>
</head>
<body>
    <form action="">
        <label for=""></label>
        <input type="text" disabled name="" id="">
    </form>
</body>
</html>