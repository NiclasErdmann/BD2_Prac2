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
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

// Obtener el modo de operación (albirament o incidencia)
$modo = $_GET['modo'] ?? 'ver'; // Valores: 'ver', 'albirament', 'incidencia'

// Obtener filtros
$filtroNombre = $_GET['nombre'] ?? '';
$filtroColor = $_GET['color'] ?? '';
$filtroXIP = $_GET['xip'] ?? '';
$filtroSexo = $_GET['sexo'] ?? '';
$filtroColonia = $_GET['colonia'] ?? '';

// Construir query con filtros
$sql = "SELECT g.idGato, g.nombre, g.numXIP, g.sexo, g.descripcion, g.foto,
               c.nombre as nombreColonia, h.idColonia
        FROM GATO g
        LEFT JOIN HISTORIAL h ON g.idGato = h.idGato AND h.fechaIda IS NULL
        LEFT JOIN COLONIA_FELINA c ON h.idColonia = c.idColonia
        WHERE g.idCementerio IS NULL
        AND g.idGato NOT IN (SELECT idGato FROM INCIDENCIA WHERE tipo = 'fallecimiento')";

if (!empty($filtroNombre)) {
    $sql .= " AND g.nombre LIKE '%$filtroNombre%'";
}

if (!empty($filtroColor)) {
    $sql .= " AND g.descripcion LIKE '%$filtroColor%'";
}

if (!empty($filtroXIP)) {
    $sql .= " AND g.numXIP LIKE '%$filtroXIP%'";
}

if (!empty($filtroSexo)) {
    $sql .= " AND g.sexo = '$filtroSexo'";
}

if (!empty($filtroColonia)) {
    $sql .= " AND c.nombre LIKE '%$filtroColonia%'";
}

$sql .= " ORDER BY g.nombre";

$resultado = mysqli_query($con, $sql);

// Obtener todas las colonias para el dropdown
$sqlTodasColonias = "SELECT idColonia, nombre FROM COLONIA_FELINA ORDER BY nombre";
$resultTodasColonias = mysqli_query($con, $sqlTodasColonias);

// Obtener todas las colonias para el dropdown (solo si es modo albirament)
$resColonias = null;
if ($modo == 'albirament') {
    $sqlColonias = "SELECT idColonia, nombre FROM COLONIA_FELINA ORDER BY nombre";
    $resColonias = mysqli_query($con, $sqlColonias);
}

// Configurar textos según el modo
$titulos = [
    'ver' => ['titulo' => 'Listado de Gatos', 'subtitulo' => 'Todos los gatos registrados en el sistema'],
    'albirament' => ['titulo' => 'Albirament de Gatos', 'subtitulo' => 'Registra el avistamiento de un gato en una colonia diferente'],
    'incidencia' => ['titulo' => 'Seleccionar Gato para Incidencia', 'subtitulo' => 'Busca y selecciona el gato para registrar la incidencia']
];

$textoConfig = $titulos[$modo] ?? $titulos['ver'];

// Añadir breadcrumb
addBreadcrumb($textoConfig['titulo']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $textoConfig['titulo']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #ffffff;
            color: #2c2c2c;
            line-height: 1.7;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 30px;
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            font-size: 1.125rem;
            color: #666;
            font-weight: 400;
            margin-bottom: 40px;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 3px solid #5b9bd5;
            padding: 20px 24px;
            margin-bottom: 32px;
            border-radius: 4px;
        }
        
        .filtros-container {
            background-color: #ffffff;
            padding: 32px;
            border: 1px solid #e0e0e0;
            margin-bottom: 40px;
            border-radius: 6px;
        }
        
        .filtros-container h3 {
            margin-top: 0;
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 24px;
        }
        
        .filtro-grupo {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filtro-item {
            flex: 1;
            min-width: 180px;
        }
        
        .filtro-item label {
            display: block;
            margin-bottom: 8px;
            color: #4a4a4a;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .filtro-item select,
        .filtro-item input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        
        .filtro-item select:focus,
        .filtro-item input:focus {
            outline: none;
            border-color: #5b9bd5;
        }
        
        .btn-filtrar {
            padding: 12px 28px;
            background-color: #5b9bd5;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.95rem;
            transition: background-color 0.2s;
        }
        
        .btn-filtrar:hover {
            background-color: #4a8bc2;
        }
        
        .btn-limpiar {
            padding: 12px 28px;
            background-color: #ffffff;
            color: #5b9bd5;
            border: 1px solid #5b9bd5;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .btn-limpiar:hover {
            background-color: #f8f9fa;
        }
        
        .gatos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 28px;
            margin-bottom: 40px;
        }
        
        .gato-card {
            background-color: #ffffff;
            padding: 24px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.2s;
        }
        
        .gato-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .gato-card h3 {
            margin-top: 0;
            color: #1a1a1a;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .gato-foto {
            width: 100%;
            height: 220px;
            object-fit: contain;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .sin-foto {
            width: 100%;
            height: 220px;
            background-color: #f8f9fa;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 14px;
        }
        .gato-info {
            margin: 12px 0;
            flex-grow: 1;
            line-height: 1.8;
        }
        
        .gato-info strong {
            color: #4a4a4a;
            font-weight: 600;
        }
        
        .gato-descripcion {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 3em;
            line-height: 1.6em;
            color: #666;
        }
        
        .colonia-actual {
            padding: 12px 16px;
            margin: 16px 0;
            background-color: #f0f7ff;
            border-radius: 4px;
            color: #2c5282;
            font-weight: 500;
        }
        
        .form-albirament {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .form-albirament h4 {
            margin-top: 0;
            color: #1a1a1a;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .form-albirament select,
        .form-albirament input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 0.95rem;
        }
        
        .form-incidencia {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .form-incidencia h4 {
            margin-top: 0;
            color: #1a1a1a;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .btn-registrar {
            background-color: #5b9bd5;
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
            font-size: 1rem;
            transition: background-color 0.2s;
        }
        
        .btn-registrar:hover {
            background-color: #4a8bc2;
        }
        
        .btn-seleccionar {
            background-color: #5b9bd5;
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-seleccionar:hover {
            background-color: #4a8bc2;
        }
        
        .no-resultados {
            text-align: center;
            padding: 60px 40px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            color: #666;
            font-size: 1.05rem;
        }
        
        .btn-volver {
            display: inline-block;
            margin-top: 40px;
            color: #5b9bd5;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.2s;
        }
        
        .btn-volver:hover {
            color: #4a8bc2;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <div class="container">
        <h1><?php echo $textoConfig['titulo']; ?></h1>
        <p class="subtitle"><?php echo $textoConfig['subtitulo']; ?></p>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div style="background-color: white; border: 1px solid #333; padding: 12px; margin-bottom: 20px;">
                <?php echo ($modo == 'albirament') ? 'Albirament registrado correctamente. El historial del gato ha sido actualizado.' : 'Operación completada correctamente.'; ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filtros-container">
            <h3>Buscar Gato</h3>
            <form method="GET" action="">
                <input type="hidden" name="modo" value="<?php echo htmlspecialchars($modo); ?>">
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
                            <option value="M" <?php echo ($filtroSexo == 'M') ? 'selected' : ''; ?>>Macho</option>
                            <option value="H" <?php echo ($filtroSexo == 'H') ? 'selected' : ''; ?>>Hembra</option>
                            <option value="Desconocido" <?php echo ($filtroSexo == 'Desconocido') ? 'selected' : ''; ?>>Desconocido</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label for="colonia">Colonia:</label>
                        <input type="text" name="colonia" id="colonia" 
                               value="<?php echo htmlspecialchars($filtroColonia); ?>" 
                               list="lista-colonias" 
                               placeholder="Escribe o selecciona...">
                        <datalist id="lista-colonias">
                            <?php while ($col = mysqli_fetch_assoc($resultTodasColonias)): ?>
                                <option value="<?php echo htmlspecialchars($col['nombre']); ?>">
                            <?php endwhile; ?>
                        </datalist>
                    </div>
                    <div class="filtro-item">
                        <button type="submit" class="btn-filtrar">Buscar</button>
                        <a href="?modo=<?php echo htmlspecialchars($modo); ?>" class="btn-limpiar">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Grid de gatos -->
        <?php if (mysqli_num_rows($resultado) > 0): ?>
            <div class="gatos-grid">
                <?php while ($gato = mysqli_fetch_assoc($resultado)): ?>
                    <div class="gato-card">
                        <?php if ($gato['foto']): ?>
                            <img src="../<?php echo htmlspecialchars($gato['foto']); ?>" alt="<?php echo htmlspecialchars($gato['nombre']); ?>" class="gato-foto">
                        <?php else: ?>
                            <div class="sin-foto">Foto no disponible</div>
                        <?php endif; ?>
                        
                        <h3> <?php echo htmlspecialchars($gato['nombre']); ?></h3>
                        
                        <div class="gato-info">
                            <strong>XIP:</strong> <?php echo $gato['numXIP'] ? htmlspecialchars($gato['numXIP']) : 'Sin XIP'; ?><br>
                            
                            <?php if ($gato['sexo']): ?>
                                <strong>Sexo:</strong> <?php echo htmlspecialchars($gato['sexo']); ?><br>
                            <?php endif; ?>
                            
                            <?php if ($gato['descripcion']): ?>
                                <strong>Descripción:</strong><br>
                                <span class="gato-descripcion"><?php echo htmlspecialchars($gato['descripcion']); ?></span><br>
                            <?php endif; ?>
                        </div>

                        <?php if ($gato['nombreColonia']): ?>
                            <div class="colonia-actual">
                                Colonia actual: <?php echo htmlspecialchars($gato['nombreColonia']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($modo == 'albirament'): ?>
                            <!-- Formulario para albirament -->
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
                        <?php elseif ($modo == 'incidencia'): ?>
                            <!-- Botón para seleccionar gato para incidencia -->
                            <div class="form-incidencia">
                                <h4>Registrar incidencia</h4>
                                <a href="nueva_incidencia_gato.php?idGato=<?php echo $gato['idGato']; ?>&idColonia=<?php echo $gato['idColonia']; ?>" class="btn-seleccionar">
                                    Seleccionar Gato
                                </a>
                            </div>
                        <?php elseif ($modo == 'ver'): ?>
                            <!-- Botón para ver ficha completa del gato -->
                            <div style="margin-top: 15px;">
                                <a href="ver_gato.php?idGato=<?php echo $gato['idGato']; ?>" class="btn-seleccionar">
                                    Ver Ficha Completa
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-resultados">
                <p>No se encontraron gatos con los criterios de búsqueda.</p>
                <p>Intenta ajustar los filtros.</p>
            </div>
        <?php endif; ?>

        <a href="../menu.php" class="btn-volver">Volver al menú</a>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
