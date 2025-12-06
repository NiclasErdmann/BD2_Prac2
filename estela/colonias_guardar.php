<html>
<head></head>
<body>
<?php
$con = mysqli_connect("localhost","root","");
$db= mysqli_select_db($con,"BD2_Prac2");

// $consulta ="select entrenador.nom from entrenador"; 

$consulta ="";
mysqli_query($con,$consulta);
mysqli_close($con);


?>
</body>
</html>
