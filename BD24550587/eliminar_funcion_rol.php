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


$rol= $_GET["rol"];
$idRol= $_GET["idRol"];
$idFunc= $_GET["funcion"];


// funciones que se anyade al rol
$consulta=' DELETE FROM puedehacer
                WHERE idRol ='.$idRol.' AND idFuncion = '.$idFunc.';
            ';
//transaction
try{
    $con->begin_transaction();
    $resultat = mysqli_query($con, $consulta);
    $con->commit();
    echo '<p>La operacion se ha realizado correctamente</p>';
    //volver
    echo'<p><button onclick="document.location=\'modifica_permisos_rol.php?rol='.$rol.'\'">Volver</button></p>';
}catch( \Throwable $error ){
    $con->rollback(); 
    echo '<p>La operacion no se ha podido realizar</p>';
    //volver
    echo'<p><button onclick="document.location=\'modifica_permisos_rol.php?rol='.$rol.'\'">Volver</button></p>';
}

mysqli_close($con);
?>
