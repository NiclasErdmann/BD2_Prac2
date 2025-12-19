<?php
session_start();
require_once '../header.php';
$con = mysqli_connect("localhost", "root", "", "BD201");

$idAccion = $_GET['idAccion'];

// Obtenemos los datos actuales de la intervención
$sql = "SELECT a.*, g.nombre as nombreGato 
        FROM ACCION_INDIVIDUAL a 
        JOIN GATO g ON a.idGato = g.idGato 
        WHERE a.idAccion = $idAccion";
$res = mysqli_query($con, $sql);
$accion = mysqli_fetch_assoc($res);

addBreadcrumb('Editar Historial Médico');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Editar Historial Médico</title>
    <style>
        .edit-box { max-width: 600px; margin: 30px auto; padding: 20px; border: 2px solid #ffc107; border-radius: 10px; font-family: sans-serif; }
        label { display: block; font-weight: bold; margin-top: 15px; }
        textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-save { background: #28a745; color: white; padding: 12px; border: none; width: 100%; margin-top: 20px; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    <div class="edit-box">
        <h2>Modificar Acción Médica</h2>
        <p><strong>Gato:</strong> <?php echo $accion['nombreGato']; ?></p>
        <p><strong>Fecha original:</strong> <?php echo $accion['fecha']; ?></p>

        <form action="actualizar_historial.php" method="POST">
            <input type="hidden" name="idAccion" value="<?php echo $idAccion; ?>">
            
            <label>Descripción del Procedimiento:</label>
            <textarea name="descripcion" rows="4"><?php echo $accion['descripcion']; ?></textarea>
            
            <label>Resultado de Autopsia (si falleció):</label>
            <textarea name="autopsia" rows="3"><?php echo $accion['autopsia']; ?></textarea>

            <label>Comentarios adicionales:</label>
            <textarea name="comentario" rows="2"><?php echo $accion['comentario']; ?></textarea>

            <button type="submit" class="btn-save">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>