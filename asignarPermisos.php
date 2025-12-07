<?php
// 2025111303principal.php?usuari=Joan
$user= $_GET["user"];
$role= $_GET["role"];
// preparar select per descarregar tots els privilegis de l usuari
$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"bd2_prac2");

$consulta=" select funciones.nombre as nombre
                from voluntario v
                join rol on v.idRol = rol.idRol
                join PuedeHacer on PuedeHacer.idRol=rol.idRol
                join funciones on funciones.idFunciones = PuedeHacer.idFunciones
            where v.usuario='".$user."' AND v.contraseña='".$contraseña."'";

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
