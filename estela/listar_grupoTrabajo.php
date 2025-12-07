<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Obtener el idAyuntamiento desde la sesión
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
if (!$idAyuntamiento) {
    echo '<h2>No se ha encontrado el ayuntamiento en la sesión.</h2>';
    exit;
}

// Consulta para obtener los grupos de trabajo del ayuntamiento
$sql = "SELECT 
            G.idGrupoTrabajo, 
            G.nombre AS nombreGrupo, 
            P.nombre AS nombreResp, 
            P.apellido AS apellidoResp
        FROM GRUPO_TRABAJO G
        LEFT JOIN VOLUNTARIO V ON G.idResponsable = V.idVoluntario
        LEFT JOIN PERSONA P ON V.idPersona = P.idPersona
        WHERE G.idAyuntamiento = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $idAyuntamiento);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos de Trabajo</title>
</head>
<body>

    <h1>Grupos de Trabajo</h1>

    <a href="form_grupoTrabajo.html">
        <button>+ Añadir Grupo de Trabajo</button>
    </a>
    
    <br><br>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Nombre del Grupo</th>
                <th>Responsable</th>
            </tr>
        </thead>
        <tbody>
            
            <?php 
            // 3. BUCLE PARA GENERAR LAS FILAS
            while($fila = $resultado->fetch_assoc()): 
            ?>
                <tr>
                    <td>
                        <a href="info_grupo.php?id=<?php echo $fila['idGrupoTrabajo']; ?>">
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
                </tr>
            <?php endwhile; ?>

            <?php 
            // Mensaje opcional si no hay resultados
            if ($resultado->num_rows === 0): 
            ?>
                <tr>
                    <td colspan="2" style="text-align: center;">No hay grupos registrados en este ayuntamiento.</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>

</body>
</html>