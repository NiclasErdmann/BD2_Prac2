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
        .gato-seleccionado {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #007bff;
        }
        .gato-seleccionado h3 {
            margin-top: 0;
            color: #0066cc;
        }
        .gato-seleccionado p {
            margin: 5px 0;
            color: #333;
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
                    <option value="salud">🏥 Problema de salud</option>
                    <option value="herido">Herida</option>
                    <option value="fallecimiento">Fallecimiento</option>
                    <option value="enfermedad">Enfermedad</option>
                    <option value="otro">📌 Otro</option>
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
