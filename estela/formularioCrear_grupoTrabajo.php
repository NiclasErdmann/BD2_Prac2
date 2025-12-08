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
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
if (!$idAyuntamiento) {
    die('Error: no se detectó ayuntamiento en la sesión.');
}

// Obtener solo los voluntarios con rol de responsable del ayuntamiento
$sqlVoluntarios = "SELECT V.idVoluntario, P.nombre, P.apellido
                   FROM VOLUNTARIO V
                   INNER JOIN PERSONA P ON V.idPersona = P.idPersona
                   INNER JOIN PER_ROL PR ON P.idPersona = PR.idPersona
                   INNER JOIN ROL R ON PR.idRol = R.idRol
                   WHERE V.idAyuntamiento = ? AND R.nombre = 'responsableGrupo'
                   ORDER BY P.nombre, P.apellido";

$stmtVol = mysqli_prepare($con, $sqlVoluntarios);
mysqli_stmt_bind_param($stmtVol, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtVol);
$resultadoVol = mysqli_stmt_get_result($stmtVol);

// Añadir breadcrumb
addBreadcrumb('Crear Grupo de Trabajo');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Grupo de Trabajo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], textarea, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .cancelar { margin-left: 10px; padding: 10px 20px; background-color: #f44336; color: white; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <h2>Crear Nuevo Grupo de Trabajo</h2>

    <form action="grupoTrabajo_guardar.php" method="POST">
        <input type="hidden" name="accion" value="crear">
        
        <label for="nombre">Nombre del Grupo:</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Grupo Zona Norte">

        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe la zona de actuación o notas importantes..."></textarea>

        <label for="idResponsable">Asignar Responsable (opcional):</label>
        <select id="idResponsable" name="idResponsable">
            <option value="">-- Sin asignar --</option>
            <?php while ($vol = mysqli_fetch_assoc($resultadoVol)): ?>
                <option value="<?php echo $vol['idVoluntario']; ?>">
                    <?php echo htmlspecialchars($vol['nombre'] . ' ' . $vol['apellido']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Guardar Grupo</button>
        <a href="listar_grupoTrabajo.php" class="cancelar">Cancelar</a>
    </form>

</body>
</html>

<?php
mysqli_close($con);
?>
