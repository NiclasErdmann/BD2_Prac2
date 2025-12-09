<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersona = $_SESSION['idPersona'] ?? null;

if (!$idAyuntamiento || !$idPersona) {
    die('Error: no se ha encontrado la información necesaria en la sesión.');
}

// Obtener el nombre del ayuntamiento
$sqlAyuntamiento = "SELECT nombre FROM AYUNTAMIENTO WHERE idAyuntamiento = ?";
$stmtAyunt = mysqli_prepare($con, $sqlAyuntamiento);
mysqli_stmt_bind_param($stmtAyunt, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtAyunt);
$resultadoAyunt = mysqli_stmt_get_result($stmtAyunt);
$ayuntamiento = mysqli_fetch_assoc($resultadoAyunt);
$nombreAyuntamiento = $ayuntamiento['nombre'] ?? 'Desconocido';

// Obtener filtros
$filtroEstado = $_GET['estado'] ?? '';
$filtroBusqueda = $_GET['busqueda'] ?? '';

// KPIs - Métricas rápidas
$sqlTotal = "SELECT COUNT(DISTINCT V.idPersona) as total FROM VOLUNTARIO V WHERE V.idAyuntamiento = ?";
$stmtTotal = mysqli_prepare($con, $sqlTotal);
mysqli_stmt_bind_param($stmtTotal, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtTotal);
$totalInscritos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];

$sqlDisponibles = "SELECT COUNT(DISTINCT V.idPersona) as disponibles 
                   FROM VOLUNTARIO V 
                   WHERE V.idAyuntamiento = ? AND V.idGrupoTrabajo IS NULL";
$stmtDisp = mysqli_prepare($con, $sqlDisponibles);
mysqli_stmt_bind_param($stmtDisp, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtDisp);
$disponibles = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDisp))['disponibles'];

$asignados = $totalInscritos - $disponibles;

// Consulta principal de voluntarios con filtros
$sql = "SELECT DISTINCT
            P.idPersona,
            P.nombre,
            P.apellido,
            P.email,
            P.telefono,
            V.idVoluntario,
            V.idGrupoTrabajo,
            G.nombre AS nombreGrupo,
            CASE 
                WHEN V.idGrupoTrabajo IS NULL THEN 'Disponible'
                ELSE 'Asignado'
            END AS estado,
            (SELECT COUNT(*) FROM PER_ROL PR JOIN ROL R ON PR.idRol = R.idRol 
             WHERE PR.idPersona = P.idPersona AND R.nombre = 'responsableGrupo') AS esResponsable
        FROM VOLUNTARIO V
        INNER JOIN PERSONA P ON V.idPersona = P.idPersona
        LEFT JOIN GRUPO_TRABAJO G ON V.idGrupoTrabajo = G.idGrupoTrabajo
        WHERE V.idAyuntamiento = ?";

$params = [$idAyuntamiento];
$types = "i";

// Aplicar filtros
if ($filtroEstado === 'disponible') {
    $sql .= " AND V.idGrupoTrabajo IS NULL";
} elseif ($filtroEstado === 'asignado') {
    $sql .= " AND V.idGrupoTrabajo IS NOT NULL";
}

if (!empty($filtroBusqueda)) {
    $sql .= " AND (P.nombre LIKE ? OR P.apellido LIKE ? OR P.email LIKE ?)";
    $busqueda = "%$filtroBusqueda%";
    $params[] = $busqueda;
    $params[] = $busqueda;
    $params[] = $busqueda;
    $types .= "sss";
}

$sql .= " ORDER BY P.nombre, P.apellido";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Añadir breadcrumb
addBreadcrumb('Gestión de Voluntarios');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Voluntarios - Borsí</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
        }
        
        h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        /* KPIs simples */
        .kpi-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .kpi-card {
            background: white;
            padding: 15px;
            border: 1px solid #ddd;
            border-left: 4px solid #667eea;
            text-align: center;
        }
        
        .kpi-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .kpi-label {
            color: #666;
            font-size: 13px;
        }
        
        /* Filtros */
        .filters-container {
            background: white;
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        
        .filter-group label {
            font-size: 12px;
            color: #666;
            font-weight: bold;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            font-size: 13px;
            width: 200px;
        }
        
        .btn-filter, .btn-clear {
            padding: 6px 15px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }
        
        .btn-filter {
            background-color: #667eea;
            color: white;
        }
        
        .btn-filter:hover {
            background-color: #5568d3;
        }
        
        .btn-clear {
            background-color: #999;
            color: white;
        }
        
        .btn-clear:hover {
            background-color: #777;
        }
        
        /* Tabla */
        .table-container {
            background: white;
            border: 1px solid #ddd;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f5f5f5;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #ddd;
            font-size: 13px;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }
        
        tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .avatar-placeholder {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #667eea;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 11px;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        .person-info {
            display: inline-block;
            vertical-align: middle;
        }
        
        .person-name {
            font-weight: bold;
            display: block;
        }
        
        .person-contact {
            font-size: 11px;
            color: #999;
        }
        
        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-disponible {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-asignado {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-responsable {
            background-color: #fff3cd;
            color: #856404;
            margin-left: 3px;
        }
        
        /* Botones */
        .action-buttons {
            display: flex;
            gap: 3px;
        }
        
        .btn-action {
            padding: 4px 8px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: #333;
        }
        
        .btn-action:hover {
            background-color: #f0f0f0;
        }
        
        .btn-action.verde {
            border-color: #28a745;
            color: #28a745;
        }
        
        .btn-action.rojo {
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .btn-action.naranja {
            border-color: #fd7e14;
            color: #fd7e14;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
        }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <h2>Gestión de Voluntarios</h2>
    
    <!-- Mensajes de éxito/error -->
    <?php if (!empty($_GET['success'])): ?>
        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; margin-bottom: 15px; border-radius: 3px;">
            ✓ Operación completada exitosamente
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; margin-bottom: 15px; border-radius: 3px;">
            ✗ Error al realizar la operación
        </div>
    <?php endif; ?>
    
    <!-- KPIs -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-label">Total Inscritos</div>
            <div class="kpi-number"><?php echo $totalInscritos; ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Disponibles</div>
            <div class="kpi-number"><?php echo $disponibles; ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Asignados</div>
            <div class="kpi-number"><?php echo $asignados; ?></div>
        </div>
    </div>
    
    <!-- Filtros -->
    <form method="GET" action="" class="filters-container">
        <div class="filter-group">
            <label>Buscar</label>
            <input type="text" name="busqueda" placeholder="Nombre o email..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
        </div>
        
        <div class="filter-group">
            <label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="disponible" <?php echo $filtroEstado === 'disponible' ? 'selected' : ''; ?>>Disponibles</option>
                <option value="asignado" <?php echo $filtroEstado === 'asignado' ? 'selected' : ''; ?>>Asignados</option>
            </select>
        </div>
        
        <button type="submit" class="btn-filter">Filtrar</button>
        <a href="?" class="btn-clear">Limpiar</a>
    </form>
    
    <!-- Tabla de Voluntarios -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Voluntario</th>
                    <th>Estado</th>
                    <th>Grupo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($vol = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td>
                                <div class="avatar-placeholder">
                                    <?php echo strtoupper(substr($vol['nombre'], 0, 1) . substr($vol['apellido'], 0, 1)); ?>
                                </div>
                                <div class="person-info">
                                    <span class="person-name"><?php echo htmlspecialchars($vol['nombre'] . ' ' . $vol['apellido']); ?></span>
                                    <span class="person-contact">
                                        <?php echo htmlspecialchars($vol['email'] ?? 'Sin email'); ?> 
                                        <?php if ($vol['telefono']): ?>| <?php echo htmlspecialchars($vol['telefono']); ?><?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if ($vol['estado'] === 'Disponible'): ?>
                                    <span class="badge badge-disponible">Disponible</span>
                                <?php else: ?>
                                    <span class="badge badge-asignado">Asignado</span>
                                <?php endif; ?>
                                <?php if ($vol['esResponsable'] > 0): ?>
                                    <span class="badge badge-responsable">Responsable</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($vol['nombreGrupo']): ?>
                                    <a href="info_grupoTrabajo.php?id=<?php echo $vol['idGrupoTrabajo']; ?>" style="text-decoration: none; color: #667eea;">
                                        <?php echo htmlspecialchars($vol['nombreGrupo']); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #999;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="ficha_voluntario.php?id=<?php echo $vol['idPersona']; ?>" class="btn-action" title="Ver">Ver</a>
                                    
                                    <?php if ($vol['estado'] === 'Disponible'): ?>
                                        <a href="voluntario_accion.php?accion=asignar&idPersona=<?php echo $vol['idPersona']; ?>" class="btn-action verde" title="Asignar">Asignar</a>
                                    <?php else: ?>
                                        <a href="voluntario_accion.php?accion=quitar&idPersona=<?php echo $vol['idPersona']; ?>" class="btn-action rojo" title="Quitar">Quitar</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($vol['esResponsable'] == 0): ?>
                                        <a href="voluntario_accion.php?accion=rol&rol_accion=promover&idPersona=<?php echo $vol['idPersona']; ?>" class="btn-action naranja" title="Responsable">Responsable</a>
                                    <?php else: ?>
                                        <a href="voluntario_accion.php?accion=rol&rol_accion=quitar&idPersona=<?php echo $vol['idPersona']; ?>" class="btn-action rojo" title="Quitar Responsable">Quitar Rol</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-state">
                            No hay voluntarios
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php
mysqli_close($con);
?>
