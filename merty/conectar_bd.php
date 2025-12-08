<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$basedatos = "gatukos";  // Cambia esto por el nombre de tu base de datos

$conn = new mysqli($servidor, $usuario, $password, $basedatos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
