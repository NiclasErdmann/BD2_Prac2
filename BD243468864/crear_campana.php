<?php
session_start();
require_once '../header.php';
$con = mysqli_connect("localhost", "root", "", "BD201");

// Verificación de seguridad: solo Responsables (Rol 2) o Admin
if (!isset($_SESSION['idRol']) || ($_SESSION['idRol'] != 2 && $_SESSION['idRol'] != 1)) {
    die("Acceso denegado. Solo los responsables de grupo pueden crear campañas.");
}

// Obtener colonias del ayuntamiento del responsable para el desplegable
$idAyu = $_SESSION['idAyuntamiento'];
$sqlCol = "SELECT idColonia, nombre FROM COLONIA_FELINA WHERE idGrupoTrabajo IN 
           (SELECT idGrupoTrabajo FROM GRUPO_TRABAJO WHERE idAyuntamiento = $idAyu)";
$resCol = mysqli_query($con, $sqlCol);

// Obtener centros veterinarios
$sqlVet = "SELECT idCentroVet, nombre FROM CENTRO_VETERINARIO";
$resVet = mysqli_query($con, $sqlVet);

// Obtener tipos de campaña
$sqlTipo = "SELECT idTipo, tipoCampaña FROM TIPO";
$resTipo = mysqli_query($con, $sqlTipo);

addBreadcrumb('Crear Nueva Campaña');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Campaña</title>
    <style>
        .form-container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; font-family: sans-serif; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        select, input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        .btn-submit { background: #007bff; color: white; padding: 10px; border: none; width: 100%; margin-top: 20px; cursor: pointer; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    <div class="form-container">
        <h2>Nueva Campaña de Intervención</h2>
        <form action="procesar_campana.php" method="POST">
            <label>Descripción:</label>
            <textarea name="descripcion" required placeholder="Ej: Campaña de esterilización Invierno 2025"></textarea>

            <label>Colonia:</label>
            <select name="idColonia" required>
                <?php while($c = mysqli_fetch_assoc($resCol)) echo "<option value='".$c['idColonia']."'>".$c['nombre']."</option>"; ?>
            </select>

            <label>Centro Veterinario:</label>
            <select name="idCentroVet" required>
                <?php while($v = mysqli_fetch_assoc($resVet)) echo "<option value='".$v['idCentroVet']."'>".$v['nombre']."</option>"; ?>
            </select>

            <label>Tipo de Intervención:</label>
            <select name="idTipo" required>
                <?php while($t = mysqli_fetch_assoc($resTipo)) echo "<option value='".$t['idTipo']."'>".$t['tipoCampaña']."</option>"; ?>
            </select>

            <label>Fecha Inicio:</label>
            <input type="date" name="fechaInicio" required value="<?php echo date('Y-m-d'); ?>">

            <button type="submit" class="btn-submit">Publicar Campaña</button>
        </form>
    </div>
</body>
</html>