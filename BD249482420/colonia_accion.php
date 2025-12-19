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
    // Número de gatos se fuerza a 0; no editable desde el formulario
    $numeroGatos = 0;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $accion === 'crear' ? 'Crear Colonia' : 'Editar Colonia'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #ffffff;
            color: #2c3e50;
            line-height: 1.6;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header-section {
            margin-bottom: 48px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            font-size: 1rem;
            color: #666;
            font-weight: 400;
            margin-top: 8px;
        }
        
        .breadcrumb {
            margin-bottom: 24px;
            padding: 12px 0;
        }
        
        .form-container {
            background: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        
        .form-group {
            margin-bottom: 28px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #333;
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            color: #333;
            transition: all 0.2s ease;
            background-color: #ffffff;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }
        
        ::placeholder {
            color: #999;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e8e8e8;
        }
        
        .btn {
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #4a90e2;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .btn-primary:hover {
            background-color: #357abd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: #f5f5f5;
            color: #666;
            border: 1px solid #d0d0d0;
        }
        
        .btn-secondary:hover {
            background-color: #e8e8e8;
            color: #333;
        }
        
        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 18px;
            padding-right: 40px;
        }
        
        .required {
            color: #e74c3c;
            margin-left: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php displayBreadcrumbs(); ?>

        <div class="header-section">
            <h1><?php echo $accion === 'crear' ? 'Nueva Colonia' : 'Editar Colonia'; ?></h1>
            <p class="subtitle">
                <?php echo $accion === 'crear' 
                    ? 'Completa los datos para registrar una nueva colonia felina' 
                    : 'Modifica la información de la colonia'; ?>
            </p>
        </div>

        <div class="form-container">
            <form method="post" action="colonia_accion.php">
                <input type="hidden" name="accion" value="<?php echo $accion; ?>">
                <input type="hidden" name="guardar" value="1">
                <?php if ($accion === 'editar'): ?>
                    <input type="hidden" name="idColonia" value="<?php echo (int)$colonia['idColonia']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre">Nombre de la Colonia<span class="required">*</span></label>
                    <input id="nombre" name="nombre" type="text" required value="<?php echo htmlspecialchars($colonia['nombre']); ?>" placeholder="Ej: Colonia del Parque Central">
                </div>

                <div class="form-group">
                    <label for="lugarReferencia">Lugar de Referencia</label>
                    <input id="lugarReferencia" name="lugarReferencia" type="text" value="<?php echo htmlspecialchars($colonia['lugarReferencia']); ?>" placeholder="Ej: Parque Central, junto a la fuente">
                </div>

                <div class="form-group">
                    <label for="coordenadas">Coordenadas GPS</label>
                    <input id="coordenadas" name="coordenadas" type="text" value="<?php echo htmlspecialchars($colonia['coordenadas']); ?>" placeholder="40.7128, -74.0060">
                </div>

                <div class="form-group">
                    <label for="idGrupo">Asignar Grupo de Trabajo</label>
                    <select id="idGrupo" name="idGrupo">
                        <option value="">-- Sin asignar --</option>
                        <?php while ($g = mysqli_fetch_assoc($resGrupos)): ?>
                            <option value="<?php echo (int)$g['idGrupoTrabajo']; ?>" <?php echo ($colonia['idGrupoTrabajo'] == $g['idGrupoTrabajo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción / Comentarios</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Describe características especiales de la colonia, observaciones importantes..."><?php echo htmlspecialchars($colonia['descripcion']); ?></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $accion === 'crear' ? 'Guardar Colonia' : 'Actualizar Colonia'; ?>
                    </button>
                    <a href="<?php echo $accion === 'crear' ? 'listar_colonias.php' : 'info_colonia.php?id=' . (int)$colonia['idColonia']; ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php mysqli_stmt_close($stmtGrupos); mysqli_close($con); ?>
</body>
</html>