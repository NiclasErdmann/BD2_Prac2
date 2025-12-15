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

// Obtener el modo de operación (albirament o incidencia)
$modo = $_GET['modo'] ?? 'ver'; // Valores: 'ver', 'albirament', 'incidencia'

// Obtener filtros
$filtroNombre = $_GET['nombre'] ?? '';
$filtroColor = $_GET['color'] ?? '';
$filtroXIP = $_GET['xip'] ?? '';
$filtroSexo = $_GET['sexo'] ?? '';

// Construir query con filtros
$sql = "SELECT g.idGato, g.nombre, g.numXIP, g.sexo, g.descripcion, g.foto,
               c.nombre as nombreColonia, h.idColonia
        FROM GATO g
        LEFT JOIN HISTORIAL h ON g.idGato = h.idGato AND h.fechaIda IS NULL
        LEFT JOIN COLONIA_FELINA c ON h.idColonia = c.idColonia
        WHERE g.idCementerio IS NULL";

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

$sql .= " ORDER BY g.nombre";

$resultado = mysqli_query($con, $sql);

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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
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
            background-color: white;
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
        }
        .filtros-container {
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
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
            background-color: #333;
            color: white;
            border: 1px solid #333;
            cursor: pointer;
        }
        .btn-filtrar:hover {
            background-color: #555;
        }
        .btn-limpiar {
            padding: 8px 20px;
            background-color: white;
            color: #333;
            border: 1px solid #333;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-limpiar:hover {
            background-color: #f0f0f0;
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
            border: 1px solid #ccc;
        }
        .gato-card h3 {
            margin-top: 0;
            color: #333;
        }
        .gato-foto {
            width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 15px;
            background-color: #f0f0f0;
        }
        .sin-foto {
            width: 100%;
            height: 200px;
            background-color: #f0f0f0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 14px;
        }
        .gato-info {
            margin: 10px 0;
            min-height: 80px;
        }
        .gato-info strong {
            color: #555;
        }
        .gato-descripcion {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 3em;
            line-height: 1.5em;
        }
        .colonia-actual {
            padding: 8px;
            margin: 10px 0;
        }
        .form-albirament {
            background-color: white;
            padding: 15px;
            border: 1px solid #ccc;
            margin-top: 15px;
        }
        .form-albirament h4 {
            margin-top: 0;
        }
        .form-albirament select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .form-incidencia {
            background-color: white;
            padding: 15px;
            border: 1px solid #ccc;
            margin-top: 15px;
        }
        .form-incidencia h4 {
            margin-top: 0;
        }
        .btn-registrar {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border: 1px solid #333;
            cursor: pointer;
            width: 100%;
        }
        .btn-registrar:hover {
            background-color: #555;
        }
        .btn-seleccionar {
            background-color: #333;
            color: white;
            padding: 10px 15px;
            border: 1px solid #333;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
            font-size: 14px;
            box-sizing: border-box;
        }
        .btn-seleccionar:hover {
            background-color: #555;
        }
        .no-resultados {
            text-align: center;
            padding: 40px;
            background-color: white;
            border: 1px solid #ccc;
            color: #666;
        }

        .btn-volver {
            display: inline-block;
            margin-top: 20px;
            color: #333;
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
                            <option value="Macho" <?php echo ($filtroSexo == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                            <option value="Hembra" <?php echo ($filtroSexo == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                        </select>
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
                                <select name="idColoniaNueva" id="nuevaColonia_<?php echo $gato['idGato']; ?>" required>
                                    <option value="">-- Selecciona colonia --</option>
                                    <?php 
                                    mysqli_data_seek($resColonias, 0);
                                    while ($col = mysqli_fetch_assoc($resColonias)): 
                                        // No mostrar la colonia actual
                                        if ($col['idColonia'] != $gato['idColonia']):
                                    ?>
                                        <option value="<?php echo $col['idColonia']; ?>">
                                            <?php echo htmlspecialchars($col['nombre']); ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    ?>
                                </select>
                                
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
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
