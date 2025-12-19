<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "BD201");

if (!$con) { die("Error"); }

$idAccion = $_POST['idAccion'];
$descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
$autopsia = mysqli_real_escape_string($con, $_POST['autopsia']);
$comentario = mysqli_real_escape_string($con, $_POST['comentario']);

// Actualizamos la tabla ACCION_INDIVIDUAL
$sql = "UPDATE ACCION_INDIVIDUAL 
        SET descripcion = '$descripcion', 
            autopsia = '$autopsia', 
            comentario = '$comentario' 
        WHERE idAccion = $idAccion";

if (mysqli_query($con, $sql)) {
    // Redirigimos a las campañas con mensaje de éxito
    header("Location: listar_campanas.php?update=success");
} else {
    echo "Error al actualizar: " . mysqli_error($con);
}

mysqli_close($con);
?>