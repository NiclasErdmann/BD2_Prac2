<?php
session_start();

require_once '../header.php';

$con = mysqli_connect('localhost', 'root', '', 'BD2_Prac2');
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersona = $_SESSION['idPersona'] ?? null;
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'crear';
$guardar = $_GET['guardar'] ?? $_POST['guardar'] ?? null;
$idColonia = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['idColonia']) ? (int)$_POST['idColonia'] : 0);

if (!$idAyuntamiento || !$idPersona) {
    die('Error: datos de sesión incompletos.');
}

// Comprueba si el usuario tiene la función solicitada
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

if (!usuarioPuede($con, $idPersona, 'Modificar Colonias')) {
    die('No tienes permiso para modificar colonias.');
}

// Datos iniciales para el formulario
$colonia = [
    'idColonia' => null,
    'nombre' => '',
    'descripcion' => '',
    'coordenadas' => '',
    'lugarReferencia' => '',
    'numeroGatos' => 0,
    'idGrupoTrabajo' => null,
];

if ($accion === 'editar') {
    if ($idColonia <= 0) {
        die('Error: ID de colonia no proporcionado.');
    }

    $sqlCol = "SELECT c.idColonia, c.nombre, c.descripcion, c.coordenadas, c.lugarReferencia, c.numeroGatos, c.idGrupoTrabajo
               FROM COLONIA_FELINA c
               LEFT JOIN GRUPO_TRABAJO gt ON c.idGrupoTrabajo = gt.idGrupoTrabajo
               WHERE c.idColonia = ? AND (gt.idAyuntamiento = ? OR c.idGrupoTrabajo IS NULL)
               LIMIT 1";
    $stmtCol = mysqli_prepare($con, $sqlCol);
    mysqli_stmt_bind_param($stmtCol, 'ii', $idColonia, $idAyuntamiento);
    mysqli_stmt_execute($stmtCol);
    $resCol = mysqli_stmt_get_result($stmtCol);
    $colonia = mysqli_fetch_assoc($resCol);
    mysqli_stmt_close($stmtCol);

    if (!$colonia) {
        die('Colonia no encontrada o no pertenece a tu ayuntamiento.');
    }
}

// Guardar cambios
if ($guardar === '1') {
    $nombre = trim($_POST['nombre'] ?? '');
    $lugar = trim($_POST['lugarReferencia'] ?? '');
    $coordenadas = trim($_POST['coordenadas'] ?? '');
    $numeroGatos = isset($_POST['numeroGatos']) ? (int)$_POST['numeroGatos'] : 0;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idGrupo = isset($_POST['idGrupo']) && $_POST['idGrupo'] !== '' ? (int)$_POST['idGrupo'] : null;

    if ($nombre === '') {
        die('El nombre es obligatorio.');
    }

    // Validar que el grupo pertenece al ayuntamiento del usuario
    if ($idGrupo !== null) {
        $sqlGrupo = "SELECT 1 FROM GRUPO_TRABAJO WHERE idGrupoTrabajo = ? AND idAyuntamiento = ? LIMIT 1";
        $stmtGrupo = mysqli_prepare($con, $sqlGrupo);
        mysqli_stmt_bind_param($stmtGrupo, 'ii', $idGrupo, $idAyuntamiento);
        mysqli_stmt_execute($stmtGrupo);
        $resGrupo = mysqli_stmt_get_result($stmtGrupo);
        if (!$resGrupo || mysqli_num_rows($resGrupo) === 0) {
            mysqli_stmt_close($stmtGrupo);
            die('El grupo seleccionado no pertenece a tu ayuntamiento.');
        }
        mysqli_stmt_close($stmtGrupo);
    }

    if ($accion === 'crear') {
        $sqlInsert = "INSERT INTO COLONIA_FELINA (nombre, descripcion, coordenadas, lugarReferencia, numeroGatos, idGrupoTrabajo)
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = mysqli_prepare($con, $sqlInsert);
        mysqli_stmt_bind_param($stmtInsert, 'ssssii', $nombre, $descripcion, $coordenadas, $lugar, $numeroGatos, $idGrupo);

        if (!mysqli_stmt_execute($stmtInsert)) {
            die('Error al guardar la colonia: ' . mysqli_error($con));
        }

        $nuevoId = mysqli_insert_id($con);
        header('Location: info_colonia.php?id=' . $nuevoId);
        exit;
    }

    if ($accion === 'editar') {
        if ($idColonia <= 0) {
            die('Error: ID de colonia no proporcionado.');
        }

        $sqlUpdate = "UPDATE COLONIA_FELINA
                      SET nombre = ?, descripcion = ?, coordenadas = ?, lugarReferencia = ?, numeroGatos = ?, idGrupoTrabajo = ?
                      WHERE idColonia = ?
                        AND (idGrupoTrabajo IS NULL OR idGrupoTrabajo IN (SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idAyuntamiento = ?))";
        $stmtUpdate = mysqli_prepare($con, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, 'ssssiiii', $nombre, $descripcion, $coordenadas, $lugar, $numeroGatos, $idGrupo, $idColonia, $idAyuntamiento);

        if (!mysqli_stmt_execute($stmtUpdate)) {
            die('Error al actualizar la colonia: ' . mysqli_error($con));
        }

        // Si no hay filas afectadas, verificar pertenencia
        if (mysqli_stmt_affected_rows($stmtUpdate) === 0) {
            mysqli_stmt_close($stmtUpdate);
            $sqlCheck = "SELECT 1 FROM COLONIA_FELINA c
                         LEFT JOIN GRUPO_TRABAJO gt ON c.idGrupoTrabajo = gt.idGrupoTrabajo
                         WHERE c.idColonia = ? AND (gt.idAyuntamiento = ? OR c.idGrupoTrabajo IS NULL)
                         LIMIT 1";
            $stmtCheck = mysqli_prepare($con, $sqlCheck);
            mysqli_stmt_bind_param($stmtCheck, 'ii', $idColonia, $idAyuntamiento);
            mysqli_stmt_execute($stmtCheck);
            $resCheck = mysqli_stmt_get_result($stmtCheck);
            $exists = ($resCheck && mysqli_num_rows($resCheck) > 0);
            mysqli_stmt_close($stmtCheck);

            if (!$exists) {
                die('No se pudo actualizar: verifica que la colonia pertenece a tu ayuntamiento.');
            }
        }

        header('Location: info_colonia.php?id=' . $idColonia);
        exit;
    }

    die('Acción no válida.');
}

// Listado de grupos disponibles para el desplegable
$sqlGrupos = "SELECT idGrupoTrabajo, nombre FROM GRUPO_TRABAJO WHERE idAyuntamiento = ? ORDER BY nombre";
$stmtGrupos = mysqli_prepare($con, $sqlGrupos);
mysqli_stmt_bind_param($stmtGrupos, 'i', $idAyuntamiento);
mysqli_stmt_execute($stmtGrupos);
$resGrupos = mysqli_stmt_get_result($stmtGrupos);

addBreadcrumb('Mis Colonias', 'listar_colonias.php');
addBreadcrumb($accion === 'crear' ? 'Crear Colonia' : 'Editar Colonia');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo $accion === 'crear' ? 'Crear Colonia' : 'Editar Colonia'; ?></title>
    <style>label{display:block;margin-top:8px;}input,select,textarea{width:100%;max-width:500px;padding:6px}</style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <h2><?php echo $accion === 'crear' ? 'Nueva Colonia' : 'Editar Colonia'; ?></h2>
    <form method="post" action="colonia_accion.php">
        <input type="hidden" name="accion" value="<?php echo $accion; ?>">
        <input type="hidden" name="guardar" value="1">
        <?php if ($accion === 'editar'): ?>
            <input type="hidden" name="idColonia" value="<?php echo (int)$colonia['idColonia']; ?>">
        <?php endif; ?>

        <label for="nombre">Nombre de la Colonia:</label>
        <input id="nombre" name="nombre" type="text" required value="<?php echo htmlspecialchars($colonia['nombre']); ?>">

        <label for="lugarReferencia">Lugar de Referencia (Ubicación):</label>
        <input id="lugarReferencia" name="lugarReferencia" type="text" value="<?php echo htmlspecialchars($colonia['lugarReferencia']); ?>">

        <label for="coordenadas">Coordenadas (GPS):</label>
        <input id="coordenadas" name="coordenadas" type="text" value="<?php echo htmlspecialchars($colonia['coordenadas']); ?>" placeholder="lat,lon">

        <label for="numeroGatos">Número de Gatos (Estimado):</label>
        <input id="numeroGatos" name="numeroGatos" type="number" min="0" value="<?php echo (int)$colonia['numeroGatos']; ?>">

        <label for="idGrupo">Asignar Grupo de Trabajo:</label>
        <select id="idGrupo" name="idGrupo">
            <option value="">-- Ninguno --</option>
            <?php while ($g = mysqli_fetch_assoc($resGrupos)): ?>
                <option value="<?php echo (int)$g['idGrupoTrabajo']; ?>" <?php echo ($colonia['idGrupoTrabajo'] == $g['idGrupoTrabajo']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($g['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="descripcion">Descripción / Comentarios:</label>
        <textarea id="descripcion" name="descripcion" rows="5"><?php echo htmlspecialchars($colonia['descripcion']); ?></textarea>

        <br>
        <button type="submit"><?php echo $accion === 'crear' ? 'Guardar Colonia' : 'Actualizar Colonia'; ?></button>
        <a href="<?php echo $accion === 'crear' ? 'listar_colonias.php' : 'info_colonia.php?id=' . (int)$colonia['idColonia']; ?>">← Cancelar</a>
    </form>

    <?php mysqli_stmt_close($stmtGrupos); mysqli_close($con); ?>
</body>
</html>