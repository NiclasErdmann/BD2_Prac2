<?php
$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"bd2_prac2");
// seleccion ayuntamiento
//nombre, apellido, usuario, contrasena, email, telefono
$consulta=" SELECT a.nombre as ayuntamiento
                FROM AYUNTAMIENTO a
            ";
$resultat = mysqli_query($con, $consulta);

?>
    <form action="anyade_voluntario.php">
        <label for="ayuntamiento">Selecciona tu ayuntamiento:</label>
        <select name="ayuntamiento" id="ayuntamiento">
        <?php
            while ($registre=mysqli_fetch_array($resultat)) {     
                $cad='<option value='.$registre["ayuntamiento"].'>'.$registre["ayuntamiento"].'</option>';
                echo $cad;
            }
        ?>
        </select>
        <br>
        <label for="nombre">Primer Nombre:</label>
        <input type="text" id="nombre" name="nombre">

        <br>
        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido">

        <br>
        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario">

        <br>
        <label for="contrasena">Contraseña:</label>
        <input type="text" id="apellido" name="contrasena">

        <br>
        <label for="email">Email:</label>
        <input type="text" id="apellido" name="email">
        
        <br>
        <label for="telefono">Telefono:</label>
        <input type="text" id="apellido" name="telefono">

        <br><br>
        <input type="submit" value="submit">
    </form>
<?php


?>



