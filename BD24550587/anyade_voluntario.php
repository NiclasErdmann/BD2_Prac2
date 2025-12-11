<?php

// Recoger datos del formulario de inscripcion
// ayuntamiento nombre, apellido, usuario, contrasena, email, telefono

$ayuntamiento = $_GET["ayuntamiento"];
$nombre = $_GET["nombre"];
$apellido = $_GET["apellido"];
$usuario = $_GET["usuario"];
$contrasena = $_GET["contrasena"];
$email = $_GET["email"];
$telefono = $_GET["telefono"];

// Validar datos not null
if (empty($ayuntamiento) || empty($nombre) || empty($apellido) || empty($usuario) || empty($contrasena) || empty($email) || empty($telefono)) {
    die("Error: Todos los campos deben estar completos.");
}

$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"bd2_prac2");
$consulta="DECLARE idA INT;
    DECLARE idP INT;

    START TRANSACTION;
        SELECT a.idAyuntamiento INTO idA
            FROM AYUNTAMIENTO a
            WHERE a.nombre = ayuntamiento;
            
        INSERT INTO PERSONA (nombre, apellido, usuario, contrasena, email, telefono) VALUES
        (nombre, apellido, usuario, contrasena, email, telefono);

        SELECT p.idPersona INTO idP
            FROM PERSONA p
            WHERE p.usuario = usuario;
        
        INSERT INTO VOLUNTARIO (idAyuntamiento, idGrupoTrabajo, idPersona) VALUES
        (idA, NULL, idP);

        -- Commit the transaction if both operations succeed
    COMMIT;";
// $consulta=" call procedure_anyade_voluntario ('".$ayuntamiento."', '".$nombre."', '".$apellido."', '".$usuario."', '".$contrasena."', '".$email."', '".$telefono."') ";
$resultat = mysqli_query($con, $consulta);