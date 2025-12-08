<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión y obtener ID del grupo
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idGrupo = $_GET['id'] ?? null;

if (!$idAyuntamiento || !$idGrupo) {
    die('Error: datos incompletos.');
}

// Obtener datos del grupo
$sqlGrupo = "SELECT G.idGrupoTrabajo, G.nombre, G.descripcion, G.idResponsable
             FROM GRUPO_TRABAJO G
             WHERE G.idGrupoTrabajo = ? AND G.idAyuntamiento = ?";

$stmtGrupo = mysqli_prepare($con, $sqlGrupo);
mysqli_stmt_bind_param($stmtGrupo, "ii", $idGrupo, $idAyuntamiento);
mysqli_stmt_execute($stmtGrupo);
$resultadoGrupo = mysqli_stmt_get_result($stmtGrupo);
$grupo = mysqli_fetch_assoc($resultadoGrupo);

if (!$grupo) {
    die('Grupo no encontrado.');
}

// Obtener solo los voluntarios con rol de responsable del ayuntamiento
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

// Añadir breadcrumb
addBreadcrumb('Editar Grupo');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Grupo de Trabajo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], textarea, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .cancelar { margin-left: 10px; padding: 10px 20px; background-color: #f44336; color: white; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <h2>Editar Grupo de Trabajo</h2>

    <form action="grupoTrabajo_guardar.php" method="POST">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="idGrupoTrabajo" value="<?php echo $grupo['idGrupoTrabajo']; ?>">
        
        <label for="nombre">Nombre del Grupo:</label>
        <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($grupo['nombre']); ?>">

        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($grupo['descripcion'] ?? ''); ?></textarea>

        <label for="idResponsable">Asignar Responsable:</label>
        <select id="idResponsable" name="idResponsable">
            <option value="">-- Sin asignar --</option>
            <?php while ($vol = mysqli_fetch_assoc($resultadoVol)): ?>
                <option value="<?php echo $vol['idVoluntario']; ?>" 
                    <?php echo ($vol['idVoluntario'] == $grupo['idResponsable']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($vol['nombre'] . ' ' . $vol['apellido']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Actualizar Grupo</button>
        <a href="info_grupoTrabajo.php?id=<?php echo $grupo['idGrupoTrabajo']; ?>" class="cancelar">Cancelar</a>
    </form>

</body>
</html>

<?php
mysqli_close($con);
?>
