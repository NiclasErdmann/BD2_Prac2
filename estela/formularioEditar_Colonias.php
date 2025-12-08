<?php
session_start();

// Comprobar sesión
if (!isset($_SESSION['idAyuntamiento']) || !isset($_SESSION['idPersona'])) {
    die('Acceso denegado. Inicia sesión.');
}

$idAyu = (int) $_SESSION['idAyuntamiento'];
$idPersona = (int) $_SESSION['idPersona'];

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

// Conexión BD
$con = mysqli_connect('localhost','root','','BD2_Prac2');
if (!$con) die('Error de conexión: ' . mysqli_connect_error());

if (!usuarioPuede($con, $idPersona, 'Modificar Colonias')) {
    mysqli_close($con);
    die('No tienes permiso para modificar colonias.');
}

// Colonia a editar
$idColonia = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($idColonia <= 0) {
    mysqli_close($con);
    die('ID de colonia no válido.');
}

// Datos de la colonia (asegurar que pertenece a tu ayuntamiento)
$sqlCol = "SELECT c.idColonia, c.nombre, c.descripcion, c.coordenadas, c.lugarReferencia, c.numeroGatos, c.idGrupoTrabajo
           FROM COLONIA_FELINA c
           LEFT JOIN GRUPO_TRABAJO gt ON c.idGrupoTrabajo = gt.idGrupoTrabajo
           WHERE c.idColonia = ? AND (gt.idAyuntamiento = ? OR c.idGrupoTrabajo IS NULL)
           LIMIT 1";
$stmtCol = mysqli_prepare($con, $sqlCol);
mysqli_stmt_bind_param($stmtCol, 'ii', $idColonia, $idAyu);
mysqli_stmt_execute($stmtCol);
$resCol = mysqli_stmt_get_result($stmtCol);

if (!$resCol || mysqli_num_rows($resCol) === 0) {
    mysqli_stmt_close($stmtCol);
    mysqli_close($con);
    die('Colonia no encontrada o no pertenece a tu ayuntamiento.');
}
$colonia = mysqli_fetch_assoc($resCol);
mysqli_stmt_close($stmtCol);

// Grupos de trabajo del ayuntamiento para el desplegable
$sqlGrupos = "SELECT idGrupoTrabajo, nombre FROM GRUPO_TRABAJO WHERE idAyuntamiento = ? ORDER BY nombre";
$stmtGrp = mysqli_prepare($con, $sqlGrupos);
mysqli_stmt_bind_param($stmtGrp, 'i', $idAyu);
mysqli_stmt_execute($stmtGrp);
$resGrupos = mysqli_stmt_get_result($stmtGrp);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar Colonia</title>
    <style>label{display:block;margin-top:8px;}input,select,textarea{width:100%;max-width:500px;padding:6px}</style>
</head>
<body>
    <h2>Editar Colonia</h2>
    <form method="post" action="colonias_guardar.php">
        <input type="hidden" name="idColonia" value="<?php echo (int)$colonia['idColonia']; ?>">

        <label for="nombre">Nombre de la Colonia:</label>
        <input id="nombre" name="nombre" type="text" required value="<?php echo htmlspecialchars($colonia['nombre']); ?>">

        <label for="lugarReferencia">Lugar de Referencia (Ubicación):</label>
        <input id="lugarReferencia" name="lugarReferencia" type="text" value="<?php echo htmlspecialchars($colonia['lugarReferencia']); ?>">

        <label for="coordenadas">Coordenadas (GPS):</label>
        <input id="coordenadas" name="coordenadas" type="text" value="<?php echo htmlspecialchars($colonia['coordenadas']); ?>">

        <label for="numeroGatos">Número de Gatos (Estimado):</label>
        <input id="numeroGatos" name="numeroGatos" type="number" min="0" value="<?php echo (int)$colonia['numeroGatos']; ?>">

        <label for="idGrupo">Asignar Grupo de Trabajo:</label>
        <select id="idGrupo" name="idGrupo">
            <option value="">-- Ninguno --</option>
            <?php while($g = mysqli_fetch_assoc($resGrupos)): ?>
                <option value="<?php echo (int)$g['idGrupoTrabajo']; ?>" <?php echo ($colonia['idGrupoTrabajo'] == $g['idGrupoTrabajo']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($g['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="descripcion">Descripción / Comentarios:</label>
        <textarea id="descripcion" name="descripcion" rows="5"><?php echo htmlspecialchars($colonia['descripcion']); ?></textarea>

        <br>
        <button type="submit">Actualizar Colonia</button>
    </form>

    <p><a href="info_colonia.php?id=<?php echo (int)$colonia['idColonia']; ?>">← Volver a la colonia</a></p>

<?php
mysqli_stmt_close($stmtGrp);
mysqli_close($con);
?>
</body>
</html>
