<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Obtener el idAyuntamiento y idPersona desde la sesión
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersona = $_SESSION['idPersona'] ?? null;

if (!$idAyuntamiento || !$idPersona) {
    echo '<h2>No se ha encontrado la información necesaria en la sesión.</h2>';
    exit;
}

// Verificar si el usuario tiene la función 'Gestionar Grupos' (idFuncion = 3)
$sqlPermiso = "SELECT COUNT(*) as tienePermiso
               FROM PER_ROL PR
               INNER JOIN PUEDEHACER PH ON PR.idRol = PH.idRol
               WHERE PR.idPersona = ? AND PH.idFuncion = 3";

$stmtPermiso = mysqli_prepare($con, $sqlPermiso);
mysqli_stmt_bind_param($stmtPermiso, "i", $idPersona);
mysqli_stmt_execute($stmtPermiso);
$resultadoPermiso = mysqli_stmt_get_result($stmtPermiso);
$permiso = mysqli_fetch_assoc($resultadoPermiso);
$puedeGestionar = $permiso['tienePermiso'] > 0;

// Construir la consulta según el permiso
if ($puedeGestionar) {
    // Si puede gestionar, muestra todos los grupos del ayuntamiento
    $sql = "SELECT 
                G.idGrupoTrabajo, 
                G.nombre AS nombreGrupo, 
                P.nombre AS nombreResp, 
                P.apellido AS apellidoResp,
                (SELECT COUNT(*) 
                 FROM VOLUNTARIO V2 
                 WHERE V2.idGrupoTrabajo = G.idGrupoTrabajo) AS numVoluntarios
            FROM GRUPO_TRABAJO G
            LEFT JOIN VOLUNTARIO V ON G.idResponsable = V.idVoluntario
            LEFT JOIN PERSONA P ON V.idPersona = P.idPersona
            WHERE G.idAyuntamiento = ?";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idAyuntamiento);
} else {
    // Si solo puede ver, muestra solo los grupos a los que pertenece como voluntario
    $sql = "SELECT 
                G.idGrupoTrabajo, 
                G.nombre AS nombreGrupo, 
                P.nombre AS nombreResp, 
                P.apellido AS apellidoResp,
                (SELECT COUNT(*) 
                 FROM VOLUNTARIO V2 
                 WHERE V2.idGrupoTrabajo = G.idGrupoTrabajo) AS numVoluntarios
            FROM GRUPO_TRABAJO G
            LEFT JOIN VOLUNTARIO V ON G.idResponsable = V.idVoluntario
            LEFT JOIN PERSONA P ON V.idPersona = P.idPersona
            INNER JOIN VOLUNTARIO VOL ON G.idGrupoTrabajo = VOL.idGrupoTrabajo
            WHERE G.idAyuntamiento = ? AND VOL.idPersona = ?";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $idAyuntamiento, $idPersona);
}

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Añadir breadcrumb
addBreadcrumb('Grupos de Trabajo');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos de Trabajo</title>
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
        
        .pending {
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
        
        td.center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php displayBreadcrumbs(); ?>

        <div class="header-section">
            <h1>Grupos de Trabajo</h1>
        </div>

        <?php if ($puedeGestionar): ?>
        <div class="action-bar">
            <a href="grupo_accion.php?accion=crear" class="btn">+ Añadir Grupo de Trabajo</a>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre del Grupo</th>
                        <th>Responsable</th>
                        <th style="text-align: center;">Nº Voluntarios</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php 
                    // 3. BUCLE PARA GENERAR LAS FILAS
                    while($fila = $resultado->fetch_assoc()): 
                    ?>
                        <tr>
                            <td>
                                <a href="info_grupoTrabajo.php?id=<?php echo $fila['idGrupoTrabajo']; ?>">
                                    <?php echo htmlspecialchars($fila['nombreGrupo']); ?>
                                </a>
                            </td>
                            <td>
                                <?php 
                                // Si tiene nombre (el JOIN funcionó), lo mostramos. Si no, indicamos pendiente.
                                if (!empty($fila['nombreResp'])) {
                                    echo htmlspecialchars($fila['nombreResp'] . " " . $fila['apellidoResp']);
                                } else {
                                    echo "<span class='pending'>(Pendiente de asignar)</span>";
                                }
                                ?>
                            </td>
                            <td class="center">
                                <span class="badge"><?php echo $fila['numVoluntarios']; ?> voluntarios</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php 
                    // Mensaje opcional si no hay resultados
                    if ($resultado->num_rows === 0): 
                    ?>
                        <tr>
                            <td colspan="3" class="empty-state">
                                <?php if ($puedeGestionar): ?>
                                    No hay grupos registrados en este ayuntamiento
                                <?php else: ?>
                                    No estás asignado a ningún grupo de trabajo
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</body>
</html>