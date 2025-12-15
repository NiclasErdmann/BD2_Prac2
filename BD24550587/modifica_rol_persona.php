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


$usuario= $_GET["usuario"];

//tite
echo '<h1>Permisos del usuario: '.$usuario.'</h1>';

// ver usuarios y sus roles

$consulta=" SELECT f.nombre as funcion, r.nombre as rol
                FROM PERSONA p
                JOIN PER_ROL pr ON p.idPersona = pr.idPersona
                JOIN ROL r ON pr.idRol = r.idRol
                JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
            WHERE p.usuario='$usuario'
            ";

$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);

if(is_null($registre)){
    // usuario no encontrado
    $cad= 'Usuario no encontrado';
    echo $cad;
}else{

    echo "<table>";
        echo "<tr>";
        
         echo '<tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'modifica_permisos_rol.php?rol='.$registre["rol"].'"> '.$registre["rol"].' </a></th>
            </tr>';
    while ($registre=mysqli_fetch_array($resultat)) {
        echo '<tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'modifica_permisos_rol.php?rol='.$registre["rol"].'"> '.$registre["rol"].' </a></th>
            </tr>';
    }
}

mysqli_close($con);
?>
