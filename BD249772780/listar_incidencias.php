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
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

$idPersona = $_SESSION['idPersona'];

// Obtener idVoluntario desde la tabla VOLUNTARIO
$sqlVol = "SELECT idVoluntario FROM VOLUNTARIO WHERE idPersona = ?";
$stmtVol = mysqli_prepare($con, $sqlVol);
mysqli_stmt_bind_param($stmtVol, "i", $idPersona);
mysqli_stmt_execute($stmtVol);
$resVol = mysqli_stmt_get_result($stmtVol);
$datosVol = mysqli_fetch_assoc($resVol);
mysqli_stmt_close($stmtVol);

if (!$datosVol) {
    die('Error: No se encontró el voluntario asociado a esta persona.');
}

$idVoluntario = $datosVol['idVoluntario'];

// Obtener datos completos del voluntario
$sqlVolInfo = "SELECT p.nombre, p.apellido, g.nombre as nombreGrupo
               FROM VOLUNTARIO v
               INNER JOIN PERSONA p ON v.idPersona = p.idPersona
               LEFT JOIN GRUPO_TRABAJO g ON v.idGrupoTrabajo = g.idGrupoTrabajo
               WHERE v.idVoluntario = ?";
$stmtVolInfo = mysqli_prepare($con, $sqlVolInfo);
mysqli_stmt_bind_param($stmtVolInfo, "i", $idVoluntario);
mysqli_stmt_execute($stmtVolInfo);
$resVolInfo = mysqli_stmt_get_result($stmtVolInfo);
$datosVol = mysqli_fetch_assoc($resVolInfo);
mysqli_stmt_close($stmtVolInfo);

// Obtener filtros
$filtroTipo = $_GET['tipo'] ?? '';
$filtroFechaDesde = $_GET['fechaDesde'] ?? '';
$filtroFechaHasta = $_GET['fechaHasta'] ?? '';

// Construir query con filtros
$sql = "SELECT i.idIncidencia, i.fecha, i.descripcion, i.tipo,
               g.nombre as nombreGato, g.numXIP, g.sexo,
               h.idColonia, c.nombre as nombreColonia
        FROM INCIDENCIA i
        LEFT JOIN GATO g ON i.idGato = g.idGato
        LEFT JOIN HISTORIAL h ON g.idGato = h.idGato AND h.fechaIda IS NULL
        LEFT JOIN COLONIA_FELINA c ON h.idColonia = c.idColonia
        WHERE i.idVoluntario = ?";

$params = [$idVoluntario];
$types = "i";

if (!empty($filtroTipo)) {
    $sql .= " AND i.tipo = ?";
    $params[] = $filtroTipo;
    $types .= "s";
}

if (!empty($filtroFechaDesde)) {
    $sql .= " AND i.fecha >= ?";
    $params[] = $filtroFechaDesde;
    $types .= "s";
}

if (!empty($filtroFechaHasta)) {
    $sql .= " AND i.fecha <= ?";
    $params[] = $filtroFechaHasta;
    $types .= "s";
}

$sql .= " ORDER BY i.fecha DESC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Contar total de incidencias (sin filtros)
$sqlCount = "SELECT COUNT(*) as total FROM INCIDENCIA WHERE idVoluntario = ?";
$stmtCount = mysqli_prepare($con, $sqlCount);
mysqli_stmt_bind_param($stmtCount, "i", $idVoluntario);
mysqli_stmt_execute($stmtCount);
$resCount = mysqli_stmt_get_result($stmtCount);
$totalIncidencias = mysqli_fetch_assoc($resCount)['total'];
mysqli_stmt_close($stmtCount);

// Añadir breadcrumb
addBreadcrumb('Mis Incidencias');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Incidencias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }
        .info-voluntario {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-voluntario p {
            margin: 5px 0;
            color: #0066cc;
        }
        .kpi-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .kpi-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
        }
        .kpi-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }
        .kpi-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            margin: 0;
        }
        .btn-nueva {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .btn-nueva:hover {
            background-color: #218838;
        }
        .filtros-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filtros-container h3 {
            margin-top: 0;
            color: #333;
        }
        .filtro-grupo {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filtro-item {
            flex: 1;
            min-width: 150px;
        }
        .filtro-item label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .filtro-item select,
        .filtro-item input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-filtrar {
            padding: 8px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-filtrar:hover {
            background-color: #0056b3;
        }
        .btn-limpiar {
            padding: 8px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-limpiar:hover {
            background-color: #5a6268;
        }
        .tabla-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-salud {
            background-color: #ffc107;
            color: #000;
        }
        .badge-herido {
            background-color: #fd7e14;
            color: white;
        }
        .badge-fallecimiento {
            background-color: #dc3545;
            color: white;
        }
        .badge-enfermedad {
            background-color: #e83e8c;
            color: white;
        }
        .badge-otro {
            background-color: #6c757d;
            color: white;
        }
        .no-resultados {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .btn-volver {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .btn-volver:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <div class="container">
        <h1>📋 Mis Incidencias Registradas</h1>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px;">
                ✓ Incidencia registrada correctamente
            </div>
        <?php endif; ?>

        <div class="info-voluntario">
            <p><strong>Voluntario:</strong> <?php echo htmlspecialchars($datosVol['nombre'] . ' ' . $datosVol['apellido']); ?></p>
            <?php if ($datosVol['nombreGrupo']): ?>
                <p><strong>Grupo:</strong> <?php echo htmlspecialchars($datosVol['nombreGrupo']); ?></p>
            <?php endif; ?>
        </div>

        <div class="kpi-container">
            <div class="kpi-card">
                <h3>Total Incidencias</h3>
                <p class="value"><?php echo $totalIncidencias; ?></p>
            </div>
        </div>

        <a href="listar_gatos.php?modo=incidencia" class="btn-nueva">➕ Nueva Incidencia</a>

        <!-- Filtros -->
        <div class="filtros-container">
            <h3>🔍 Filtros</h3>
            <form method="GET" action="">
                <div class="filtro-grupo">
                    <div class="filtro-item">
                        <label for="tipo">Tipo de Incidencia:</label>
                        <select name="tipo" id="tipo">
                            <option value="">Todos</option>
                            <option value="salud" <?php echo ($filtroTipo == 'salud') ? 'selected' : ''; ?>>Salud</option>
                            <option value="herido" <?php echo ($filtroTipo == 'herido') ? 'selected' : ''; ?>>Herido</option>
                            <option value="fallecimiento" <?php echo ($filtroTipo == 'fallecimiento') ? 'selected' : ''; ?>>Fallecimiento</option>
                            <option value="enfermedad" <?php echo ($filtroTipo == 'enfermedad') ? 'selected' : ''; ?>>Enfermedad</option>
                            <option value="otro" <?php echo ($filtroTipo == 'otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label for="fechaDesde">Fecha desde:</label>
                        <input type="date" name="fechaDesde" id="fechaDesde" value="<?php echo htmlspecialchars($filtroFechaDesde); ?>">
                    </div>
                    <div class="filtro-item">
                        <label for="fechaHasta">Fecha hasta:</label>
                        <input type="date" name="fechaHasta" id="fechaHasta" value="<?php echo htmlspecialchars($filtroFechaHasta); ?>">
                    </div>
                    <div class="filtro-item">
                        <button type="submit" class="btn-filtrar">Filtrar</button>
                        <a href="?" class="btn-limpiar">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de incidencias -->
        <div class="tabla-container">
            <?php if (mysqli_num_rows($resultado) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Gato</th>
                            <th>Colonia</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = 'badge-otro';
                                    switch($row['tipo']) {
                                        case 'salud': $badgeClass = 'badge-salud'; break;
                                        case 'herido': $badgeClass = 'badge-herido'; break;
                                        case 'fallecimiento': $badgeClass = 'badge-fallecimiento'; break;
                                        case 'enfermedad': $badgeClass = 'badge-enfermedad'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo strtoupper($row['tipo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($row['nombreGato'] ?? 'Sin gato');
                                    if ($row['numXIP']) {
                                        echo ' (' . htmlspecialchars($row['numXIP']) . ')';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['nombreColonia'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-resultados">
                    <p>📭 No se encontraron incidencias con los filtros aplicados.</p>
                </div>
            <?php endif; ?>
        </div>

        <a href="../menu.php" class="btn-volver">← Volver al menú</a>
    </div>
</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
