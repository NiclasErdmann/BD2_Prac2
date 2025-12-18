<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

// Añadir breadcrumb
addBreadcrumb('Tarea');
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

// ver tarea
$tarea= $_GET["tarea"];

$consulta=" SELECT  t.descripcion, fecha, hora, estado, c.nombre as nombreColonia, c.idColonia, t.comentario, 
                    c.numeroGatos, m.nombre as nombreComida, m.calidad as calidadComida,
                    m.caracteristicas as caracteristicasComida, m.idMarcaComida, m.pesoPorGato
            FROM TRABAJO t
            JOIN COLONIA_FELINA c ON c.idColonia = t.idColonia
            LEFT JOIN MARCACOMIDA m ON m.idMarcaComida = t.idMarcaComida
            WHERE t.idTrabajo = ".$tarea."

            ";
$resultat = mysqli_query($con, $consulta);

$registre = mysqli_fetch_array($resultat);

if(is_null($registre)){
    // Querry error
    $cad= 'No se encontraro la tarea';
    echo $cad;
}else{
    //descripcion basica de la tarea
    $cad=   '<p><table>'.
                '<tr> <th>Descripcion</th>      <th>'.$registre["descripcion"].'   </th> <t/r>'.
                '<tr> <th>Fecha:</th>           <th>'.$registre["fecha"].'         </th> <t/r>'.
                '<tr> <th>Hora:</th>            <th>'.$registre["hora"].'          </th> <t/r>'.
                '<tr> <th>Nombre Colonia:</th>  <th><a href="../BD249482420/info_colonia.php?id='.$registre["idColonia"].'" >'.$registre["nombreColonia"].'</a> </th> <t/r>'.
                '<tr> <th>Estado:</th>          <th>'.$registre["estado"].'        </th> <t/r>'.
            '</table></p>';
    echo $cad;

    //comida en caso de que se trate de una tarea de dar de comer.
    if($registre["idMarcaComida"] != NULL){
        $cad='
            <p><table>
                <tr> 
                    <th>Colonia:</th>
                    <th>'.$registre["nombreColonia"].'</th>
                    <th>Numero de Gatos:</th>
                    <th>'.$registre["numeroGatos"].'</th>
                    <th>Peso de comida total / dia (gramos):</th>
                    <th>'.$registre["numeroGatos"]*$registre["pesoPorGato"].'</th>
                <t/r>
            </table></p>

            <p><table>
            <tr> 
                    <th></th>
                    <th>Nombre</th>
                    <th>Calidad Comida</th>
                    <th>Caracteristicas Comida</th>
                    <th>Peso Por Gato</th>
                <t/r>
                <tr> 
                    <th>Comida</th>
                    <th>'.$registre["nombreComida"].'</th>
                    <th>'.$registre["calidadComida"].'</th>
                    <th>'.$registre["caracteristicasComida"].'</th>
                    <th>'.$registre["pesoPorGato"].'</th>
                <t/r>
            </table></p>';
    echo $cad;
    }


    if($registre["estado"] == "pendiente" || $registre["estado"] == NULL){
        //trabajo pendiente
        ?>
            <form action="marcar_tarea_completada.php">
                <input type="hidden" name="tarea" value="<?php echo $tarea; ?>" />
                <p><label for="comentario">Comentario:</label></p>
                <textarea id="comentario" name="comentario" rows="4" cols="50"></textarea>
                <br>
                <br>
                <input type="submit" value="Marcar como completado">
            </form>
        <?php

    }else{
        //trabajo completado
        echo "Comentario:<br>";
        
        if($registre["comentario"] == NULL || $registre["comentario"] == ""){
            echo "Comentario vacio.";
        }else{
            echo $registre["comentario"];
        }
    }

    //volver
    echo'<p><button onclick="document.location=\'consulta_lista_tareas.php\'">Volver</button></p>';
}

mysqli_close($con);
?>
