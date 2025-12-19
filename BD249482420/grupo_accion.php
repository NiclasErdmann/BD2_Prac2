<?php
session_start();

require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersonaSesion = $_SESSION['idPersona'] ?? null;
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'crear';
$guardar = $_GET['guardar'] ?? $_POST['guardar'] ?? null;
$idGrupoTrabajo = $_GET['id'] ?? $_POST['idGrupoTrabajo'] ?? null;

if (!$idAyuntamiento || !$idPersonaSesion) {
    die('Error: datos de sesión incompletos.');
}

// Verificar permiso de gestionar grupos (idFuncion = 3)
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

// Recuperar datos existentes si se edita
$grupo = [
    'idGrupoTrabajo' => null,
    'nombre' => '',
    'descripcion' => '',
    'idResponsable' => null,
];

if ($accion === 'editar') {
    if (!$idGrupoTrabajo) {
        die('Error: ID de grupo no proporcionado.');
    }

    $sqlGrupo = "SELECT idGrupoTrabajo, nombre, descripcion, idResponsable
                 FROM GRUPO_TRABAJO
                 WHERE idGrupoTrabajo = ? AND idAyuntamiento = ?";
    $stmtGrupo = mysqli_prepare($con, $sqlGrupo);
    mysqli_stmt_bind_param($stmtGrupo, "ii", $idGrupoTrabajo, $idAyuntamiento);
    mysqli_stmt_execute($stmtGrupo);
    $resultadoGrupo = mysqli_stmt_get_result($stmtGrupo);
    $grupo = mysqli_fetch_assoc($resultadoGrupo);

    if (!$grupo) {
        die('Error: grupo no encontrado.');
    }
}

// Procesar guardado
if ($guardar === '1') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idResponsable = !empty($_POST['idResponsable']) ? (int)$_POST['idResponsable'] : null;

    if ($nombre === '') {
        die('Error: el nombre del grupo es obligatorio.');
    }

    if ($accion === 'crear') {
        $sqlInsert = "INSERT INTO GRUPO_TRABAJO (nombre, descripcion, idResponsable, idAyuntamiento)
                      VALUES (?, ?, ?, ?)";
        $stmtInsert = mysqli_prepare($con, $sqlInsert);
        mysqli_stmt_bind_param($stmtInsert, "ssii", $nombre, $descripcion, $idResponsable, $idAyuntamiento);

        if (!mysqli_stmt_execute($stmtInsert)) {
            die('Error al crear el grupo: ' . mysqli_error($con));
        }

        $nuevoId = mysqli_insert_id($con);
        header("Location: info_grupoTrabajo.php?id=$nuevoId");
        exit;
    }

    if ($accion === 'editar') {
        $idGrupoTrabajo = $_POST['idGrupoTrabajo'] ?? null;
        if (!$idGrupoTrabajo) {
            die('Error: ID de grupo no proporcionado.');
        }

        $sqlUpdate = "UPDATE GRUPO_TRABAJO
                      SET nombre = ?, descripcion = ?, idResponsable = ?
                      WHERE idGrupoTrabajo = ? AND idAyuntamiento = ?";
        $stmtUpdate = mysqli_prepare($con, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "ssiii", $nombre, $descripcion, $idResponsable, $idGrupoTrabajo, $idAyuntamiento);

        if (!mysqli_stmt_execute($stmtUpdate)) {
            die('Error al actualizar el grupo: ' . mysqli_error($con));
        }

        header("Location: info_grupoTrabajo.php?id=$idGrupoTrabajo");
        exit;
    }

    die('Error: acción no válida.');
}

// Cargar lista de responsables disponibles
$sqlVoluntarios = "SELECT V.idVoluntario, P.nombre, P.apellido
                   FROM VOLUNTARIO V
                   INNER JOIN PERSONA P ON V.idPersona = P.idPersona
                   INNER JOIN PER_ROL PR ON P.idPersona = PR.idPersona
                   INNER JOIN ROL R ON PR.idRol = R.idRol
                   WHERE V.idAyuntamiento = ? AND R.nombre = 'responsableGrupo'
                   ORDER BY P.nombre, P.apellido";

$stmtVol = mysqli_prepare($con, $sqlVoluntarios);
mysqli_stmt_bind_param($stmtVol, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtVol);
$resultadoVol = mysqli_stmt_get_result($stmtVol);

addBreadcrumb('Grupos de Trabajo', 'listar_grupoTrabajo.php');
addBreadcrumb($accion === 'crear' ? 'Crear Grupo de Trabajo' : 'Editar Grupo');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $accion === 'crear' ? 'Crear Grupo de Trabajo' : 'Editar Grupo de Trabajo'; ?></title>
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
            <h1><?php echo $accion === 'crear' ? 'Crear Nuevo Grupo de Trabajo' : 'Editar Grupo de Trabajo'; ?></h1>
            <p class="subtitle">
                <?php echo $accion === 'crear' 
                    ? 'Completa los datos para crear un nuevo grupo de trabajo' 
                    : 'Modifica la información del grupo de trabajo'; ?>
            </p>
        </div>

        <div class="form-container">
            <form action="grupo_accion.php" method="POST">
                <input type="hidden" name="accion" value="<?php echo $accion; ?>">
                <input type="hidden" name="guardar" value="1">
                <?php if ($accion === 'editar'): ?>
                    <input type="hidden" name="idGrupoTrabajo" value="<?php echo $grupo['idGrupoTrabajo']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nombre">Nombre del Grupo<span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        required 
                        value="<?php echo htmlspecialchars($grupo['nombre']); ?>" 
                        placeholder="Ej: Grupo Zona Norte">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea 
                        id="descripcion" 
                        name="descripcion" 
                        placeholder="Describe la zona de actuación o notas importantes..."><?php echo htmlspecialchars($grupo['descripcion']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="idResponsable">Asignar Responsable</label>
                    <select id="idResponsable" name="idResponsable">
                        <option value="">-- Sin asignar --</option>
                        <?php while ($vol = mysqli_fetch_assoc($resultadoVol)): ?>
                            <option value="<?php echo $vol['idVoluntario']; ?>" <?php echo ($vol['idVoluntario'] == $grupo['idResponsable']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($vol['nombre'] . ' ' . $vol['apellido']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $accion === 'crear' ? 'Guardar Grupo' : 'Actualizar Grupo'; ?>
                    </button>
                    <a href="<?php echo $accion === 'crear' ? 'listar_grupoTrabajo.php' : 'info_grupoTrabajo.php?id=' . $grupo['idGrupoTrabajo']; ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>