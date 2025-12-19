<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

// Añadir breadcrumb
addBreadcrumb('Modifica_Rol_Persona');
displayBreadcrumbs();

//style
echo 
"<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        text-align: left;
        padding: 8px;
        border: 1px solid black;
    }

    tr:nth-child(even) {
        background-color: #D6EEEE;
    }
</style>";


$usuario= $_GET["usuario"];

//tite
echo '<h1>Permisos del usuario: '.$usuario.'</h1>';

// ver usuarios y sus roles

$consulta=" SELECT f.nombre as funcion, r.nombre as rol, p.idPersona, r.idRol
                FROM PERSONA p
                JOIN PER_ROL pr ON p.idPersona = pr.idPersona
                JOIN ROL r ON pr.idRol = r.idRol
                JOIN PUEDEHACER ph ON r.idRol = ph.idRol
                JOIN FUNCION f ON ph.idFuncion = f.idFuncion
            WHERE p.usuario='$usuario'
            ";

$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);

$rol;
$idRol;
$idPersona;

if(is_null($registre)){
    // usuario no encontrado
    $cad= 'Usuario no encontrado';
    echo $cad;
}else{
    $rol = $registre["rol"];
    $idRol = $registre["idRol"];
    $idPersona = $registre["idPersona"];
    echo "<p><table>";
        echo "<tr>";
        
         echo '<tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'modifica_permisos_rol.php?rol='.$registre["rol"].'"> '.$registre["rol"].' </a></th>
            </tr>';
    while ($registre=mysqli_fetch_array($resultat)) {
        echo '<tr>
                <th>'.$registre["funcion"].'</th>
                <th><a href="'.'modifica_permisos_rol.php?rol='.$registre["rol"].'"> '.$registre["rol"].' </a></th>
            </tr>';
    }
    echo "</table></p>";
}


// todos los roles que no sean el del usuario para cambiar rol
$consulta=' SELECT nombre, idRol
                FROM ROL r
            WHERE r.nombre!="'.$rol.'"
            ';
//echo $idPersona;
$resultat = mysqli_query($con, $consulta);

?>
<p>
    <form action="cambio_rol.php">
        <label for="Cambio_Rol">Selecciona cambio de rol:</label>
        <input type="hidden" name="idPersona" value="<?php echo $idPersona; ?>" />
        <input type="hidden" name="usuario" value="<?php echo $usuario; ?>" />
        <input type="hidden" name="viejo_idRol" value="<?php echo $idRol; ?>" />
        <select name="nuevo_idRol" id="nuevo_idRol">
            <?php
                while ($registre=mysqli_fetch_array($resultat)) {     
                    $cad='<option value='.$registre["idRol"].'>'.$registre["nombre"].'</option>';
                    echo $cad;
                }
            ?>
        </select>
        
        <br><br>
        <input type="submit" value="submit">
    </form>
</p>
<?php

mysqli_close($con);
?>
