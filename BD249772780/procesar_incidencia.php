<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: nueva_incidencia.php');
    exit;
}

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Recibir datos del formulario
$descripcion = $_POST['descripcion'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$idGato = $_POST['idGato'] ?? '';
$idVoluntario = $_POST['idVoluntario'] ?? '';

// Validar que todos los campos estén presentes
if (empty($descripcion) || empty($tipo) || empty($idGato) || empty($idVoluntario)) {
    die('Error: Faltan datos obligatorios. <a href="nueva_incidencia.php">Volver al formulario</a>');
}

// Obtener fecha actual
$fecha = date('Y-m-d');

// Insertar la incidencia
$sql = "INSERT INTO INCIDENCIA (descripcion, fecha, tipo, idGato, idVoluntario) 
        VALUES ('$descripcion', '$fecha', '$tipo', $idGato, $idVoluntario)";

try {
    $resultado = mysqli_query($con, $sql);
    if (!$resultado) {
        throw new Exception(mysqli_error($con));
    }
    
    mysqli_close($con);
    header('Location: listar_incidencias.php?success=1');
    exit;
    
} catch (\Throwable $error) {
    mysqli_close($con);
    die("Error al registrar la incidencia: " . $error->getMessage() . " <br><a href='nueva_incidencia.php'>Volver</a>");
}
?>
