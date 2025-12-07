<?php
session_start();

// Conexión a BD
$con = mysqli_connect("localhost","root","","BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Obtener idAyuntamiento desde la sesión (guardado en login.php)
if (!isset($_SESSION['idAyuntamiento']) || empty($_SESSION['idAyuntamiento'])) {
    die('Error: no se detectó ayuntamiento en la sesión. Por favor inicia sesión correctamente.');
}

$idAyuntamiento = (int) $_SESSION['idAyuntamiento'];

// Consultar colonias del ayuntamiento
$query = "SELECT idColonia, nombre, lugarReferencia, numeroGatos
          FROM COLONIA_FELINA
          WHERE idGrupoTrabajo IN (
              SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idAyuntamiento = $idAyuntamiento
          )
          ORDER BY nombre";

$resultado = mysqli_query($con, $query);
if (!$resultado) {
    die('Error en la consulta: ' . mysqli_error($con));
}
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
    <h2>Mis Colonias (Ayuntamiento)</h2>
        <a href="crearColonia.php">
        <button>Crear Nueva Colonia</button>
    </a>
    <br><br>

    <table>
        <tr>
            <th>Nombre</th>
            <th>Ubicacion</th>
            <th>Numero de Gatos</th>
        </tr>

        <?php
        // Mostrar cada colonia
        if (mysqli_num_rows($resultado) > 0) {
            while($fila = mysqli_fetch_assoc($resultado)) {
                echo "<tr>";
                echo "<td><a href='info_colonia.php?id=" . $fila['idColonia'] . "'>" . htmlspecialchars($fila['nombre']) . "</a></td>";
                echo "<td>" . htmlspecialchars($fila['lugarReferencia']) . "</td>";
                echo "<td>" . $fila['numeroGatos'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3' style='text-align: center;'>No hay colonias registradas en tu ayuntamiento.</td></tr>";
        }
        ?>
    </table>

    <br><br>
    <a href="../login.html">Volver al menu</a>
</body>
</html>

<?php
mysqli_close($con);
?>
