<?php
// 1. Conectar a BD
$con = mysqli_connect("localhost","root","");
$db= mysqli_select_db($con,"BD2_Prac2");

// 2. Obtener el id del ayuntamiento logueado (desde $_SESSION)
$idAyuntamiento = $_SESSION['idAyuntamiento']; // o similar

// 3. Consultar colonias de ese ayuntamiento
$sql = "SELECT idColoniaFelina, nombre, lugarReferencia, numeroGatos 
        FROM colonias 
        WHERE idAyuntamiento = $idAyuntamiento";
        
$resultado = $conn->query($sql);

// 4. Incluir la vista HTML
include('listar_colonias.php');
?>