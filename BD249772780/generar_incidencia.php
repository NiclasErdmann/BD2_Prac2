<?php
session_start();
require_once 'conectar_bd.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['idVoluntario'])) {
    die("Error: Debes iniciar sesión como voluntario para registrar incidencias.");
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: Método no permitido.");
}

// Recoger datos del formulario
$fecha = date('Y-m-d'); // Fecha actual automática
$descripcion = $_POST['descripcion'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$idGato = $_POST['idGato'] ?? '';
$idVoluntario = $_SESSION['idVoluntario'];

// Validar datos requeridos
if (empty($descripcion) || empty($tipo) || empty($idGato)) {
    die("Error: Todos los campos obligatorios deben estar completos (incluyendo el gato).");
}

// Insertar en la base de datos
$sql = "INSERT INTO INCIDENCIA (fecha, descripcion, tipo, idVoluntario, idGato)
        VALUES ('$fecha', '$descripcion', '$tipo', $idVoluntario, $idGato)";

try {
    $resultado = mysqli_query($conn, $sql);
    if (!$resultado) {
        throw new Exception(mysqli_error($conn));
    }
    
    $idIncidencia = mysqli_insert_id($conn);
    
    echo "<h2>Incidencia registrada correctamente</h2>";
    echo "<p>ID de incidencia: " . $idIncidencia . "</p>";
    echo "<p><a href='incidencias_nueva.php'>Registrar otra incidencia</a></p>";
    echo "<p><a href='incidencias_listar.php'>Ver todas las incidencias</a></p>";
    
} catch (\Throwable $error) {
    echo "<h2>Error al registrar la incidencia</h2>";
    echo "<p>Detalles: " . $error->getMessage() . "</p>";
    echo "<p><a href='incidencias_nueva.php'>Volver al formulario</a></p>";
}

$conn->close();
?>