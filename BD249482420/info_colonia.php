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
$con = mysqli_connect('localhost', 'root', '', 'BD201');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colonia: <?php echo htmlspecialchars($colonia['nombre']); ?></title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header-section {
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .meta {
            color: #999;
            font-size: 0.9rem;
        }
        
        .breadcrumb {
            margin-bottom: 24px;
            padding: 12px 0;
        }
        
        .card {
            background: #ffffff;
            border-radius: 8px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
            margin-top: 32px;
        }
        
        .section-title:first-of-type {
            margin-top: 0;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
        }
        
        .info-list li {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 12px;
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
        
        .info-list strong {
            color: #666;
            font-weight: 600;
            min-width: 180px;
        }
        
        .description-text {
            color: #333;
            line-height: 1.8;
        }
        
        .card-highlight {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #d0d0d0;
            padding: 24px;
            border-radius: 8px;
            margin-top: 24px;
        }
        
        .card-highlight h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
        }
        
        .card-highlight h2 {
            font-size: 1.6rem;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 8px;
        }
        
        .card-highlight p {
            color: #666;
            margin-bottom: 8px;
        }
        
        .danger {
            color: #e74c3c;
            font-weight: 500;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4a90e2;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .btn:hover {
            background-color: #357abd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            transform: translateY(-1px);
            color: #ffffff;
            text-decoration: none;
        }
        
        .gatos-list {
            list-style: none;
            padding: 0;
        }
        
        .gatos-list li {
            padding: 14px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: baseline;
            gap: 8px;
        }
        
        .gatos-list li:last-child {
            border-bottom: none;
        }
        
        .gatos-list li::before {
            content: "🐱";
            font-size: 1.1rem;
        }
        
        a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        a:hover {
            color: #357abd;
            text-decoration: underline;
        }
        
        .empty-state {
            color: #999;
            font-style: italic;
            padding: 20px 0;
        }
        
        .divider {
            height: 1px;
            background: #e8e8e8;
            margin: 32px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php displayBreadcrumbs(); ?>

        <div class="header-section">
            <h1>Colonia: <?php echo htmlspecialchars($colonia['nombre']); ?></h1>
            <p class="meta">ID: <?php echo (int)$colonia['idColonia']; ?></p>
        </div>

        <div class="card">
            <h3 class="section-title">Datos generales</h3>
            <ul class="info-list">
                <li>
                    <strong>Lugar de referencia:</strong>
                    <span><?php echo htmlspecialchars($colonia['lugarReferencia']); ?></span>
                </li>
                <li>
                    <strong>Coordenadas:</strong>
                    <span><?php echo htmlspecialchars($colonia['coordenadas']); ?></span>
                </li>
                <li>
                    <strong>Número de gatos:</strong>
                    <span><?php echo (int)$colonia['numeroGatos']; ?></span>
                </li>
            </ul>

            <h3 class="section-title">Descripción</h3>
            <p class="description-text"><?php echo nl2br(htmlspecialchars($colonia['descripcion'])); ?></p>

            <div class="card-highlight">
                <h4>Grupo de trabajo asignado</h4>
                <?php if (!empty($colonia['idGrupoTrabajo'])): ?>
                    <p>Esta colonia está gestionada por el grupo:</p>
                    <h2>
                        <a href="info_grupoTrabajo.php?id=<?php echo (int)$colonia['idGrupoTrabajo']; ?>">
                            <?php echo htmlspecialchars($colonia['nombreGrupo']); ?>
                        </a>
                    </h2>
                <?php else: ?>
                    <p class="danger">⚠ Esta colonia no tiene grupo asignado</p>
                <?php endif; ?>
            </div>

            <?php if ($puedeModificar): ?>
                <a class="btn" href="colonia_accion.php?accion=editar&id=<?php echo (int)$colonia['idColonia']; ?>">✏ Editar colonia</a>
            <?php endif; ?>

            <div class="divider"></div>

            <h3 class="section-title">Gatos en esta colonia</h3>
            <?php
            if ($resCats && mysqli_num_rows($resCats) > 0) {
                echo '<ul class="gatos-list">';
                while ($g = mysqli_fetch_assoc($resCats)) {
                    echo '<li><a href="../BD249772780/ver_gato.php?idGato=' . (int)$g['idGato'] . '">' . htmlspecialchars($g['numXIP'] ?: ('Gato ' . (int)$g['idGato'])) . '</a> <span style="color: #666;">— ' . htmlspecialchars(substr($g['descripcion'], 0, 80)) . '</span></li>';
                }
                echo '</ul>';
            } else {
                echo '<p class="empty-state">No hay gatos registrados actualmente en esta colonia</p>';
            }
            mysqli_stmt_close($stmt2);
            ?>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
