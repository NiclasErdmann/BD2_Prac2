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

// Obtener filtros
$filtroNombre = $_GET['nombre'] ?? '';
$filtroColor = $_GET['color'] ?? '';
$filtroXIP = $_GET['xip'] ?? '';
$filtroSexo = $_GET['sexo'] ?? '';

// Construir query con filtros
$sql = "SELECT g.idGato, g.nombre, g.numXIP, g.sexo, g.descripcion,
               c.nombre as nombreColonia, h.idColonia
        FROM GATO g
        LEFT JOIN HISTORIAL h ON g.idGato = h.idGato AND h.fechaIda IS NULL
        LEFT JOIN COLONIA_FELINA c ON h.idColonia = c.idColonia
        WHERE g.idCementerio IS NULL";

$params = [];
$types = "";

if (!empty($filtroNombre)) {
    $sql .= " AND g.nombre LIKE ?";
    $params[] = "%$filtroNombre%";
    $types .= "s";
}

if (!empty($filtroColor)) {
    $sql .= " AND g.descripcion LIKE ?";
    $params[] = "%$filtroColor%";
    $types .= "s";
}

if (!empty($filtroXIP)) {
    $sql .= " AND g.numXIP LIKE ?";
    $params[] = "%$filtroXIP%";
    $types .= "s";
}

if (!empty($filtroSexo)) {
    $sql .= " AND g.sexo = ?";
    $params[] = $filtroSexo;
    $types .= "s";
}

$sql .= " ORDER BY g.nombre";

$stmt = mysqli_prepare($con, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Obtener todas las colonias para el dropdown
$sqlColonias = "SELECT idColonia, nombre FROM COLONIA_FELINA ORDER BY nombre";
$resColonias = mysqli_query($con, $sqlColonias);

// Añadir breadcrumb
addBreadcrumb('Albirament Gato');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albirament Gato</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1400px;
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
        .info-box {
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #0c5460;
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
        .gatos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .gato-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .gato-card h3 {
            margin-top: 0;
            color: #333;
        }
        .gato-info {
            margin: 10px 0;
        }
        .gato-info strong {
            color: #555;
        }
        .colonia-actual {
            background-color: #e7f3ff;
            padding: 8px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
            color: #0066cc;
        }
        .form-albirament {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .form-albirament h4 {
            margin-top: 0;
            color: #856404;
        }
        .form-albirament select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .btn-registrar {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }
        .btn-registrar:hover {
            background-color: #218838;
        }
        .no-resultados {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 8px;
            color: #666;
        }
        .badge-sexo {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-macho {
            background-color: #6c757d;
            color: white;
        }
        .badge-hembra {
            background-color: #6c757d;
            color: white;
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
        <h1>🔍 Albirament de Gatos</h1>
        <p class="subtitle">Registra el avistamiento de un gato en una colonia diferente</p>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px;">
                ✓ Albirament registrado correctamente. El historial del gato ha sido actualizado.
            </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>ℹ️ ¿Cómo funciona?</strong><br>
            1. Usa los filtros para buscar el gato que has visto<br>
            2. Selecciona la nueva colonia donde lo has avistado<br>
            3. Confirma el albirament - se actualizará automáticamente el historial del gato
        </div>

        <!-- Filtros -->
        <div class="filtros-container">
            <h3>🔎 Buscar Gato</h3>
            <form method="GET" action="">
                <div class="filtro-grupo">
                    <div class="filtro-item">
                        <label for="nombre">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($filtroNombre); ?>" placeholder="Ej: Bigotes">
                    </div>
                    <div class="filtro-item">
                        <label for="color">Color/Descripción:</label>
                        <input type="text" name="color" id="color" value="<?php echo htmlspecialchars($filtroColor); ?>" placeholder="Ej: naranja, blanco">
                    </div>
                    <div class="filtro-item">
                        <label for="xip">Número XIP:</label>
                        <input type="text" name="xip" id="xip" value="<?php echo htmlspecialchars($filtroXIP); ?>" placeholder="Ej: XIP-007">
                    </div>
                    <div class="filtro-item">
                        <label for="sexo">Sexo:</label>
                        <select name="sexo" id="sexo">
                            <option value="">Todos</option>
                            <option value="Macho" <?php echo ($filtroSexo == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                            <option value="Hembra" <?php echo ($filtroSexo == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <button type="submit" class="btn-filtrar">Buscar</button>
                        <a href="?" class="btn-limpiar">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Grid de gatos -->
        <?php if (mysqli_num_rows($resultado) > 0): ?>
            <div class="gatos-grid">
                <?php while ($gato = mysqli_fetch_assoc($resultado)): ?>
                    <div class="gato-card">
                        <h3><?php echo htmlspecialchars($gato['nombre']); ?></h3>
                        
                        <div class="gato-info">
                            <?php if ($gato['numXIP']): ?>
                                <strong>XIP:</strong> <?php echo htmlspecialchars($gato['numXIP']); ?><br>
                            <?php endif; ?>
                            
                            <?php if ($gato['sexo']): ?>
                                <strong>Sexo:</strong> 
                                <span class="badge-sexo <?php echo ($gato['sexo'] == 'Macho') ? 'badge-macho' : 'badge-hembra'; ?>">
                                    <?php echo ($gato['sexo'] == 'Macho') ? 'Macho' : 'Hembra'; ?>
                                </span><br>
                            <?php endif; ?>
                            
                            <?php if ($gato['descripcion']): ?>
                                <strong>Descripción:</strong> <?php echo htmlspecialchars($gato['descripcion']); ?><br>
                            <?php endif; ?>
                        </div>

                        <?php if ($gato['nombreColonia']): ?>
                            <div class="colonia-actual">
                                📍 Colonia actual: <?php echo htmlspecialchars($gato['nombreColonia']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="procesar_albirament.php" method="POST" class="form-albirament">
                            <h4>Registrar avistamiento</h4>
                            <input type="hidden" name="idGato" value="<?php echo $gato['idGato']; ?>">
                            
                            <label for="nuevaColonia_<?php echo $gato['idGato']; ?>">Nueva colonia donde lo has visto:</label>
                            <input type="text" list="colonias_<?php echo $gato['idGato']; ?>" name="coloniaInput_<?php echo $gato['idGato']; ?>" id="nuevaColonia_<?php echo $gato['idGato']; ?>" placeholder="Escribe para buscar..." required style="width: 100%; padding: 8px; border: 1px solid #ddd; margin-bottom: 10px;">
                            <input type="hidden" name="idColoniaNueva" id="idColoniaNueva_<?php echo $gato['idGato']; ?>">
                            <datalist id="colonias_<?php echo $gato['idGato']; ?>">
                                <?php 
                                mysqli_data_seek($resColonias, 0);
                                while ($col = mysqli_fetch_assoc($resColonias)): 
                                    if ($col['idColonia'] != $gato['idColonia']):
                                ?>
                                    <option value="<?php echo htmlspecialchars($col['nombre']); ?>" data-id="<?php echo $col['idColonia']; ?>">
                                <?php 
                                    endif;
                                endwhile; 
                                ?>
                            </datalist>
                            <script>
                            document.getElementById('nuevaColonia_<?php echo $gato['idGato']; ?>').addEventListener('input', function() {
                                var input = this.value;
                                var datalist = document.getElementById('colonias_<?php echo $gato['idGato']; ?>');
                                var options = datalist.querySelectorAll('option');
                                var found = false;
                                for (var i = 0; i < options.length; i++) {
                                    if (options[i].value === input) {
                                        document.getElementById('idColoniaNueva_<?php echo $gato['idGato']; ?>').value = options[i].getAttribute('data-id');
                                        found = true;
                                        break;
                                    }
                                }
                                if (!found) {
                                    document.getElementById('idColoniaNueva_<?php echo $gato['idGato']; ?>').value = '';
                                }
                            });
                            </script>
                            
                            <button type="submit" class="btn-registrar">Registrar Albirament</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-resultados">
                <p>📭 No se encontraron gatos con los criterios de búsqueda.</p>
                <p>Intenta ajustar los filtros.</p>
            </div>
        <?php endif; ?>

        <a href="../menu.php" class="btn-volver">← Volver al menú</a>
    </div>
</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
