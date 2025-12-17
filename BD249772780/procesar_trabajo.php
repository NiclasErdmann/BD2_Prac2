<?php
session_start();

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión
if (!isset($_SESSION['idPersona'])) {
    header('Location: ../login.html');
    exit();
}

// Validar datos del formulario
if (!isset($_POST['idColonia']) || !isset($_POST['idVoluntario']) || 
    !isset($_POST['fechaInicio']) || !isset($_POST['fechaFin']) || 
    !isset($_POST['hora']) || !isset($_POST['idMarcaComida'])) {
    header('Location: planificar_trabajo.php?error=' . urlencode('Faltan datos obligatorios'));
    exit();
}

$idColonia = intval($_POST['idColonia']);
$idVoluntario = intval($_POST['idVoluntario']);
$fechaInicio = mysqli_real_escape_string($con, $_POST['fechaInicio']);
$fechaFin = mysqli_real_escape_string($con, $_POST['fechaFin']);
$hora = mysqli_real_escape_string($con, $_POST['hora']);
$idMarcaComida = intval($_POST['idMarcaComida']);
$descripcion = isset($_POST['descripcion']) && $_POST['descripcion'] !== '' 
    ? "'" . mysqli_real_escape_string($con, $_POST['descripcion']) . "'" 
    : 'NULL';

// Validar que fechaFin >= fechaInicio
if (strtotime($fechaFin) < strtotime($fechaInicio)) {
    header('Location: planificar_trabajo.php?error=' . urlencode('La fecha fin debe ser posterior o igual a la fecha inicio'));
    exit();
}

// Verificar que el responsable está asignado a un grupo
$idPersona = $_SESSION['idPersona'];
$sqlResponsable = "SELECT idGrupoTrabajo FROM VOLUNTARIO WHERE idPersona = $idPersona";
$resultResponsable = mysqli_query($con, $sqlResponsable);
$responsable = mysqli_fetch_assoc($resultResponsable);

if (!$responsable || !$responsable['idGrupoTrabajo']) {
    header('Location: planificar_trabajo.php?error=' . urlencode('No tienes grupo asignado'));
    exit();
}

$idGrupoResponsable = $responsable['idGrupoTrabajo'];

// Verificar que la colonia pertenece al grupo del responsable
$sqlCheckColonia = "SELECT idColonia FROM COLONIA_FELINA WHERE idColonia = $idColonia AND idGrupoTrabajo = $idGrupoResponsable";
$resultCheckColonia = mysqli_query($con, $sqlCheckColonia);

if (mysqli_num_rows($resultCheckColonia) === 0) {
    header('Location: planificar_trabajo.php?error=' . urlencode('La colonia no pertenece a tu grupo'));
    exit();
}

// Verificar que el voluntario pertenece al grupo del responsable
$sqlCheckVoluntario = "SELECT idVoluntario FROM VOLUNTARIO WHERE idVoluntario = $idVoluntario AND idGrupoTrabajo = $idGrupoResponsable";
$resultCheckVoluntario = mysqli_query($con, $sqlCheckVoluntario);

if (mysqli_num_rows($resultCheckVoluntario) === 0) {
    header('Location: planificar_trabajo.php?error=' . urlencode('El voluntario no pertenece a tu grupo'));
    exit();
}

// Insertar un trabajo por cada día entre fechaInicio y fechaFin
$fechaActual = strtotime($fechaInicio);
$fechaFinTimestamp = strtotime($fechaFin);
$trabajosCreados = 0;

while ($fechaActual <= $fechaFinTimestamp) {
    $fechaStr = date('Y-m-d', $fechaActual);
    
    $sql = "INSERT INTO TRABAJO (descripcion, fecha, hora, estado, comentario, idMarcaComida, idColonia, idVoluntario) 
            VALUES ($descripcion, '$fechaStr', '$hora', 'pendiente', NULL, $idMarcaComida, $idColonia, $idVoluntario)";
    
    if (!mysqli_query($con, $sql)) {
        header('Location: planificar_trabajo.php?error=' . urlencode('Error al crear trabajos: ' . mysqli_error($con)));
        mysqli_close($con);
        exit();
    }
    
    $trabajosCreados++;
    $fechaActual = strtotime('+1 day', $fechaActual);
}

mysqli_close($con);
header('Location: planificar_trabajo.php?exito=' . $trabajosCreados);
?>
