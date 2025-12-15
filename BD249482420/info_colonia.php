<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

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

// Obtener id desde GET
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('ID de colonia no válido.');
}

// Conexión a BD
$con = mysqli_connect('localhost', 'root', '', 'BD2_Prac2');
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$idPersona = isset($_SESSION['idPersona']) ? (int) $_SESSION['idPersona'] : 0;
$puedeModificar = ($idPersona > 0) ? usuarioPuede($con, $idPersona, 'Modificar Colonias') : false;

// Consulta: obtener datos de la colonia y nombre del grupo (si existe)
$sql = "SELECT c.idColonia, c.nombre, c.descripcion, c.coordenadas, c.lugarReferencia, c.numeroGatos,
               c.idGrupoTrabajo, g.nombre AS nombreGrupo
        FROM COLONIA_FELINA c
        LEFT JOIN GRUPO_TRABAJO g ON c.idGrupoTrabajo = g.idGrupoTrabajo
        WHERE c.idColonia = ? LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$res || mysqli_num_rows($res) == 0) {
    mysqli_close($con);
    die('Colonia no encontrada.');
}

$colonia = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Consulta gatos asociados (historial activo)
$sqlCats = "SELECT g.idGato, g.numXIP, g.descripcion
            FROM GATO g
            JOIN HISTORIAL h ON g.idGato = h.idGato
            WHERE h.idColonia = ? AND (h.fechaIda IS NULL OR h.fechaIda > CURDATE())
            GROUP BY g.idGato";

$stmt2 = mysqli_prepare($con, $sqlCats);
mysqli_stmt_bind_param($stmt2, 'i', $id);
mysqli_stmt_execute($stmt2);
$resCats = mysqli_stmt_get_result($stmt2);

// Añadir breadcrumb
addBreadcrumb($colonia['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Colonia: <?php echo htmlspecialchars($colonia['nombre']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { border:1px solid #ccc; padding:16px; border-radius:6px; max-width:800px; }
        h1 { margin-top:0 }
        .meta { color:#555 }
        .danger { color: #c00 }
        a.button { display:inline-block; padding:8px 12px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <div class="card">
        <h1>Colonia: <?php echo htmlspecialchars($colonia['nombre']); ?></h1>
        <p class="meta">ID: <?php echo (int)$colonia['idColonia']; ?></p>

        <hr>

        <h3>Datos generales</h3>
        <ul>
            <li><strong>Lugar de referencia:</strong> <?php echo htmlspecialchars($colonia['lugarReferencia']); ?></li>
            <li><strong>Coordenadas:</strong> <?php echo htmlspecialchars($colonia['coordenadas']); ?></li>
            <li><strong>Número de gatos:</strong> <?php echo (int)$colonia['numeroGatos']; ?></li>
        </ul>

        <h3>Descripción</h3>
        <p><?php echo nl2br(htmlspecialchars($colonia['descripcion'])); ?></p>

        <div style="margin-top:18px; padding:12px; background:#f7f7f7; border-radius:4px;">
            <h4>Grupo de trabajo asignado</h4>
            <?php if (!empty($colonia['idGrupoTrabajo'])): ?>
                <p>Esta colonia está gestionada por el grupo:</p>
                <h2>
                    <a href="info_grupoTrabajo.php?id=<?php echo (int)$colonia['idGrupoTrabajo']; ?>">
                        <?php echo htmlspecialchars($colonia['nombreGrupo']); ?>
                    </a>
                </h2>
            <?php else: ?>
                <p class="danger">⚠ Esta colonia no tiene grupo asignado.</p>
            <?php endif; ?>
        </div>

        <?php if ($puedeModificar): ?>
            <p style="margin-top:16px;">
                <a class="button" href="colonia_accion.php?accion=editar&id=<?php echo (int)$colonia['idColonia']; ?>">✏ Editar colonia</a>
            </p>
        <?php endif; ?>

        <hr>
        <h3>Gatos en esta colonia</h3>
        <?php
        if ($resCats && mysqli_num_rows($resCats) > 0) {
            echo '<ul>';
            while ($g = mysqli_fetch_assoc($resCats)) {
                echo '<li><a href="../BD249772780/ver_gato.php?idGato=' . (int)$g['idGato'] . '">' . htmlspecialchars($g['numXIP'] ?: ('Gato ' . (int)$g['idGato'])) . '</a> - ' . htmlspecialchars(substr($g['descripcion'], 0, 80)) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No hay gatos registrados actualmente en esta colonia.</p>';
        }
        mysqli_stmt_close($stmt2);
        ?>

    </div>

</body>
</html>

<?php
mysqli_close($con);
?>
