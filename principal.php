<?php
// 2025111303principal.php?usuari=Joan
$user= $_GET["usuari"];
// preparar select per descarregar tots els privilegis de l usuari
$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"20251113privilegis");

$consulta= "select titol,enlace from usuari join rol on usuari.esunrol = rol.rol join potfer on potfer.rol=rol.rol join privilegis on privilegis.idprivilegi = potfer.privilegi
where usuari.username='".$user."'";
$resultat = mysqli_query($con, $consulta);
while ($registre=mysqli_fetch_array($resultat)) {
    $cad= '<a href="'.$registre["enlace"].'">'.$registre["titol"].'</a><br>';
    echo $cad;
}
mysqli_close($con);
?>
