<?php
session_start();
require_once 'conectar_bd.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['idVoluntario'])) {
    die("Error: Debes iniciar sesión como voluntario para registrar incidencias.");
}

// Conseguimos todas las colonias
$sqlColonias = "SELECT idColonia, nombre FROM COLONIA_FELINA ORDER BY nombre";
$resColonias = $conn->query($sqlColonias);

// Si se ha seleccionado una colonia, obtenemos sus gatos
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
    
    $stmt = $conn->prepare($sqlGatos);
    $stmt->bind_param("i", $idColoniaSeleccionada);
    $stmt->execute();
    $resGatos = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nueva Incidencia</title>
</head>
<body>

    <h2>Nueva Incidencia</h2>

    <form action="" method="GET">

        <label>Colonia:</label><br>
        <select name="idColonia" onchange="this.form.submit()" required>
            <option value="">-- Selecciona una colonia --</option>
            <?php while ($col = $resColonias->fetch_assoc()) { ?>
                <option value="<?php echo $col['idColonia']; ?>" 
                    <?php echo ($col['idColonia'] == $idColoniaSeleccionada) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($col['nombre']); ?>
                </option>
            <?php } ?>
        </select><br><br>

    </form>

    <?php if (!empty($idColoniaSeleccionada)) { ?>
    <form action="generar_incidencia.php" method="POST">

        <input type="hidden" name="idColonia" value="<?php echo $idColoniaSeleccionada; ?>">

        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="4" cols="50" required></textarea><br><br>

        <label>Tipo de Incidencia:</label><br>
        <select name="tipo" required>
            <option value="">-- Selecciona un tipo --</option>
            <option value="salud">Problema de salud</option>
            <option value="herido">Herida</option>
            <option value="fallecimiento">Fallecimiento</option>
            <option value="enfermedad">Enfermedad</option>
            <option value="otro">Otro</option>
        </select><br><br>

        <label>Gato:</label><br>
        <select name="idGato" required>
            <option value="">-- Selecciona un gato --</option>
            <?php while ($gato = $resGatos->fetch_assoc()) { ?>
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
            <?php } ?>
        </select><br><br>

        <input type="submit" value="Registrar Incidencia">
        <a href="incidencias_nueva.php">Cancelar</a>

    </form>
    <?php } else { ?>
        <p><em>Primero selecciona una colonia para ver los gatos disponibles</em></p>
    <?php } ?>

</body>
</html>

<?php
$conn->close();