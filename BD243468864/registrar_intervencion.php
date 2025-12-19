<?php
session_start();
require_once '../header.php';
$con = mysqli_connect("localhost", "root", "", "BD201");

$idCamp = $_GET['idCamp'];
$idColonia = $_GET['idCol']; // Para listar solo gatos de esa colonia

// Necesitamos los gatos de la colonia asignada a la campaña
$sqlGatos = "SELECT g.idGato, g.nombre FROM GATO g 
             JOIN HISTORIAL h ON g.idGato = h.idGato 
             WHERE h.idColonia = $idColonia AND h.fechaIda IS NULL";
$resGatos = mysqli_query($con, $sqlGatos);

// Necesitamos la lista de profesionales veterinarios
$sqlPro = "SELECT pr.idProfesional, p.nombre FROM PROFESIONAL pr 
           JOIN PERSONA p ON pr.idPersona = p.idPersona";
$resPro = mysqli_query($con, $sqlPro);

addBreadcrumb('Registrar Acción Médica');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Intervención Veterinaria</title>
    <style>
        .form-vete { max-width: 600px; margin: 20px auto; padding: 20px; border: 2px solid #007bff; border-radius: 10px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        select, textarea, input { width: 100%; padding: 8px; margin-bottom: 10px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 10px; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    <div class="form-vete">
        <h3>Nueva Acción Médica / Autopsia</h3>
        <form action="procesar_intervencion.php" method="POST">
            <input type="hidden" name="idCampana" value="<?php echo $idCamp; ?>">
            
            <label>Gato intervenido:</label>
            <select name="idGato" required>
                <?php while($g = mysqli_fetch_assoc($resGatos)) echo "<option value='".$g['idGato']."'>".$g['nombre']."</option>"; ?>
            </select>

            <label>Veterinario:</label>
            <select name="idProfesional" required>
                <?php while($p = mysqli_fetch_assoc($resPro)) echo "<option value='".$p['idProfesional']."'>".$p['nombre']."</option>"; ?>
            </select>

            <label>Descripción del procedimiento:</label>
            <textarea name="descripcion" rows="3" placeholder="Ej: Esterilización exitosa..."></textarea>

            <label>Resultado de Autopsia (si aplica):</label>
            <textarea name="autopsia" rows="3" placeholder="Completar solo en caso de fallecimiento..."></textarea>

            <label>Comentarios adicionales:</label>
            <textarea name="comentario" rows="2"></textarea>

            <button type="submit" class="btn-save">Guardar Registro Médico</button>
        </form>
    </div>
</body>
</html>