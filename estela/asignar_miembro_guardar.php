<?php
session_start();

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
if (!$idAyuntamiento) {
    die('Error: no se detectó ayuntamiento en la sesión.');
}

// Obtener datos del formulario
$idGrupo = $_POST['idGrupo'] ?? null;
$idPersona = $_POST['idPersona'] ?? null;

// Validar datos
if (!$idGrupo || !$idPersona) {
    die('Error: datos incompletos.');
}

// Verificar que el grupo pertenece al ayuntamiento del usuario
$sqlVerificar = "SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idGrupoTrabajo = ? AND idAyuntamiento = ?";
$stmtVerif = mysqli_prepare($con, $sqlVerificar);
mysqli_stmt_bind_param($stmtVerif, "ii", $idGrupo, $idAyuntamiento);
mysqli_stmt_execute($stmtVerif);
$resultVerif = mysqli_stmt_get_result($stmtVerif);

if (mysqli_num_rows($resultVerif) === 0) {
    die('Error: no tienes permiso para modificar este grupo.');
}

// Verificar si la persona ya es voluntario del ayuntamiento
$sqlCheckVol = "SELECT idVoluntario FROM VOLUNTARIO WHERE idPersona = ? AND idAyuntamiento = ?";
$stmtCheck = mysqli_prepare($con, $sqlCheckVol);
mysqli_stmt_bind_param($stmtCheck, "ii", $idPersona, $idAyuntamiento);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) > 0) {
    // Ya es voluntario, actualizar el grupo (mover de un grupo a otro)
    $sql = "UPDATE VOLUNTARIO SET idGrupoTrabajo = ? WHERE idPersona = ? AND idAyuntamiento = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $idGrupo, $idPersona, $idAyuntamiento);
    $mensaje = "voluntario movido";
} else {
    // No es voluntario, crear nuevo registro
    $sql = "INSERT INTO VOLUNTARIO (idAyuntamiento, idGrupoTrabajo, idPersona) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $idAyuntamiento, $idGrupo, $idPersona);
    $mensaje = "voluntario añadido";
}

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    
    // Redirigir a la página de información del grupo
    header("Location: info_grupoTrabajo.php?id=$idGrupo");
    exit;
} else {
    die('Error al añadir el voluntario: ' . mysqli_error($con));
}
?>
