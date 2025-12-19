<?php
session_start();

require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersona = $_GET['idPersona'] ?? $_POST['idPersona'] ?? null;
$accion = $_GET['accion'] ?? $_POST['accion'] ?? null;
$guardar = $_GET['guardar'] ?? $_POST['guardar'] ?? null;

if (!$idAyuntamiento || !$idPersona) {
    die('Error: parámetros inválidos');
}

// Verificar que el usuario es administrador
$sqlCheck = "SELECT COUNT(*) as count FROM ADMINAYU WHERE idAyuntamiento = ? AND idPersona = ?";
$stmtCheck = mysqli_prepare($con, $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, "ii", $idAyuntamiento, $_SESSION['idPersona']);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck))['count'];

if (!$resultCheck) {
    die('Error: no tienes permisos para realizar esta acción');
}

// ============================================
// ACCIÓN: ASIGNAR A GRUPO
// ============================================
if ($accion === 'asignar') {
    // GET: Mostrar formulario
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Obtener datos del voluntario
        $sqlVol = "SELECT V.idVoluntario, P.nombre, P.apellido, V.idGrupoTrabajo, G.nombre as nombreGrupo
                   FROM VOLUNTARIO V
                   INNER JOIN PERSONA P ON V.idPersona = P.idPersona
                   LEFT JOIN GRUPO_TRABAJO G ON V.idGrupoTrabajo = G.idGrupoTrabajo
                   WHERE V.idPersona = ? AND V.idAyuntamiento = ?";
        $stmtVol = mysqli_prepare($con, $sqlVol);
        mysqli_stmt_bind_param($stmtVol, "ii", $idPersona, $idAyuntamiento);
        mysqli_stmt_execute($stmtVol);
        $voluntario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVol));

        if (!$voluntario) {
            die('Error: voluntario no encontrado');
        }

        // Obtener lista de grupos de trabajo
        $sqlGrupos = "SELECT idGrupoTrabajo, nombre FROM GRUPO_TRABAJO WHERE idAyuntamiento = ? ORDER BY nombre";
        $stmtGrupos = mysqli_prepare($con, $sqlGrupos);
        mysqli_stmt_bind_param($stmtGrupos, "i", $idAyuntamiento);
        mysqli_stmt_execute($stmtGrupos);
        $resultGrupos = mysqli_stmt_get_result($stmtGrupos);

        addBreadcrumb('Gestión de Voluntarios', 'gestionarBorsi.php');
        addBreadcrumb('Asignar Grupo');
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Asignar Grupo</title>
            <style>
                * { box-sizing: border-box; }
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
                h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
                .form-container { background: white; border: 1px solid #ddd; padding: 20px; max-width: 500px; margin-bottom: 20px; }
                .form-group { margin-bottom: 15px; }
                .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
                .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; font-size: 13px; }
                .form-group.readonly input { background-color: #f5f5f5; }
                .form-buttons { display: flex; gap: 10px; margin-top: 20px; }
                .btn { padding: 8px 20px; border: none; cursor: pointer; font-weight: bold; font-size: 13px; }
                .btn-primary { background-color: #667eea; color: white; }
                .btn-primary:hover { background-color: #5568d3; }
                .btn-secondary { background-color: #999; color: white; text-decoration: none; text-align: center; }
                .btn-secondary:hover { background-color: #777; }
                .info-box { background-color: #f9f9f9; border-left: 4px solid #667eea; padding: 12px; margin-bottom: 15px; font-size: 13px; }
            </style>
        </head>
        <body>
            <?php displayBreadcrumbs(); ?>
            <h2>Asignar Grupo a Voluntario</h2>
            <div class="form-container">
                <div class="info-box">
                    <strong><?php echo htmlspecialchars($voluntario['nombre'] . ' ' . $voluntario['apellido']); ?></strong>
                    <?php if ($voluntario['nombreGrupo']): ?>
                        <br>Grupo actual: <strong><?php echo htmlspecialchars($voluntario['nombreGrupo']); ?></strong>
                    <?php else: ?>
                        <br><em>No tiene grupo asignado</em>
                    <?php endif; ?>
                </div>
                <form method="POST" action="voluntario_accion.php">
                    <input type="hidden" name="idPersona" value="<?php echo $idPersona; ?>">
                    <input type="hidden" name="accion" value="asignar">
                    <input type="hidden" name="guardar" value="1">
                    <div class="form-group">
                        <label>Asignar a Grupo</label>
                        <select name="idGrupoTrabajo" required>
                            <option value="">-- Selecciona un grupo --</option>
                            <?php while ($grupo = mysqli_fetch_assoc($resultGrupos)): ?>
                                <option value="<?php echo $grupo['idGrupoTrabajo']; ?>" 
                                    <?php echo $voluntario['idGrupoTrabajo'] == $grupo['idGrupoTrabajo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($grupo['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="gestionarBorsi.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </body>
        </html>
        <?php
    }
    // POST: Procesar asignación
    elseif ($guardar === '1') {
        $idGrupoTrabajo = $_POST['idGrupoTrabajo'] ?? null;

        if (!$idGrupoTrabajo) {
            die('Error: parámetros inválidos');
        }

        // Verificar que el grupo existe
        $sqlGrupo = "SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idGrupoTrabajo = ? AND idAyuntamiento = ?";
        $stmtGrupo = mysqli_prepare($con, $sqlGrupo);
        mysqli_stmt_bind_param($stmtGrupo, "ii", $idGrupoTrabajo, $idAyuntamiento);
        mysqli_stmt_execute($stmtGrupo);

        if (mysqli_num_rows(mysqli_stmt_get_result($stmtGrupo)) == 0) {
            die('Error: grupo no encontrado');
        }

        // Verificar que el voluntario existe
        $sqlVol = "SELECT idVoluntario FROM VOLUNTARIO WHERE idPersona = ? AND idAyuntamiento = ?";
        $stmtVol = mysqli_prepare($con, $sqlVol);
        mysqli_stmt_bind_param($stmtVol, "ii", $idPersona, $idAyuntamiento);
        mysqli_stmt_execute($stmtVol);

        if (mysqli_num_rows(mysqli_stmt_get_result($stmtVol)) == 0) {
            die('Error: voluntario no encontrado');
        }

        // Actualizar grupo de trabajo del voluntario
        $sqlUpdate = "UPDATE VOLUNTARIO SET idGrupoTrabajo = ? WHERE idPersona = ? AND idAyuntamiento = ?";
        $stmtUpdate = mysqli_prepare($con, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "iii", $idGrupoTrabajo, $idPersona, $idAyuntamiento);

        if (mysqli_stmt_execute($stmtUpdate)) {
            header('Location: gestionarBorsi.php?success=asignado');
            exit;
        } else {
            header('Location: gestionarBorsi.php?error=asignacion');
            exit;
        }
    }
}

// ============================================
// ACCIÓN: QUITAR DEL GRUPO
// ============================================
elseif ($accion === 'quitar') {
    // GET: Mostrar confirmación
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sqlVol = "SELECT V.idVoluntario, P.nombre, P.apellido, V.idGrupoTrabajo, G.nombre as nombreGrupo
                   FROM VOLUNTARIO V
                   INNER JOIN PERSONA P ON V.idPersona = P.idPersona
                   LEFT JOIN GRUPO_TRABAJO G ON V.idGrupoTrabajo = G.idGrupoTrabajo
                   WHERE V.idPersona = ? AND V.idAyuntamiento = ?";
        $stmtVol = mysqli_prepare($con, $sqlVol);
        mysqli_stmt_bind_param($stmtVol, "ii", $idPersona, $idAyuntamiento);
        mysqli_stmt_execute($stmtVol);
        $voluntario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVol));

        if (!$voluntario) {
            die('Error: voluntario no encontrado');
        }

        if (!$voluntario['idGrupoTrabajo']) {
            die('Error: el voluntario no tiene grupo asignado');
        }

        addBreadcrumb('Gestión de Voluntarios', 'gestionarBorsi.php');
        addBreadcrumb('Quitar del Grupo');
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Quitar del Grupo</title>
            <style>
                * { box-sizing: border-box; }
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
                h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
                .form-container { background: white; border: 1px solid #ddd; padding: 20px; max-width: 500px; margin-bottom: 20px; }
                .warning-box { background-color: #fff3cd; border-left: 4px solid #fd7e14; padding: 12px; margin-bottom: 15px; font-size: 13px; color: #856404; }
                .info-box { background-color: #f9f9f9; border-left: 4px solid #667eea; padding: 12px; margin-bottom: 15px; font-size: 13px; }
                .form-buttons { display: flex; gap: 10px; margin-top: 20px; }
                .btn { padding: 8px 20px; border: none; cursor: pointer; font-weight: bold; font-size: 13px; }
                .btn-danger { background-color: #dc3545; color: white; }
                .btn-danger:hover { background-color: #c82333; }
                .btn-secondary { background-color: #999; color: white; text-decoration: none; text-align: center; }
                .btn-secondary:hover { background-color: #777; }
            </style>
        </head>
        <body>
            <?php displayBreadcrumbs(); ?>
            <h2>Quitar Voluntario del Grupo</h2>
            <div class="form-container">
                <div class="warning-box">
                    <strong>⚠️ Acción Irreversible</strong><br>
                    Se quitará al voluntario del grupo <?php echo htmlspecialchars($voluntario['nombreGrupo']); ?>
                </div>
                <div class="info-box">
                    <strong><?php echo htmlspecialchars($voluntario['nombre'] . ' ' . $voluntario['apellido']); ?></strong><br>
                    Grupo: <strong><?php echo htmlspecialchars($voluntario['nombreGrupo']); ?></strong>
                </div>
                <form method="POST" action="voluntario_accion.php">
                    <input type="hidden" name="idPersona" value="<?php echo $idPersona; ?>">
                    <input type="hidden" name="accion" value="quitar">
                    <input type="hidden" name="guardar" value="1">
                    <p style="color: #666; font-size: 13px;">
                        ¿Estás seguro de que quieres quitar a este voluntario del grupo?
                    </p>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-danger">Sí, Quitar</button>
                        <a href="gestionarBorsi.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </body>
        </html>
        <?php
    }
    // POST: Procesar eliminación
    elseif ($guardar === '1') {
        // Verificar que el voluntario existe
        $sqlVol = "SELECT idVoluntario FROM VOLUNTARIO WHERE idPersona = ? AND idAyuntamiento = ?";
        $stmtVol = mysqli_prepare($con, $sqlVol);
        mysqli_stmt_bind_param($stmtVol, "ii", $idPersona, $idAyuntamiento);
        mysqli_stmt_execute($stmtVol);

        if (mysqli_num_rows(mysqli_stmt_get_result($stmtVol)) == 0) {
            die('Error: voluntario no encontrado');
        }

        // Quitar grupo de trabajo (set a NULL)
        $sqlUpdate = "UPDATE VOLUNTARIO SET idGrupoTrabajo = NULL WHERE idPersona = ? AND idAyuntamiento = ?";
        $stmtUpdate = mysqli_prepare($con, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "ii", $idPersona, $idAyuntamiento);

        if (mysqli_stmt_execute($stmtUpdate)) {
            header('Location: gestionarBorsi.php?success=quitado');
            exit;
        } else {
            header('Location: gestionarBorsi.php?error=quitar');
            exit;
        }
    }
}

// ============================================
// ACCIÓN: CAMBIAR ROL (promover/quitar responsable)
// ============================================
elseif ($accion === 'rol') {
    $rolAccion = $_GET['rol_accion'] ?? $_POST['rol_accion'] ?? null;

    if (!$rolAccion || !in_array($rolAccion, ['promover', 'quitar'])) {
        die('Error: acción de rol inválida');
    }

    // Verificar que el usuario es administrador (ya hecho arriba)

    // Obtener ID del rol responsableGrupo
    $sqlRol = "SELECT idRol FROM ROL WHERE nombre = 'responsableGrupo'";
    $idRolResponsable = mysqli_fetch_assoc(mysqli_query($con, $sqlRol))['idRol'];

    if ($rolAccion === 'promover') {
        // Insertar relación PER_ROL si no existe
        $sqlInsert = "INSERT IGNORE INTO PER_ROL (idPersona, idRol) VALUES (?, ?)";
        $stmtIns = mysqli_prepare($con, $sqlInsert);
        mysqli_stmt_bind_param($stmtIns, "ii", $idPersona, $idRolResponsable);

        if (mysqli_stmt_execute($stmtIns)) {
            header('Location: gestionarBorsi.php?success=promovido');
            exit;
        } else {
            header('Location: gestionarBorsi.php?error=promover');
            exit;
        }
    } elseif ($rolAccion === 'quitar') {
        // Eliminar relación PER_ROL
        $sqlDelete = "DELETE FROM PER_ROL WHERE idPersona = ? AND idRol = ?";
        $stmtDel = mysqli_prepare($con, $sqlDelete);
        mysqli_stmt_bind_param($stmtDel, "ii", $idPersona, $idRolResponsable);

        if (mysqli_stmt_execute($stmtDel)) {
            header('Location: gestionarBorsi.php?success=eliminado');
            exit;
        } else {
            header('Location: gestionarBorsi.php?error=eliminar');
            exit;
        }
    }
}

else {
    die('Error: acción no especificada');
}

mysqli_close($con);
?>
