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



$consulta=" SELECT f.nombre as nombre, f.ruta as ruta
                FROM PERSONA p
                LEFT JOIN VOLUNTARIO v ON p.idPersona = v.idPersona
                LEFT JOIN ADMINAYU a ON p.idPersona = a.idPersona
                JOIN PER_ROL pr ON p.idPersona = pr.idPersona
                JOIN ROL r ON pr.idRol = r.idRol
                JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
            WHERE p.usuario='".$user."' AND p.contrasena='".$pasword."'";

$resultat = mysqli_query($con, $consulta);

if(mysqli_num_rows($resultat) == 0){
    // incorrect username or password
    $cad= 'Incorrect Username or Password';
    echo $cad;
}else{
    // "loged in"
    echo "<h2>Bienvenido $user</h2>";
    echo "<p>Páginas disponibles:</p>";
    
    while ($registre=mysqli_fetch_array($resultat)) {
        $cad= '<a href="'.$registre["ruta"].'">'.$registre["nombre"].'</a><br>';
        echo $cad;
    }
}
mysqli_close($con);
?>
