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
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en la preparación: " . $conn->error);
}

$stmt->bind_param("sssii", $fecha, $descripcion, $tipo, $idVoluntario, $idGato);

if ($stmt->execute()) {
    echo "<h2>✓ Incidencia registrada correctamente</h2>";
    echo "<p>ID de incidencia: " . $stmt->insert_id . "</p>";
    echo "<p><a href='incidencias_nueva.php'>Registrar otra incidencia</a></p>";
    echo "<p><a href='incidencias_listar.php'>Ver todas las incidencias</a></p>";
} else {
    echo "<h2>✗ Error al registrar la incidencia</h2>";
    echo "<p>Detalles: " . $stmt->error . "</p>";
    echo "<p><a href='incidencias_nueva.php'>Volver al formulario</a></p>";
}

$stmt->close();
$conn->close();
?>