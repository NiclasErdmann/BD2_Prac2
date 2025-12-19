<?php
//este archivo realiza el INSERT en la base de datos
session_start();
$con = mysqli_connect("localhost", "root", "", "BD201");

$desc = mysqli_real_escape_string($con, $_POST['descripcion']);
$idColonia = $_POST['idColonia'];
$idCentroVet = $_POST['idCentroVet'];
$idTipo = $_POST['idTipo'];
$fechaInicio = $_POST['fechaInicio'];

$sql = "INSERT INTO CAMPAÑA_INTERVENCION (fechaInicio, descripcion, idCentroVet, idColonia, idTipo) 
        VALUES ('$fechaInicio', '$desc', $idCentroVet, $idColonia, $idTipo)";

if (mysqli_query($con, $sql)) {
    header("Location: listar_campanas.php?creado=1");
} else {
    echo "Error al crear campaña: " . mysqli_error($con);
}
mysqli_close($con);
?>