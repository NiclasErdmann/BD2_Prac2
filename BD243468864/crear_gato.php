<?php
session_start();
require_once '../header.php'; // Para mantener los breadcrumbs
$con = mysqli_connect("localhost", "root", "", "BD201");

// Necesitamos las colonias para que el usuario elija dónde vive el gato
$sqlColonias = "SELECT idColonia, nombre FROM COLONIA_FELINA ORDER BY nombre";
$resColonias = mysqli_query($con, $sqlColonias);

addBreadcrumb('Añadir Nuevo Gato');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrar Moix</title>
    <style>
        .form-container { max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; }
        .campo { margin-bottom: 15px; }
        label { display: block; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    <div class="form-container">
        <h2>Nuevo Registro Felino</h2>
        <form action="procesar_gato.php" method="POST">
            <div class="campo">
                <label>Nombre del Gato:</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="campo">
                <label>Número XIP (si tiene):</label>
                <input type="text" name="numXIP">
            </div>
            <div class="campo">
                <label>Sexo:</label>
                <select name="sexo">
                    <option value="Macho">Macho</option>
                    <option value="Hembra">Hembra</option>
                    <option value="Desconocido">Desconocido</option>
                </select>
            </div>
            <div class="campo">
                <label>Descripción / Color:</label>
                <textarea name="descripcion" placeholder="Ej: Atigrado naranja, muy sociable..."></textarea>
            </div>
            <div class="campo">
                <label>Colonia Inicial:</label>
                <select name="idColonia" required>
                    <option value="">-- Selecciona Colonia --</option>
                    <?php while($col = mysqli_fetch_assoc($resColonias)) {
                        echo "<option value='".$col['idColonia']."'>".$col['nombre']."</option>";
                    } ?>
                </select>
            </div>
            <button type="submit" class="btn">Registrar Gato</button>
        </form>
    </div>
</body>
</html>