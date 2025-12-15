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

//tite
echo '<h1>Roles de los usuarios</h1>';

// ver usuarios y sus roles
$consulta=" SELECT p.usuario as usuario, r.nombre as rol
                FROM PERSONA p
                JOIN PER_ROL pr ON p.idPersona = pr.idPersona
                JOIN ROL r ON pr.idRol = r.idRol

            ";

$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);
if(is_null($registre)){
    // Querry error
    $cad= 'Querry error';
    echo $cad;
}else{
    // usuarios y sus roles
    echo "<table>";
        echo "<tr>";
        
        $cad=   '<th><a href="'.'modifica_rol_persona.php?id='.$registre["usuario"].'"> '.$registre["usuario"].' </a></th>'.
                '<th><a href="'.'modifica_permisos_rol.php?rol='.$registre["rol"].'"> '.$registre["rol"].' </a></th>';
        echo $cad;
        echo "</tr>";
    while ($registre=mysqli_fetch_array($resultat)) {
        echo "<tr>";
        
        $cad=   '<th><a href="'.'modifica_rol_persona.php?usuario='.$registre["usuario"].'"> '.$registre["usuario"].' </a></th>'.
                '<th><a href="'.'modifica_permisos_rol.php?rol='.$registre["rol"].'"> '.$registre["rol"].' </a></th>';
        echo $cad;
        echo "</tr>";
    }
}
mysqli_close($con);
?>
