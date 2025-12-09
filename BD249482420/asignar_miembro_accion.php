<?php
session_start();

require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersonaSesion = $_SESSION['idPersona'] ?? null;
$idGrupo = isset($_GET['idGrupo']) ? (int)$_GET['idGrupo'] : (isset($_POST['idGrupo']) ? (int)$_POST['idGrupo'] : 0);
$guardar = $_GET['guardar'] ?? $_POST['guardar'] ?? null;

if (!$idAyuntamiento || !$idPersonaSesion || !$idGrupo) {
    die('Error: datos incompletos.');
}

// Permiso: Gestionar Grupos (idFuncion = 3)
$sqlPermiso = "SELECT COUNT(*) as tienePermiso
               FROM PER_ROL PR
               INNER JOIN PUEDEHACER PH ON PR.idRol = PH.idRol
               WHERE PR.idPersona = ? AND PH.idFuncion = 3";
$stmtPermiso = mysqli_prepare($con, $sqlPermiso);
mysqli_stmt_bind_param($stmtPermiso, "i", $idPersonaSesion);
mysqli_stmt_execute($stmtPermiso);
$permiso = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtPermiso));
$puedeGestionar = $permiso['tienePermiso'] > 0;

if (!$puedeGestionar) {
    die('Error: no tienes permisos para gestionar grupos.');
}

// Verificar que el grupo es del ayuntamiento
$sqlGrupo = "SELECT nombre FROM GRUPO_TRABAJO WHERE idGrupoTrabajo = ? AND idAyuntamiento = ?";
$stmtGrupo = mysqli_prepare($con, $sqlGrupo);
mysqli_stmt_bind_param($stmtGrupo, "ii", $idGrupo, $idAyuntamiento);
mysqli_stmt_execute($stmtGrupo);
$grupo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGrupo));

if (!$grupo) {
    die('Grupo no encontrado.');
}

if ($guardar === '1') {
    $idPersona = isset($_POST['idPersona']) ? (int)$_POST['idPersona'] : 0;
    if ($idPersona <= 0) {
        die('Error: persona no válida.');
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
    } else {
        // No es voluntario, crear nuevo registro
        $sql = "INSERT INTO VOLUNTARIO (idAyuntamiento, idGrupoTrabajo, idPersona) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $idAyuntamiento, $idGrupo, $idPersona);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die('Error al añadir el voluntario: ' . mysqli_error($con));
    }

    mysqli_stmt_close($stmt);
    header('Location: info_grupoTrabajo.php?id=' . $idGrupo);
    exit;
}

// Obtener personas que NO están en este grupo específico
$sqlPersonas = "SELECT P.idPersona, P.nombre, P.apellido, P.telefono, P.email,
                       V.idVoluntario, V.idGrupoTrabajo,
                       CASE WHEN V.idVoluntario IS NULL THEN 'Sin asignar'
                            WHEN V.idGrupoTrabajo = ? THEN 'En este grupo'
                            ELSE 'En otro grupo'
                       END AS estadoVoluntario,
                       G.nombre AS nombreGrupoActual
                FROM PERSONA P
                LEFT JOIN VOLUNTARIO V ON P.idPersona = V.idPersona AND V.idAyuntamiento = ?
                LEFT JOIN GRUPO_TRABAJO G ON V.idGrupoTrabajo = G.idGrupoTrabajo
                WHERE P.idPersona NOT IN (
                    SELECT Vol.idPersona 
                    FROM VOLUNTARIO Vol 
                    WHERE Vol.idGrupoTrabajo = ?
                )
                ORDER BY P.nombre, P.apellido";

$stmtPers = mysqli_prepare($con, $sqlPersonas);
mysqli_stmt_bind_param($stmtPers, "iii", $idGrupo, $idAyuntamiento, $idGrupo);
mysqli_stmt_execute($stmtPers);
$resultadoPers = mysqli_stmt_get_result($stmtPers);

addBreadcrumb('Grupos de Trabajo', 'listar_grupoTrabajo.php');
addBreadcrumb('Añadir Voluntario');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Voluntario al Grupo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info-box { background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        form { max-width: 600px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .cancelar { margin-left: 10px; padding: 10px 20px; background-color: #f44336; color: white; text-decoration: none; display: inline-block; }
        .nota { color: #666; font-size: 0.9em; font-style: italic; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <h2>Añadir Voluntario al Grupo</h2>

    <div class="info-box">
        <strong>Grupo:</strong> <?php echo htmlspecialchars($grupo['nombre']); ?>
    </div>

    <?php if (mysqli_num_rows($resultadoPers) > 0): ?>
        <form action="asignar_miembro_accion.php" method="POST">
            <input type="hidden" name="idGrupo" value="<?php echo $idGrupo; ?>">
            <input type="hidden" name="guardar" value="1">
            
            <label for="idPersona">Selecciona una Persona:</label>
            <select id="idPersona" name="idPersona" required>
                <option value="">-- Selecciona una persona --</option>
                <?php while ($pers = mysqli_fetch_assoc($resultadoPers)): ?>
                    <option value="<?php echo $pers['idPersona']; ?>">
                        <?php echo htmlspecialchars($pers['nombre'] . ' ' . $pers['apellido']); ?>
                        <?php if ($pers['telefono']): ?>
                            (Tel: <?php echo htmlspecialchars($pers['telefono']); ?>)
                        <?php endif; ?>
                        - <?php echo htmlspecialchars($pers['estadoVoluntario']); ?>
                        <?php if ($pers['nombreGrupoActual'] && $pers['estadoVoluntario'] === 'En otro grupo'): ?>
                            (<?php echo htmlspecialchars($pers['nombreGrupoActual']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <p class="nota">
                <strong>Sin asignar:</strong> Personas que no están en ningún grupo.<br>
                <strong>En otro grupo:</strong> Se moverá del grupo actual a este grupo.
            </p>

            <button type="submit">Añadir Voluntario</button>
            <a href="info_grupoTrabajo.php?id=<?php echo $idGrupo; ?>" class="cancelar">Cancelar</a>
        </form>
    <?php else: ?>
        <p><strong>No hay personas disponibles para añadir a este grupo.</strong></p>
        <p>Todas las personas registradas ya están asignadas a este grupo.</p>
        <br>
        <a href="info_grupoTrabajo.php?id=<?php echo $idGrupo; ?>" class="cancelar">Volver</a>
    <?php endif; ?>

    <?php mysqli_stmt_close($stmtPers); mysqli_close($con); ?>
</body>
</html>
