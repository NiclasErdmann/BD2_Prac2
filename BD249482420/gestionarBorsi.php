<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD201");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Voluntarios - Borsí</title>
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
            max-width: 1400px;
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
        
        /* Alert messages */
        .alert {
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        /* KPIs */
        .kpi-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .kpi-card {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
            border-left: 4px solid #4a90e2;
            text-align: center;
        }
        
        .kpi-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4a90e2;
            margin: 12px 0 8px 0;
        }
        
        .kpi-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Filters */
        .filters-container {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
            margin-bottom: 24px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 10px 14px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .btn-filter, .btn-clear {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-filter {
            background-color: #4a90e2;
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .btn-filter:hover {
            background-color: #357abd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            transform: translateY(-1px);
        }
        
        .btn-clear {
            background-color: #f5f5f5;
            color: #666;
            border: 1px solid #d0d0d0;
        }
        
        .btn-clear:hover {
            background-color: #e8e8e8;
            color: #333;
        }
        
        /* Table */
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
            font-size: 0.95rem;
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
        
        .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            margin-right: 12px;
            vertical-align: middle;
        }
        
        .person-info {
            display: inline-block;
            vertical-align: middle;
        }
        
        .person-name {
            font-weight: 600;
            display: block;
            color: #1a1a1a;
        }
        
        .person-contact {
            font-size: 0.85rem;
            color: #999;
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
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
            margin-left: 6px;
        }
        
        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 6px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #d0d0d0;
            background: white;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: #333;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .btn-action.verde {
            border-color: #28a745;
            color: #28a745;
        }
        
        .btn-action.verde:hover {
            background-color: #28a745;
            color: white;
        }
        
        .btn-action.rojo {
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .btn-action.rojo:hover {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-action.naranja {
            border-color: #fd7e14;
            color: #fd7e14;
        }
        
        .btn-action.naranja:hover {
            background-color: #fd7e14;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
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
    </style>
</head>
<body>
    <div class="container">
        <?php displayBreadcrumbs(); ?>
        
        <div class="header-section">
            <h1>Gestión de Voluntarios</h1>
        </div>
        
        <!-- Mensajes de éxito/error -->
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success">
                ✓ Operación completada exitosamente
            </div>
        <?php endif; ?>
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-error">
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
                                        <a href="info_grupoTrabajo.php?id=<?php echo $vol['idGrupoTrabajo']; ?>">
                                            <?php echo htmlspecialchars($vol['nombreGrupo']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
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
    </div>

</body>
</html>

<?php
mysqli_close($con);
?>
