<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .form-paso {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-paso h3 {
            margin-top: 0;
            color: #0066cc;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        label .required {
            color: #dc3545;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        select {
            cursor: pointer;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .btn-submit:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .btn-cancelar {
            display: inline-block;
            margin-left: 10px;
            color: #007bff;
            text-decoration: none;
            padding: 12px 20px;
        }
        .btn-cancelar:hover {
            text-decoration: underline;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
            color: #856404;
        }
        .paso-info {
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            color: #0c5460;
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
