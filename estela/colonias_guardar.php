<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crearColonia.php');
    exit;
}

// Comprobar sesión
if (!isset($_SESSION['idAyuntamiento'])) {
    die('Acceso denegado. Inicia sesión.');
}

// Recoger parámetros
$nombre = $_POST['nombre'] ?? '';
$lugar = $_POST['lugarReferencia'] ?? null;
$coordenadas = $_POST['coordenadas'] ?? null;
$numeroGatos = isset($_POST['numeroGatos']) ? (int)$_POST['numeroGatos'] : 0;
$descripcion = $_POST['descripcion'] ?? null;
$idGrupo = isset($_POST['idGrupo']) && $_POST['idGrupo'] !== '' ? (int)$_POST['idGrupo'] : null;

if (trim($nombre) === '') {
    die('El nombre es obligatorio.');
}

// Conexión
$con = mysqli_connect('localhost','root','','BD2_Prac2');
if (!$con) die('Error conexión: ' . mysqli_connect_error());

// Insertar colonia
$sql = "INSERT INTO COLONIA_FELINA (nombre, descripcion, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ssssii', $nombre, $descripcion, $coordenadas, $lugar, $numeroGatos, $idGrupo);
$ok = mysqli_stmt_execute($stmt);

if (!$ok) {
    die('Error al guardar: ' . mysqli_error($con));
}

mysqli_stmt_close($stmt);
mysqli_close($con);

header('Location: listar_colonias.php');
exit;
?>