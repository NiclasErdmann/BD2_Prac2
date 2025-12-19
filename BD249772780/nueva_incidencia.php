<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión y obtener idVoluntario
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

$idPersona = $_SESSION['idPersona'];

// Obtener idVoluntario
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

// Obtener colonias disponibles
$sqlColonias = "SELECT idColonia, nombre FROM COLONIA_FELINA ORDER BY nombre";
$resColonias = mysqli_query($con, $sqlColonias);

// Si se ha seleccionado una colonia, obtener sus gatos
$idColoniaSeleccionada = $_GET['idColonia'] ?? '';
$resGatos = null;

if (!empty($idColoniaSeleccionada)) {
    // Obtener los gatos activos de esa colonia
    $sqlGatos = "SELECT DISTINCT g.idGato, g.nombre, g.numXIP, g.sexo 
                 FROM GATO g
                 INNER JOIN HISTORIAL h ON g.idGato = h.idGato
                 WHERE h.idColonia = ? 
                 AND h.fechaIda IS NULL
                 AND g.idCementerio IS NULL
                 ORDER BY g.nombre";
    
    $stmt = mysqli_prepare($con, $sqlGatos);
    mysqli_stmt_bind_param($stmt, "i", $idColoniaSeleccionada);
    mysqli_stmt_execute($stmt);
    $resGatos = mysqli_stmt_get_result($stmt);
}

// Añadir breadcrumb
addBreadcrumb('Nueva Incidencia');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Incidencia</title>
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
            max-width: 900px;
            margin: 0 auto;
            padding: 50px 40px;
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
        
        .form-paso {
            background-color: #f0f7ff;
            padding: 28px 32px;
            border-radius: 6px;
            margin-bottom: 32px;
            border-left: 4px solid #5b9bd5;
        }
        
        .form-paso h3 {
            margin-top: 0;
            color: #2c5282;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 32px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #4a4a4a;
            font-size: 0.95rem;
        }
        
        label .required {
            color: #dc3545;
        }
        
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #5b9bd5;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }
        
        select {
            cursor: pointer;
        }
        
        .btn-submit {
            background-color: #5b9bd5;
            color: white;
            padding: 14px 36px;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-submit:hover {
            background-color: #4a8bc2;
        }
        
        .btn-submit:disabled {
            background-color: #d0d0d0;
            cursor: not-allowed;
        }
        
        .btn-cancelar {
            display: inline-block;
            margin-left: 16px;
            color: #5b9bd5;
            text-decoration: none;
            padding: 14px 24px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .btn-cancelar:hover {
            color: #4a8bc2;
            text-decoration: underline;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 3px solid #5b9bd5;
            padding: 20px 24px;
            margin-bottom: 32px;
            border-radius: 4px;
        }
        
        .info-box p {
            margin: 8px 0;
            color: #4a4a4a;
            line-height: 1.7;
        }
        
        .info-box strong {
            color: #2c2c2c;
        }
        
        .paso-info {
            background-color: #f0f7ff;
            border-left: 3px solid #5b9bd5;
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 4px;
            color: #2c5282;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <div class="container">
        <h1>➕ Registrar Nueva Incidencia</h1>
        <p class="subtitle">Completa el formulario para registrar una incidencia</p>

        <!-- PASO 1: Seleccionar colonia -->
        <div class="form-paso">
            <h3>📍 Paso 1: Selecciona la colonia</h3>
            <form action="" method="GET">
                <div class="form-group">
                    <label for="colonia">Colonia donde ocurrió la incidencia <span class="required">*</span></label>
                    <select name="idColonia" id="colonia" onchange="this.form.submit()" required>
                        <option value="">-- Selecciona una colonia --</option>
                        <?php 
                        mysqli_data_seek($resColonias, 0);
                        while ($col = mysqli_fetch_assoc($resColonias)): 
                        ?>
                            <option value="<?php echo $col['idColonia']; ?>" 
                                <?php echo ($col['idColonia'] == $idColoniaSeleccionada) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($col['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if (!empty($idColoniaSeleccionada)): ?>
            <!-- PASO 2: Formulario completo -->
            <div class="paso-info">
                ✓ Colonia seleccionada. Ahora completa los datos de la incidencia.
            </div>

            <form action="procesar_incidencia.php" method="POST">
                <input type="hidden" name="idColonia" value="<?php echo $idColoniaSeleccionada; ?>">
                <input type="hidden" name="idVoluntario" value="<?php echo $idVoluntario; ?>">

                <div class="form-paso">
                    <h3>Paso 2: Datos de la incidencia</h3>

                    <div class="form-group">
                        <label for="descripcion">Descripción <span class="required">*</span></label>
                        <textarea name="descripcion" id="descripcion" required 
                            placeholder="Describe detalladamente lo ocurrido..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo de Incidencia <span class="required">*</span></label>
                        <select name="tipo" id="tipo" required>
                            <option value="">-- Selecciona un tipo --</option>
                            <option value="salud">🏥 Problema de salud</option>
                            <option value="herido">Herida</option>
                            <option value="fallecimiento">Fallecimiento</option>
                            <option value="enfermedad">Enfermedad</option>
                            <option value="otro">📌 Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="idGato">Gato afectado <span class="required">*</span></label>
                        <select name="idGato" id="idGato" required>
                            <option value="">-- Selecciona un gato --</option>
                            <?php if ($resGatos && mysqli_num_rows($resGatos) > 0): ?>
                                <?php while ($gato = mysqli_fetch_assoc($resGatos)): ?>
                                    <option value="<?php echo $gato['idGato']; ?>">
                                        <?php 
                                        echo htmlspecialchars($gato['nombre']);
                                        if ($gato['numXIP']) {
                                            echo ' (' . htmlspecialchars($gato['numXIP']) . ')';
                                        }
                                        if ($gato['sexo']) {
                                            echo ' - ' . htmlspecialchars($gato['sexo']);
                                        }
                                        ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">No hay gatos en esta colonia</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="info-box">
                    <p><strong>ℹ️ Información:</strong></p>
                    <p>• La fecha de la incidencia se registrará automáticamente con la fecha actual.</p>
                    <p>• Asegúrate de que la descripción sea clara y detallada.</p>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-submit">✓ Registrar Incidencia</button>
                    <a href="listar_incidencias.php" class="btn-cancelar">Cancelar</a>
                </div>
            </form>

        <?php else: ?>
            <div class="paso-info">
                👆 Selecciona una colonia para continuar
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
