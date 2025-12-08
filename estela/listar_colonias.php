<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost","root","","BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Helper: verifica si el usuario tiene una función asignada
function usuarioPuede($con, $idPersona, $funcionNombre) {
    $sqlPerm = "SELECT 1
                FROM PER_ROL pr
                JOIN PUEDEHACER ph ON pr.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
                WHERE pr.idPersona = ? AND LOWER(f.nombre) = LOWER(?)
                LIMIT 1";

    $stmtPerm = mysqli_prepare($con, $sqlPerm);
    mysqli_stmt_bind_param($stmtPerm, 'is', $idPersona, $funcionNombre);
    mysqli_stmt_execute($stmtPerm);
    $resPerm = mysqli_stmt_get_result($stmtPerm);
    $has = ($resPerm && mysqli_num_rows($resPerm) > 0);
    mysqli_stmt_close($stmtPerm);
    return $has;
}

// Obtener idAyuntamiento desde la sesión (guardado en login.php)
if (!isset($_SESSION['idAyuntamiento']) || empty($_SESSION['idAyuntamiento'])) {
    die('Error: no se detectó ayuntamiento en la sesión. Por favor inicia sesión correctamente.');
}

$idAyuntamiento = (int) $_SESSION['idAyuntamiento'];
$idPersona = isset($_SESSION['idPersona']) ? (int) $_SESSION['idPersona'] : 0;
$puedeModificar = ($idPersona > 0) ? usuarioPuede($con, $idPersona, 'Modificar Colonias') : false;

// Obtener el nombre del ayuntamiento
$sqlAyuntamiento = "SELECT nombre FROM AYUNTAMIENTO WHERE idAyuntamiento = ?";
$stmtAyunt = mysqli_prepare($con, $sqlAyuntamiento);
mysqli_stmt_bind_param($stmtAyunt, "i", $idAyuntamiento);
mysqli_stmt_execute($stmtAyunt);
$resultadoAyunt = mysqli_stmt_get_result($stmtAyunt);
$ayuntamiento = mysqli_fetch_assoc($resultadoAyunt);
$nombreAyuntamiento = $ayuntamiento['nombre'] ?? 'Desconocido';
mysqli_stmt_close($stmtAyunt);

// Consultar colonias del ayuntamiento con su grupo asignado
$query = "SELECT C.idColonia, C.nombre, C.lugarReferencia, C.numeroGatos,
                 G.idGrupoTrabajo, G.nombre AS nombreGrupo
          FROM COLONIA_FELINA C
          LEFT JOIN GRUPO_TRABAJO G ON C.idGrupoTrabajo = G.idGrupoTrabajo
          WHERE C.idGrupoTrabajo IN (
              SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idAyuntamiento = $idAyuntamiento
          )
          ORDER BY C.nombre";

$resultado = mysqli_query($con, $query);
if (!$resultado) {
    die('Error en la consulta: ' . mysqli_error($con));
}

// Añadir breadcrumb
addBreadcrumb('Mis Colonias');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Colonias</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            color: blue;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <h2>Mis Colonias (Ayuntamiento de <?php echo htmlspecialchars($nombreAyuntamiento); ?>)</h2>
    <?php if ($puedeModificar): ?>
        <a href="formularioCrear_Colonias.php">
            <button>Crear Nueva Colonia</button>
        </a>
    <?php endif; ?>
    <br><br>

    <table>
        <tr>
            <th>Nombre</th>
            <th>Ubicación</th>
            <th>Grupo de Trabajo</th>
            <th>Número de Gatos</th>
        </tr>

        <?php
        // Mostrar cada colonia
        if (mysqli_num_rows($resultado) > 0) {
            while($fila = mysqli_fetch_assoc($resultado)) {
                echo "<tr>";
                echo "<td><a href='info_colonia.php?id=" . $fila['idColonia'] . "'>" . htmlspecialchars($fila['nombre']) . "</a></td>";
                echo "<td>" . htmlspecialchars($fila['lugarReferencia']) . "</td>";
                echo "<td>";
                if (!empty($fila['idGrupoTrabajo'])) {
                    echo "<a href='info_grupoTrabajo.php?id=" . $fila['idGrupoTrabajo'] . "'>" . htmlspecialchars($fila['nombreGrupo']) . "</a>";
                } else {
                    echo "<i>Sin asignar</i>";
                }
                echo "</td>";
                echo "<td>" . $fila['numeroGatos'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3' style='text-align: center;'>No hay colonias registradas en tu ayuntamiento.</td></tr>";
        }
        ?>
    </table>

</body>
</html>

<?php
mysqli_close($con);
?>
