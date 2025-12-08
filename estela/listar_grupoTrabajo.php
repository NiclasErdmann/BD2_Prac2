<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Obtener el idAyuntamiento y idPersona desde la sesión
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
$idPersona = $_SESSION['idPersona'] ?? null;

if (!$idAyuntamiento || !$idPersona) {
    echo '<h2>No se ha encontrado la información necesaria en la sesión.</h2>';
    exit;
}

// Verificar si el usuario tiene la función 'Gestionar Grupos' (idFuncion = 3)
$sqlPermiso = "SELECT COUNT(*) as tienePermiso
               FROM PER_ROL PR
               INNER JOIN PUEDEHACER PH ON PR.idRol = PH.idRol
               WHERE PR.idPersona = ? AND PH.idFuncion = 3";

$stmtPermiso = mysqli_prepare($con, $sqlPermiso);
mysqli_stmt_bind_param($stmtPermiso, "i", $idPersona);
mysqli_stmt_execute($stmtPermiso);
$resultadoPermiso = mysqli_stmt_get_result($stmtPermiso);
$permiso = mysqli_fetch_assoc($resultadoPermiso);
$puedeGestionar = $permiso['tienePermiso'] > 0;

// Construir la consulta según el permiso
if ($puedeGestionar) {
    // Si puede gestionar, muestra todos los grupos del ayuntamiento
    $sql = "SELECT 
                G.idGrupoTrabajo, 
                G.nombre AS nombreGrupo, 
                P.nombre AS nombreResp, 
                P.apellido AS apellidoResp,
                (SELECT COUNT(*) 
                 FROM VOLUNTARIO V2 
                 WHERE V2.idGrupoTrabajo = G.idGrupoTrabajo) AS numVoluntarios
            FROM GRUPO_TRABAJO G
            LEFT JOIN VOLUNTARIO V ON G.idResponsable = V.idVoluntario
            LEFT JOIN PERSONA P ON V.idPersona = P.idPersona
            WHERE G.idAyuntamiento = ?";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idAyuntamiento);
} else {
    // Si solo puede ver, muestra solo los grupos a los que pertenece como voluntario
    $sql = "SELECT 
                G.idGrupoTrabajo, 
                G.nombre AS nombreGrupo, 
                P.nombre AS nombreResp, 
                P.apellido AS apellidoResp,
                (SELECT COUNT(*) 
                 FROM VOLUNTARIO V2 
                 WHERE V2.idGrupoTrabajo = G.idGrupoTrabajo) AS numVoluntarios
            FROM GRUPO_TRABAJO G
            LEFT JOIN VOLUNTARIO V ON G.idResponsable = V.idVoluntario
            LEFT JOIN PERSONA P ON V.idPersona = P.idPersona
            INNER JOIN VOLUNTARIO VOL ON G.idGrupoTrabajo = VOL.idGrupoTrabajo
            WHERE G.idAyuntamiento = ? AND VOL.idPersona = ?";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $idAyuntamiento, $idPersona);
}

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Añadir breadcrumb
addBreadcrumb('Grupos de Trabajo');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos de Trabajo</title>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <h1>Grupos de Trabajo</h1>

    <?php if ($puedeGestionar): ?>
        <a href="form_grupoTrabajo.html">
            <button>+ Añadir Grupo de Trabajo</button>
        </a>
    <?php endif; ?>
    
    <br><br>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Nombre del Grupo</th>
                <th>Responsable</th>
                <th>Nº Voluntarios</th>
            </tr>
        </thead>
        <tbody>
            
            <?php 
            // 3. BUCLE PARA GENERAR LAS FILAS
            while($fila = $resultado->fetch_assoc()): 
            ?>
                <tr>
                    <td>
                        <a href="info_grupoTrabajo.php?id=<?php echo $fila['idGrupoTrabajo']; ?>">
                            <?php echo htmlspecialchars($fila['nombreGrupo']); ?>
                        </a>
                    </td>
                    <td>
                        <?php 
                        // Si tiene nombre (el JOIN funcionó), lo mostramos. Si no, indicamos pendiente.
                        if (!empty($fila['nombreResp'])) {
                            echo htmlspecialchars($fila['nombreResp'] . " " . $fila['apellidoResp']);
                        } else {
                            echo "<i>(Pendiente de asignar)</i>";
                        }
                        ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo $fila['numVoluntarios']; ?>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php 
            // Mensaje opcional si no hay resultados
            if ($resultado->num_rows === 0): 
            ?>
                <tr>
                    <td colspan="3" style="text-align: center;">
                        <?php if ($puedeGestionar): ?>
                            No hay grupos registrados en este ayuntamiento.
                        <?php else: ?>
                            No estás asignado a ningún grupo de trabajo.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>

</body>
</html>