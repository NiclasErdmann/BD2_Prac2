<?php
session_start();

require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
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
    <title><?php echo $accion === 'crear' ? 'Crear Grupo de Trabajo' : 'Editar Grupo de Trabajo'; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 640px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], textarea, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .cancelar { margin-left: 10px; padding: 10px 20px; background-color: #f44336; color: white; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <h2><?php echo $accion === 'crear' ? 'Crear Nuevo Grupo de Trabajo' : 'Editar Grupo de Trabajo'; ?></h2>

    <form action="grupo_accion.php" method="POST">
        <input type="hidden" name="accion" value="<?php echo $accion; ?>">
        <input type="hidden" name="guardar" value="1">
        <?php if ($accion === 'editar'): ?>
            <input type="hidden" name="idGrupoTrabajo" value="<?php echo $grupo['idGrupoTrabajo']; ?>">
        <?php endif; ?>
        
        <label for="nombre">Nombre del Grupo:</label>
        <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($grupo['nombre']); ?>" placeholder="Ej: Grupo Zona Norte">

        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe la zona de actuación o notas importantes..."><?php echo htmlspecialchars($grupo['descripcion']); ?></textarea>

        <label for="idResponsable">Asignar Responsable (opcional):</label>
        <select id="idResponsable" name="idResponsable">
            <option value="">-- Sin asignar --</option>
            <?php while ($vol = mysqli_fetch_assoc($resultadoVol)): ?>
                <option value="<?php echo $vol['idVoluntario']; ?>" <?php echo ($vol['idVoluntario'] == $grupo['idResponsable']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($vol['nombre'] . ' ' . $vol['apellido']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit"><?php echo $accion === 'crear' ? 'Guardar Grupo' : 'Actualizar Grupo'; ?></button>
        <a href="<?php echo $accion === 'crear' ? 'listar_grupoTrabajo.php' : 'info_grupoTrabajo.php?id=' . $grupo['idGrupoTrabajo']; ?>" class="cancelar">Cancelar</a>
    </form>

</body>
</html>

<?php
mysqli_close($con);
?>