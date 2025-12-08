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
$idColonia = $_POST['idColonia'] ?? null;

// Validar datos
if (!$idGrupo || !$idColonia) {
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

// Actualizar la colonia asignándola al grupo
$sql = "UPDATE COLONIA_FELINA SET idGrupoTrabajo = ? WHERE idColonia = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ii", $idGrupo, $idColonia);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    
    // Redirigir a la página de información del grupo
    header("Location: info_grupoTrabajo.php?id=$idGrupo");
    exit;
} else {
    die('Error al asignar la colonia: ' . mysqli_error($con));
}
?>
