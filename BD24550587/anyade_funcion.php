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
$funcion= $_GET["funcion"];


// funciones que se anyade al rol
$consulta=' INSERT INTO PUEDEHACER (idRol, idFuncion) VALUES
            ('.$rol.','.$funcion.');
            ';

//transaction
try{
    $con->begin_transaction();
    $resultat = mysqli_query($con, $consulta);
    $con->commit();
    echo '<p>La operacion se ha realizado correctamente</p>'
    //volver
    echo'<p><button onclick="document.location=\'consulta_lista_tareas.php\'">Volver</button></p>';
}catch{

}

mysqli_close($con);
?>
