<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost","root","","BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

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

// Obtener idAyuntamiento desde la sesión (guardado en login.php)
if (!isset($_SESSION['idAyuntamiento']) || empty($_SESSION['idAyuntamiento'])) {
    die('Error: no se detectó ayuntamiento en la sesión. Por favor inicia sesión correctamente.');
}

$idAyuntamiento = (int) $_SESSION['idAyuntamiento'];
$idPersona = isset($_SESSION['idPersona']) ? (int) $_SESSION['idPersona'] : 0;
$puedeModificar = ($idPersona > 0) ? usuarioPuede($con, $idPersona, 'Modificar Colonias') : false;

// Obtener el nombre del ayuntamiento
$sqlAyuntamiento = "SELECT nombre FROM AYUNTAMIENTO WHERE idAyuntamiento = ?";
$stmtAyunt = mysqli_prepare($con, $sqlAyuntamiento);
mysqli_stmt_bind_param($stmtAyunt, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtAyunt);
$resultadoAyunt = mysqli_stmt_get_result($stmtAyunt);
$ayuntamiento = mysqli_fetch_assoc($resultadoAyunt);
$nombreAyuntamiento = $ayuntamiento['nombre'] ?? 'Desconocido';
mysqli_stmt_close($stmtAyunt);

// Consultar colonias del ayuntamiento con su grupo asignado
$query = "SELECT C.idColonia, C.nombre, C.lugarReferencia, C.numeroGatos,
                 G.idGrupoTrabajo, G.nombre AS nombreGrupo
          FROM COLONIA_FELINA C
          LEFT JOIN GRUPO_TRABAJO G ON C.idGrupoTrabajo = G.idGrupoTrabajo
          WHERE C.idGrupoTrabajo IN (
              SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idAyuntamiento = $idAyuntamiento
          )
          ORDER BY C.nombre";

$resultado = mysqli_query($con, $query);
if (!$resultado) {
    die('Error en la consulta: ' . mysqli_error($con));
}

// Añadir breadcrumb
addBreadcrumb('Mis Colonias');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Colonias</title>
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
            max-width: 1200px;
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
            font-size: 1.1rem;
            color: #666;
            font-weight: 400;
        }
        
        .action-bar {
            margin-bottom: 32px;
            display: flex;
            justify-content: flex-end;
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
        }
        
        .btn:hover {
            background-color: #357abd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            transform: translateY(-1px);
        }
        
        .table-container {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f8f9fa;
        }
        
        th {
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e8e8e8;
        }
        
        td {
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-size: 1rem;
        }
        
        tbody tr {
            transition: background-color 0.15s ease;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
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
            text-align: center;
            padding: 60px 20px;
            color: #999;
            font-size: 1.1rem;
        }
        
        .unassigned {
            color: #999;
            font-style: italic;
            font-size: 0.95rem;
        }
        
        .breadcrumb {
            margin-bottom: 24px;
            padding: 12px 0;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            background-color: #f0f0f0;
            color: #666;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php displayBreadcrumbs(); ?>
        
        <div class="header-section">
            <h1>Mis Colonias</h1>
            <p class="subtitle">Ayuntamiento de <?php echo htmlspecialchars($nombreAyuntamiento); ?></p>
        </div>
        
        <?php if ($puedeModificar): ?>
        <div class="action-bar">
            <a href="colonia_accion.php?accion=crear" class="btn">+ Crear Nueva Colonia</a>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Grupo de Trabajo</th>
                        <th>Número de Gatos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Mostrar cada colonia
                    if (mysqli_num_rows($resultado) > 0) {
                        while($fila = mysqli_fetch_assoc($resultado)) {
                            echo "<tr>";
                            echo "<td><a href='info_colonia.php?id=" . $fila['idColonia'] . "'>" . htmlspecialchars($fila['nombre']) . "</a></td>";
                            echo "<td>" . htmlspecialchars($fila['lugarReferencia']) . "</td>";
                            echo "<td>";
                            if (!empty($fila['idGrupoTrabajo'])) {
                                echo "<a href='info_grupoTrabajo.php?id=" . $fila['idGrupoTrabajo'] . "'>" . htmlspecialchars($fila['nombreGrupo']) . "</a>";
                            } else {
                                echo "<span class='unassigned'>Sin asignar</span>";
                            }
                            echo "</td>";
                            echo "<td><span class='badge'>" . $fila['numeroGatos'] . " gatos</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='empty-state'>No hay colonias registradas en tu ayuntamiento</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
