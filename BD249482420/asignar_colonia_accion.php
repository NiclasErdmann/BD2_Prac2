<?php
session_start();

require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersona = $_SESSION['idPersona'] ?? null;
$idGrupo = isset($_GET['idGrupo']) ? (int)$_GET['idGrupo'] : (isset($_POST['idGrupo']) ? (int)$_POST['idGrupo'] : 0);
$guardar = $_GET['guardar'] ?? $_POST['guardar'] ?? null;

if (!$idAyuntamiento || !$idPersona || !$idGrupo) {
    die('Error: datos incompletos.');
}

// Permiso: Gestionar Grupos (idFuncion = 3)
$sqlPermiso = "SELECT COUNT(*) as tienePermiso
               FROM PER_ROL PR
               INNER JOIN PUEDEHACER PH ON PR.idRol = PH.idRol
               WHERE PR.idPersona = ? AND PH.idFuncion = 3";
$stmtPermiso = mysqli_prepare($con, $sqlPermiso);
mysqli_stmt_bind_param($stmtPermiso, "i", $idPersona);
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
    $idColonia = isset($_POST['idColonia']) ? (int)$_POST['idColonia'] : 0;
    if ($idColonia <= 0) {
        die('Error: colonia no válida.');
    }

    // Reasigna la colonia al grupo
    $sqlUpdate = "UPDATE COLONIA_FELINA SET idGrupoTrabajo = ? WHERE idColonia = ?";
    $stmtUpdate = mysqli_prepare($con, $sqlUpdate);
    mysqli_stmt_bind_param($stmtUpdate, "ii", $idGrupo, $idColonia);

    if (!mysqli_stmt_execute($stmtUpdate)) {
        die('Error al asignar la colonia: ' . mysqli_error($con));
    }

    header('Location: info_grupoTrabajo.php?id=' . $idGrupo);
    exit;
}

// Obtener colonias disponibles del ayuntamiento (sin asignar o en cualquier grupo del mismo ayuntamiento)
$sqlColonias = "SELECT C.idColonia, C.nombre, C.lugarReferencia,
                       CASE WHEN C.idGrupoTrabajo IS NULL THEN 'Sin asignar'
                            ELSE G.nombre END AS grupoActual
                FROM COLONIA_FELINA C
                LEFT JOIN GRUPO_TRABAJO G ON C.idGrupoTrabajo = G.idGrupoTrabajo
                WHERE C.idColonia IN (
                    SELECT CF.idColonia
                    FROM COLONIA_FELINA CF
                    LEFT JOIN GRUPO_TRABAJO GT ON CF.idGrupoTrabajo = GT.idGrupoTrabajo
                    WHERE GT.idAyuntamiento = ? OR CF.idGrupoTrabajo IS NULL
                )
                ORDER BY C.nombre";

$stmtCol = mysqli_prepare($con, $sqlColonias);
mysqli_stmt_bind_param($stmtCol, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtCol);
$resultadoCol = mysqli_stmt_get_result($stmtCol);

addBreadcrumb('Grupos de Trabajo', 'listar_grupoTrabajo.php');
addBreadcrumb('Asignar Colonia');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Colonia al Grupo</title>
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

    <h2>Asignar Colonia al Grupo</h2>

    <div class="info-box">
        <strong>Grupo:</strong> <?php echo htmlspecialchars($grupo['nombre']); ?>
    </div>

    <form action="asignar_colonia_accion.php" method="POST">
        <input type="hidden" name="idGrupo" value="<?php echo $idGrupo; ?>">
        <input type="hidden" name="guardar" value="1">
        
        <label for="idColonia">Selecciona una Colonia:</label>
        <select id="idColonia" name="idColonia" required>
            <option value="">-- Selecciona una colonia --</option>
            <?php while ($col = mysqli_fetch_assoc($resultadoCol)): ?>
                <option value="<?php echo $col['idColonia']; ?>">
                    <?php echo htmlspecialchars($col['nombre']); ?> 
                    (<?php echo htmlspecialchars($col['lugarReferencia']); ?>) 
                    - Actualmente: <?php echo htmlspecialchars($col['grupoActual']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <p class="nota">Si una colonia ya está asignada a otro grupo, al seleccionarla se reasignará a este grupo.</p>

        <button type="submit">Asignar Colonia</button>
        <a href="info_grupoTrabajo.php?id=<?php echo $idGrupo; ?>" class="cancelar">Cancelar</a>
    </form>

    <?php mysqli_stmt_close($stmtCol); mysqli_close($con); ?>
</body>
</html>
