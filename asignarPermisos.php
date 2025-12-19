<?php
// 2025111303principal.php?usuari=Joan
$user= $_GET["user"];
$role= $_GET["role"];
// preparar select per descarregar tots els privilegis de l usuari
$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"BD201");

$consulta=" SELECT f.nombre as nombre
                FROM PERSONA p
                JOIN PER_ROL pr ON p.idPersona = pr.idPersona
                JOIN ROL r ON pr.idRol = r.idRol
                JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
            WHERE p.usuario='".$user."' AND r.nombre='".$role."'";

$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);
if(is_null($registre)){
    // incorrect username or pasword
    $cad= 'Incorrect Usename or Password';
    echo $cad;
}else{
    // "loged in"
    
    while ($registre=mysqli_fetch_array($resultat)) {
        $cad= '<a href="'.$registre["nombre"].'">'."asdasds".'</a><br>';
        echo $cad;
    }
}
mysqli_close($con);
?>
