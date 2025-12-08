<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Obtener el ID del grupo desde la URL y el idPersona de la sesión
$idGrupo = $_GET['id'] ?? null;
$idPersona = $_SESSION['idPersona'] ?? null;

if (!$idGrupo) {
    echo '<h2>No se ha especificado un grupo.</h2>';
    exit;
}

if (!$idPersona) {
    echo '<h2>No se ha encontrado la información del usuario en la sesión.</h2>';
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

// Consulta para obtener la información del grupo
$sqlGrupo = "SELECT 
                G.idGrupoTrabajo,
                G.nombre AS nombreGrupo,
                G.descripcion,
                P.nombre AS nombreResp,
                P.apellido AS apellidoResp,
                P.telefono AS telefonoResp
            FROM GRUPO_TRABAJO G
            LEFT JOIN VOLUNTARIO V ON G.idResponsable = V.idVoluntario
            LEFT JOIN PERSONA P ON V.idPersona = P.idPersona
            WHERE G.idGrupoTrabajo = ?";

$stmt = mysqli_prepare($con, $sqlGrupo);
mysqli_stmt_bind_param($stmt, "i", $idGrupo);
mysqli_stmt_execute($stmt);
$resultadoGrupo = mysqli_stmt_get_result($stmt);
$grupo = mysqli_fetch_assoc($resultadoGrupo);

if (!$grupo) {
    echo '<h2>No se ha encontrado el grupo especificado.</h2>';
    exit;
}

// Consulta para obtener las colonias asignadas al grupo
$sqlColonias = "SELECT idColonia, nombre
                FROM COLONIA_FELINA
                WHERE idGrupoTrabajo = ?
                ORDER BY nombre";

$stmtColonias = mysqli_prepare($con, $sqlColonias);
mysqli_stmt_bind_param($stmtColonias, "i", $idGrupo);
mysqli_stmt_execute($stmtColonias);
$resultadoColonias = mysqli_stmt_get_result($stmtColonias);

// Consulta para obtener los voluntarios del grupo
$sqlVoluntarios = "SELECT 
                    P.nombre,
                    P.apellido
                FROM VOLUNTARIO V
                INNER JOIN PERSONA P ON V.idPersona = P.idPersona
                WHERE V.idGrupoTrabajo = ?
                ORDER BY P.nombre, P.apellido";

$stmtVoluntarios = mysqli_prepare($con, $sqlVoluntarios);
mysqli_stmt_bind_param($stmtVoluntarios, "i", $idGrupo);
mysqli_stmt_execute($stmtVoluntarios);
$resultadoVoluntarios = mysqli_stmt_get_result($stmtVoluntarios);

// Añadir breadcrumb
addBreadcrumb($grupo['nombreGrupo']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Grupo de Trabajo</title>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <h1><?php echo htmlspecialchars($grupo['nombreGrupo']); ?></h1>

    <table border="0" width="100%">
        <tr>
            <td width="50%" valign="top">
                <h3>Descripción:</h3>
                <p>
                    <?php 
                    if (!empty($grupo['descripcion'])) {
                        echo nl2br(htmlspecialchars($grupo['descripcion']));
                    } else {
                        echo "<i>Sin descripción</i>";
                    }
                    ?>
                </p>
            </td>
            <td width="50%" valign="top">
                <div style="background-color: #e3f2fd; padding: 15px; border: 1px solid #90caf9;">
                    <h3 style="margin-top: 0;">👤 Responsable del Grupo:</h3>
                    <?php if (!empty($grupo['nombreResp'])): ?>
                        <h2><?php echo htmlspecialchars($grupo['nombreResp'] . ' ' . $grupo['apellidoResp']); ?></h2>
                        <?php if (!empty($grupo['telefonoResp'])): ?>
                            <small>Tel: <?php echo htmlspecialchars($grupo['telefonoResp']); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <h2><i>(Pendiente de asignar)</i></h2>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <hr>

    <table border="1" width="100%" cellpadding="10" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th width="50%">🏠 Colonias Asignadas</th>
                <th width="50%">👥 Voluntarios Miembros</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td valign="top">
                    <?php if (mysqli_num_rows($resultadoColonias) > 0): ?>
                        <ul>
                            <?php while ($colonia = mysqli_fetch_assoc($resultadoColonias)): ?>
                                <li>
                                    <a href="info_colonia.php?id=<?php echo $colonia['idColonia']; ?>">
                                        <?php echo htmlspecialchars($colonia['nombre']); ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p><i>No hay colonias asignadas a este grupo.</i></p>
                    <?php endif; ?>
                    
                    <?php if ($puedeGestionar): ?>
                    <br>
                    <small><a href="asignar_colonia.php?idGrupo=<?php echo $idGrupo; ?>">[+ Asignar otra colonia]</a></small>
                    <?php endif; ?>
                </td>

                <td valign="top">
                    <?php if (mysqli_num_rows($resultadoVoluntarios) > 0): ?>
                        <ul>
                            <?php while ($voluntario = mysqli_fetch_assoc($resultadoVoluntarios)): ?>
                                <li><?php echo htmlspecialchars($voluntario['nombre'] . ' ' . $voluntario['apellido']); ?></li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p><i>No hay voluntarios en este grupo.</i></p>
                    <?php endif; ?>
                    
                    <?php if ($puedeGestionar): ?>
                    <br>
                    <small><a href="asignar_miembro.php?idGrupo=<?php echo $idGrupo; ?>">[+ Añadir Voluntario]</a></small>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <?php if ($puedeGestionar): ?>
        <a href="formularioEditar_grupoTrabajo.php?id=<?php echo $idGrupo; ?>">
            <button style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
                ✏ Editar Datos del Grupo / Cambiar Responsable
            </button>
        </a>
    <?php endif; ?></body>
</html>

<?php
mysqli_close($con);
?>
