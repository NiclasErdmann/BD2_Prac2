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
addBreadcrumb('Modifica_Permisos_Rol');
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


$rol= $_GET["rol"];

// funciones que se tiene permiso
$consulta=" SELECT f.nombre as funcion, ph.idRol, ph.idFuncion
                FROM ROL r
                JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
            WHERE r.nombre='$rol'
            ";


$resultat = mysqli_query($con, $consulta);
$registre = mysqli_fetch_array($resultat);
$idRol=$registre["idRol"];

//tite
echo '<h1>Permisos del rol "'.$rol.'"</h1>';

if(is_null($registre)){
    // rol no tiene funciones asignadas
    //$cad= 'Este rol no tiene funciones asignadas';
    //echo $cad;
}else{

    echo '<p> <b>Funciones asignadas:</b>
        <table>
            <tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'eliminar_funcion_rol.php?rol='.$rol.'&idRol='.$idRol.'&funcion='.$registre["idFuncion"].'"> eliminar </a></th>
            </tr>';
    while ($registre=mysqli_fetch_array($resultat)) {
        echo '<tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'eliminar_funcion_rol.php?rol='.$rol.'&idRol='.$idRol.'&funcion='.$registre["idFuncion"].'"> eliminar </a></th>
            </tr>';
    }
    echo '</table></p>';
}


// funciones que no se tienen permiso
$consulta=" SELECT f.nombre as funcion, f.idFuncion, i.idRol
               	FROM FUNCION f
                LEFT JOIN 
                (
                    SELECT f.idFuncion as id, r.idRol
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
    //$cad= 'Este rol tiene todas las funciones asignadas';
    //echo $cad;
}else{

    echo '<p> <b>Funciones NO asignadas:</b>
        <table>
            <tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'anyade_funcion_rol.php?rol='.$rol.'&idRol='.$idRol.'&funcion='.$registre["idFuncion"].'"> añadir </a></th>
            </tr>';
    while ($registre=mysqli_fetch_array($resultat)) {
        echo '<tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'anyade_funcion_rol.php?rol='.$rol.'&idRol='.$idRol.'&funcion='.$registre["idFuncion"].'"> añadir </a></th>
            </tr>';
    }
    echo '</table></p>';
}


mysqli_close($con);
?>
