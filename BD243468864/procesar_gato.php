<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "BD201");

if (!$con) { die("Error: " . mysqli_connect_error()); }

$nombre = $_POST['nombre'];
$numXIP = $_POST['numXIP'];
$sexo = $_POST['sexo'];
$descripcion = $_POST['descripcion'];
$idColonia = $_POST['idColonia'];
$fechaActual = date('Y-m-d');

// 1. Insertar en la tabla GATO
$sqlGato = "INSERT INTO GATO (nombre, numXIP, sexo, descripcion) 
            VALUES ('$nombre', '$numXIP', '$sexo', '$descripcion')";

if (mysqli_query($con, $sqlGato)) {
    $idGatoRecienCreado = mysqli_insert_id($con); // Obtenemos el ID que MySQL le dio al gato

    // 2. Crear el historial inicial (relación Gato-Colonia) 
    $sqlHistorial = "INSERT INTO HISTORIAL (fechaLlegada, idGato, idColonia) 
                     VALUES ('$fechaActual', $idGatoRecienCreado, $idColonia)";
    
    if (mysqli_query($con, $sqlHistorial)) {
        header("Location: ../BD249772780/listar_gatos.php?modo=ver&success=1");
    } else {
        echo "Error en historial: " . mysqli_error($con);
    }
} else {
    echo "Error en gato: " . mysqli_error($con);
}

mysqli_close($con);
?>