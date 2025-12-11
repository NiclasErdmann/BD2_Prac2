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

$rol= $_GET["rol"];


$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"bd2_prac2");


// funciones que se tiene permiso
$consulta=" SELECT f.nombre as funcion
                FROM ROL r
                JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
            WHERE r.nombre='$rol'
            ";

$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);

if(is_null($registre)){
    // rol no tiene funciones asignadas
    $cad= 'Este rol no tiene funciones asignadas';
    echo $cad;
}else{

    echo "<table>";
        echo "<tr>";
        
        $cad=   '<th>'.$registre["funcion"].'</th>'.
                '<th><a href="'.'eliminar_funcion_rol.php?rol='.$rol.'?funcion='.$registre["funcion"].'"> eliminar </a></th>';
        echo $cad;

        echo "</tr>";
    while ($registre=mysqli_fetch_array($resultat)) {
        echo "<tr>";
        
        $cad=   '<th>'.$registre["funcion"].'</th>'.
                '<th><a href="'.'eliminar_funcion_rol.php?rol='.$rol.'?funcion='.$registre["funcion"].'"> eliminar </a></th>';
        echo $cad;

        echo "</tr>";
    }
}


// funciones que no se tienen permiso
$consulta=" SELECT f.nombre as funcion
               	FROM FUNCION f
                LEFT JOIN 
                (
                    SELECT f.idFuncion as id
                	FROM ROL r
                	JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                	JOIN FUNCION f ON ph.idFuncion = f.idFuncion
           			WHERE r.nombre='$rol'
                ) as i
            	ON f.idFuncion = i.id
                WHERE i.id is NULL
            ";

$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);

if(is_null($registre)){
    // rol tiene todas las funciones asignadas
    $cad= 'Este rol tiene todas las funciones asignadas';
    echo $cad;
}else{

    echo "<table>";
        echo "<tr>";
        
        $cad=   '<th>'.$registre["funcion"].'</th>'.
                '<th><a href="'.'anyadir_funcion_rol.php?rol='.$rol.'?funcion='.$registre["funcion"].'"> añadir </a></th>';
        echo $cad;

        echo "</tr>";
    while ($registre=mysqli_fetch_array($resultat)) {
        echo "<tr>";
        
        $cad=   '<th>'.$registre["funcion"].'</th>'.
                '<th><a href="'.'anyadir_funcion_rol.php?rol='.$rol.'?funcion='.$registre["funcion"].'"> añadir </a></th>';
        echo $cad;

        echo "</tr>";
    }
}


mysqli_close($con);
?>
