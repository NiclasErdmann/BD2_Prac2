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

// Obtener datos del gato seleccionado
$idGato = $_GET['idGato'] ?? '';
$idColonia = $_GET['idColonia'] ?? '';

if (empty($idGato)) {
    die('Error: No se ha seleccionado ningún gato. <a href="listar_gatos.php?modo=incidencia">Volver</a>');
}

// Obtener información del gato
$sqlGato = "SELECT g.idGato, g.nombre, g.numXIP, g.sexo, g.descripcion,
                   c.nombre as nombreColonia
            FROM GATO g
            LEFT JOIN HISTORIAL h ON g.idGato = h.idGato AND h.fechaIda IS NULL
            LEFT JOIN COLONIA_FELINA c ON h.idColonia = c.idColonia
            WHERE g.idGato = ?";

$stmtGato = mysqli_prepare($con, $sqlGato);
mysqli_stmt_bind_param($stmtGato, "i", $idGato);
mysqli_stmt_execute($stmtGato);
$resGato = mysqli_stmt_get_result($stmtGato);
$gato = mysqli_fetch_assoc($resGato);
mysqli_stmt_close($stmtGato);

if (!$gato) {
    die('Error: Gato no encontrado. <a href="listar_gatos.php?modo=incidencia">Volver</a>');
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
        
        .gato-seleccionado {
            background-color: #f0f7ff;
            padding: 28px 32px;
            border-radius: 6px;
            margin-bottom: 40px;
            border-left: 4px solid #5b9bd5;
        }
        
        .gato-seleccionado h3 {
            margin-top: 0;
            color: #2c5282;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .gato-seleccionado p {
            margin: 10px 0;
            color: #2c2c2c;
            font-size: 1rem;
            line-height: 1.7;
        }
        
        .gato-seleccionado strong {
            color: #4a4a4a;
            font-weight: 600;
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
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <div class="container">
        <h1>➕ Registrar Nueva Incidencia</h1>
        <p class="subtitle">Completa los datos de la incidencia para el gato seleccionado</p>

        <!-- Información del gato seleccionado -->
        <div class="gato-seleccionado">
            <h3>Gato Seleccionado</h3>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($gato['nombre']); ?></p>
            <p><strong>XIP:</strong> <?php echo $gato['numXIP'] ? htmlspecialchars($gato['numXIP']) : 'Sin XIP'; ?></p>
            <?php if ($gato['sexo']): ?>
                <p><strong>Sexo:</strong> <?php echo ($gato['sexo'] == 'Macho') ? 'Macho' : 'Hembra'; ?></p>
            <?php endif; ?>
            <?php if ($gato['nombreColonia']): ?>
                <p><strong>Colonia:</strong> <?php echo htmlspecialchars($gato['nombreColonia']); ?></p>
            <?php endif; ?>
        </div>

        <form action="procesar_incidencia.php" method="POST">
            <input type="hidden" name="idGato" value="<?php echo $gato['idGato']; ?>">
            <input type="hidden" name="idVoluntario" value="<?php echo $idVoluntario; ?>">

            <div class="form-group">
                <label for="tipo">Tipo de Incidencia <span class="required">*</span></label>
                <select name="tipo" id="tipo" required>
                    <option value="">-- Selecciona un tipo --</option>
                    <option value="salud">Problema de salud</option>
                    <option value="herido">Herida</option>
                    <option value="fallecimiento">Fallecimiento</option>
                    <option value="enfermedad">Enfermedad</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción <span class="required">*</span></label>
                <textarea name="descripcion" id="descripcion" required 
                    placeholder="Describe detalladamente lo ocurrido..."></textarea>
            </div>

            <div class="info-box">
                <p><strong>ℹ️ Información:</strong></p>
                <p>• La fecha de la incidencia se registrará automáticamente con la fecha actual.</p>
                <p>• Asegúrate de que la descripción sea clara y detallada.</p>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-submit">✓ Registrar Incidencia</button>
                <a href="listar_gatos.php?modo=incidencia" class="btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
