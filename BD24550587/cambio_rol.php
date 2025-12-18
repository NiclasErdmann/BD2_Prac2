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


$nuevo_idRol= $_GET["nuevo_idRol"];
$viejo_idRol= $_GET["viejo_idRol"];
$idPersona= $_GET["idPersona"];
$usuario= $_GET["usuario"];

// cambio de rol a $idRol de la persona $idPersona
$consulta1=' DELETE FROM PER_ROL
                WHERE idRol ='.$viejo_idRol.';
            ';
$consulta2=' INSERT INTO PER_ROL (idPersona, idRol) VALUES
                ('.$idPersona.','.$nuevo_idRol.');
            ';
//echo $consulta;
//transaction
try{
    $con->begin_transaction();
    $resultat = mysqli_query($con, $consulta1);
    $resultat = mysqli_query($con, $consulta2);
    $con->commit();
    echo '<p>La operacion se ha realizado correctamente</p>';
    //volver
    echo'<p><button onclick="document.location=\'modifica_rol_persona.php?usuario='.$usuario.'\'">Volver</button></p>';
}catch( \Throwable $error ){
    echo $error;
    $con->rollback(); 
    echo '<p>La operacion no se ha podido realizar</p>';
    //volver
    echo'<p><button onclick="document.location=\'modifica_rol_persona.php?usuario='.$usuario.'\'">Volver</button></p>';
}

mysqli_close($con);
?>
