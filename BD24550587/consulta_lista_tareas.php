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
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

// Añadir breadcrumb
addBreadcrumb('Lista_Tareas');
displayBreadcrumbs();


//style
echo 
"<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        text-align: left;
        padding: 8px;
        border: 1px solid black;
    }

    tr:nth-child(even) {
        background-color: #D6EEEE;
    }
</style>";

// ver tareas de la persona
$consulta=" SELECT t.idTrabajo, t.descripcion, fecha, hora, estado, c.nombre as nombreColonia, c.idColonia
            FROM PERSONA p
            JOIN VOLUNTARIO v ON p.idPersona = v.idPersona
            JOIN TRABAJO t ON v.idVoluntario = t.idVoluntario
            JOIN COLONIA_FELINA c ON c.idColonia = t.idColonia
            WHERE p.idPersona = ".$_SESSION['idPersona']." 
            ORDER BY estado DESC, fecha ASC, hora ASC
            ";

$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);
if(is_null($registre)){
    // Querry error
    $cad= 'No se encontraron tareas';
    echo $cad;
}else{

    echo "<p><table>";
        echo "<tr>";
        
        $cad=   '<th><a href="consulta_tarea.php?tarea='.$registre["idTrabajo"].'" >Tarea:</a></th>'.
                '<th>'.$registre["descripcion"].'</th>'.
                '<th>'.$registre["fecha"].'</th>'.
                '<th>'.$registre["hora"].'</th>'.
                '<th><a href="../BD249482420/info_colonia.php?id='.$registre["idColonia"].'" >'.$registre["nombreColonia"].'</a></th>'.
                '<th>'.$registre["estado"].'</th>';
        echo $cad;
        echo "</tr>";
    while ($registre=mysqli_fetch_array($resultat)) {
        echo "<tr>";
        
        $cad=   '<th><a href="consulta_tarea.php?tarea='.$registre["idTrabajo"].'" >Tarea:</a></th>'.
                '<th>'.$registre["descripcion"].'</th>'.
                '<th>'.$registre["fecha"].'</th>'.
                '<th>'.$registre["hora"].'</th>'.
                '<th><a href="../BD249482420/info_colonia.php?id='.$registre["idColonia"].'" >'.$registre["nombreColonia"].'</a></th>'.
                '<th>'.$registre["estado"].'</th>';
        echo $cad;
        echo "</tr>";
    }
    echo "</table></p>";
}
//volver
echo'<p><button onclick="document.location=\'../menu.php\'">Volver</button></p>';
mysqli_close($con);
?>
