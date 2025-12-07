<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: formularioCrear_Colonias.php');
    exit;
}

// Comprobar sesión
if (!isset($_SESSION['idAyuntamiento']) || !isset($_SESSION['idPersona'])) {
    die('Acceso denegado. Inicia sesión.');
}

// Helper: verifica si el usuario tiene una función asignada
function usuarioPuede($con, $idPersona, $funcionNombre) {
    $sqlPerm = "SELECT 1
                FROM PER_ROL pr
                JOIN PUEDEHACER ph ON pr.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
                WHERE pr.idPersona = ? AND LOWER(f.nombre) = LOWER(?)
                LIMIT 1";

    $stmtPerm = mysqli_prepare($con, $sqlPerm);
    mysqli_stmt_bind_param($stmtPerm, 'is', $idPersona, $funcionNombre);
    mysqli_stmt_execute($stmtPerm);
    $resPerm = mysqli_stmt_get_result($stmtPerm);
    $has = ($resPerm && mysqli_num_rows($resPerm) > 0);
    mysqli_stmt_close($stmtPerm);
    return $has;
}

// Recoger parámetros
$idColonia = isset($_POST['idColonia']) ? (int)$_POST['idColonia'] : 0;
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

// Verificar permiso para modificar/crear
if (!usuarioPuede($con, (int)$_SESSION['idPersona'], 'modificar Colonia')) {
    mysqli_close($con);
    die('No tienes permiso para modificar colonias.');
}

// Validar que el grupo pertenece al ayuntamiento del usuario
$idAyu = (int) $_SESSION['idAyuntamiento'];
if ($idGrupo !== null) {
    $sqlGrupo = "SELECT 1 FROM GRUPO_TRABAJO WHERE idGrupoTrabajo = ? AND idAyuntamiento = ? LIMIT 1";
    $stmtGrupo = mysqli_prepare($con, $sqlGrupo);
    mysqli_stmt_bind_param($stmtGrupo, 'ii', $idGrupo, $idAyu);
    mysqli_stmt_execute($stmtGrupo);
    $resGrupo = mysqli_stmt_get_result($stmtGrupo);
    if (!$resGrupo || mysqli_num_rows($resGrupo) === 0) {
        mysqli_stmt_close($stmtGrupo);
        mysqli_close($con);
        die('El grupo seleccionado no pertenece a tu ayuntamiento.');
    }
    mysqli_stmt_close($stmtGrupo);
}

if ($idColonia > 0) {
    // Actualizar colonia existente (asegurando pertenencia al ayuntamiento)
    $sql = "UPDATE COLONIA_FELINA
            SET nombre = ?, descripcion = ?, coordenadas = ?, lugarReferencia = ?, numeroGatos = ?, idGrupoTrabajo = ?
            WHERE idColonia = ?
              AND (idGrupoTrabajo IS NULL OR idGrupoTrabajo IN (SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idAyuntamiento = ?))";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssiiii', $nombre, $descripcion, $coordenadas, $lugar, $numeroGatos, $idGrupo, $idColonia, $idAyu);
    $ok = mysqli_stmt_execute($stmt);

    if (!$ok) {
        die('Error al actualizar: ' . mysqli_error($con));
    }

    // Si no hubo filas afectadas puede ser que no haya cambios o que no pertenezca; verificamos pertenencia.
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        mysqli_stmt_close($stmt);
        $sqlCheck = "SELECT 1 FROM COLONIA_FELINA c
                     LEFT JOIN GRUPO_TRABAJO gt ON c.idGrupoTrabajo = gt.idGrupoTrabajo
                     WHERE c.idColonia = ? AND (gt.idAyuntamiento = ? OR c.idGrupoTrabajo IS NULL)
                     LIMIT 1";
        $stmtCheck = mysqli_prepare($con, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, 'ii', $idColonia, $idAyu);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $exists = ($resCheck && mysqli_num_rows($resCheck) > 0);
        mysqli_stmt_close($stmtCheck);

        if (!$exists) {
            mysqli_close($con);
            die('No se actualizó ninguna fila. Verifica que la colonia pertenece a tu ayuntamiento.');
        }
        // Existe y simplemente no hubo cambios: continuar sin error.
    } else {
        mysqli_stmt_close($stmt);
    }
} else {
    // Insertar colonia nueva
    $sql = "INSERT INTO COLONIA_FELINA (nombre, descripcion, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssii', $nombre, $descripcion, $coordenadas, $lugar, $numeroGatos, $idGrupo);
    $ok = mysqli_stmt_execute($stmt);

    if (!$ok) {
        die('Error al guardar: ' . mysqli_error($con));
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($con);

// Redirigir según si era edición o alta
if ($idColonia > 0) {
    header('Location: info_colonia.php?id=' . $idColonia);
} else {
    header('Location: listar_colonias.php');
}
exit;
?>