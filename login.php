<?php
// 2025111303principal.php?usuari=Joan
$user= $_GET["user"];
$pasword= $_GET["password"];
// preparar select per descarregar tots els privilegis de l usuari
$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"bd2_prac2");

/*
$consulta= "select titol,enlace 
                from usuari 
                join rol on usuari.esunrol = rol.rol
                join potfer on potfer.rol=rol.rol
                join privilegis on privilegis.idprivilegi = potfer.privilegi
where usuari.username='".$user."'";
*/



$consulta=" SELECT f.nombre as nombre
                FROM PERSONA p
                LEFT JOIN VOLUNTARIO v ON p.idPersona = v.idPersona
                LEFT JOIN ADMINAYU a ON p.idPersona = a.idPersona
                JOIN PER_ROL pr ON p.idPersona = pr.idPersona
                JOIN ROL r ON pr.idRol = r.idRol
                JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
            WHERE p.usuario='".$user."' AND p.contrasena='".$pasword."'";

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
