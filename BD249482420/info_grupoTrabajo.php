<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Obtener el ID del grupo desde la URL y el idPersona de la sesión
$idGrupo = $_GET['id'] ?? null;
$idPersona = $_SESSION['idPersona'] ?? null;

if (!$idGrupo) {
    echo '<h2>No se ha especificado un grupo.</h2>';
    exit;
}

if (!$idPersona) {
    echo '<h2>No se ha encontrado la información del usuario en la sesión.</h2>';
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

// Consulta para obtener la información del grupo
$sqlGrupo = "SELECT 
                G.idGrupoTrabajo,
                G.nombre AS nombreGrupo,
                G.descripcion,
                P.nombre AS nombreResp,
                P.apellido AS apellidoResp,
                P.telefono AS telefonoResp
            FROM GRUPO_TRABAJO G
            LEFT JOIN VOLUNTARIO V ON G.idResponsable = V.idVoluntario
            LEFT JOIN PERSONA P ON V.idPersona = P.idPersona
            WHERE G.idGrupoTrabajo = ?";

$stmt = mysqli_prepare($con, $sqlGrupo);
mysqli_stmt_bind_param($stmt, "i", $idGrupo);
mysqli_stmt_execute($stmt);
$resultadoGrupo = mysqli_stmt_get_result($stmt);
$grupo = mysqli_fetch_assoc($resultadoGrupo);

if (!$grupo) {
    echo '<h2>No se ha encontrado el grupo especificado.</h2>';
    exit;
}

// Consulta para obtener las colonias asignadas al grupo
$sqlColonias = "SELECT idColonia, nombre
                FROM COLONIA_FELINA
                WHERE idGrupoTrabajo = ?
                ORDER BY nombre";

$stmtColonias = mysqli_prepare($con, $sqlColonias);
mysqli_stmt_bind_param($stmtColonias, "i", $idGrupo);
mysqli_stmt_execute($stmtColonias);
$resultadoColonias = mysqli_stmt_get_result($stmtColonias);

// Consulta para obtener los voluntarios del grupo
$sqlVoluntarios = "SELECT 
                    P.nombre,
                    P.apellido
                FROM VOLUNTARIO V
                INNER JOIN PERSONA P ON V.idPersona = P.idPersona
                WHERE V.idGrupoTrabajo = ?
                ORDER BY P.nombre, P.apellido";

$stmtVoluntarios = mysqli_prepare($con, $sqlVoluntarios);
mysqli_stmt_bind_param($stmtVoluntarios, "i", $idGrupo);
mysqli_stmt_execute($stmtVoluntarios);
$resultadoVoluntarios = mysqli_stmt_get_result($stmtVoluntarios);

// Añadir breadcrumb
addBreadcrumb($grupo['nombreGrupo']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Grupo de Trabajo</title>
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
            margin-bottom: 40px;
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
        
        .breadcrumb {
            margin-bottom: 24px;
            padding: 12px 0;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background: #ffffff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        
        .card-highlight {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #d0d0d0;
        }
        
        h2, h3 {
            color: #1a1a1a;
            margin-bottom: 16px;
            font-weight: 600;
        }
        
        h2 {
            font-size: 1.8rem;
        }
        
        h3 {
            font-size: 1.3rem;
        }
        
        .section-title {
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .responsable-info h2 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .responsable-info small {
            color: #666;
            font-size: 0.95rem;
        }
        
        .pending {
            color: #999;
            font-style: italic;
            font-size: 1.1rem;
        }
        
        .two-column-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 32px;
        }
        
        @media (max-width: 768px) {
            .two-column-section {
                grid-template-columns: 1fr;
            }
        }
        
        .list-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        
        .list-card h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        ul {
            list-style: none;
            padding: 0;
        }
        
        ul li {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }
        
        ul li:last-child {
            border-bottom: none;
        }
        
        ul li::before {
            content: "•";
            color: #4a90e2;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
            margin-right: 0.5em;
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
        
        .add-link {
            display: inline-block;
            margin-top: 16px;
            padding: 8px 16px;
            background-color: #f5f5f5;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #4a90e2;
            transition: all 0.2s ease;
        }
        
        .add-link:hover {
            background-color: #e8e8e8;
            text-decoration: none;
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
            margin-top: 24px;
        }
        
        .btn:hover {
            background-color: #357abd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            transform: translateY(-1px);
            color: #ffffff;
            text-decoration: none;
        }
        
        .action-section {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e8e8e8;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php displayBreadcrumbs(); ?>

        <div class="header-section">
            <h1><?php echo htmlspecialchars($grupo['nombreGrupo']); ?></h1>
        </div>

        <div class="content-grid">
            <div class="card">
                <div class="section-title">Descripción</div>
                <p>
                    <?php 
                    if (!empty($grupo['descripcion'])) {
                        echo nl2br(htmlspecialchars($grupo['descripcion']));
                    } else {
                        echo "<span class='pending'>Sin descripción</span>";
                    }
                    ?>
                </p>
            </div>

            <div class="card card-highlight">
                <div class="section-title">👤 Responsable del Grupo</div>
                <div class="responsable-info">
                    <?php if (!empty($grupo['nombreResp'])): ?>
                        <h2><?php echo htmlspecialchars($grupo['nombreResp'] . ' ' . $grupo['apellidoResp']); ?></h2>
                        <?php if (!empty($grupo['telefonoResp'])): ?>
                            <small>📞 Tel: <?php echo htmlspecialchars($grupo['telefonoResp']); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="pending">Pendiente de asignar</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="two-column-section">
            <div class="list-card">
                <h3>🏠 Colonias Asignadas</h3>
                <?php if (mysqli_num_rows($resultadoColonias) > 0): ?>
                    <ul>
                        <?php while ($colonia = mysqli_fetch_assoc($resultadoColonias)): ?>
                            <li>
                                <a href="info_colonia.php?id=<?php echo $colonia['idColonia']; ?>">
                                    <?php echo htmlspecialchars($colonia['nombre']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="empty-state">No hay colonias asignadas a este grupo</p>
                <?php endif; ?>
                
                <?php if ($puedeGestionar): ?>
                    <a href="asignar_colonia_accion.php?idGrupo=<?php echo $idGrupo; ?>" class="add-link">+ Asignar otra colonia</a>
                <?php endif; ?>
            </div>

            <div class="list-card">
                <h3>👥 Voluntarios Miembros</h3>
                <?php if (mysqli_num_rows($resultadoVoluntarios) > 0): ?>
                    <ul>
                        <?php while ($voluntario = mysqli_fetch_assoc($resultadoVoluntarios)): ?>
                            <li><?php echo htmlspecialchars($voluntario['nombre'] . ' ' . $voluntario['apellido']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="empty-state">No hay voluntarios en este grupo</p>
                <?php endif; ?>
                
                <?php if ($puedeGestionar): ?>
                    <a href="asignar_miembro_accion.php?idGrupo=<?php echo $idGrupo; ?>" class="add-link">+ Añadir Voluntario</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($puedeGestionar): ?>
        <div class="action-section">
            <a href="grupo_accion.php?accion=editar&id=<?php echo $idGrupo; ?>" class="btn">
                ✏ Editar Datos del Grupo / Cambiar Responsable
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
