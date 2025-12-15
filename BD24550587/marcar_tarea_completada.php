<?php

// marca la tarea dada por parametro como completado y añade el comentario dado por parametro

// Recoger datos de consulta tarea
// comentario

// comentario puede ser null o string vacio
$comentario = $_GET["comentario"];
$tarea = $_GET["tarea"];

$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"bd2_prac2");

$consulta="
        UPDATE TRABAJO t
        SET estado = 'completado', comentario = '".$comentario."'
        WHERE t.idTrabajo = ".$tarea.";
            ";
try{
    //transaction
    $con->begin_transaction();
    $resultat = mysqli_query($con, $consulta);
    $con->commit();
    echo '<p>Operacion compleatada.</p>';
    
    echo'<button onclick="document.location=\'consulta_tarea.php?tarea='.$tarea.'\'">Volver a la Tarea</button>';
}
catch ( \Throwable $error ) {
    $con->rollback(); 
    echo '<p>No se pudo completar la operacion.</p>';
    
    echo'<button onclick="document.location=\'consulta_tarea.php?tarea='.$tarea.'\'">Volver a la Tarea</button>';

}