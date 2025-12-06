<?php
session_start();
require_once 'conectar_bd.php';

// Seguridad
if (!isset($_SESSION['idVoluntario']) || $_SESSION['idRol'] != 1) {
    die("Acceso denegado.");
}

$idAyuntamiento = $_SESSION['idAyuntamiento'];
$datos = null;

// Si recibimos un ID por la URL, estamos EDITANDO
if (isset($_GET['id'])) {
    $idColonia = $_GET['id'];
    
    // Verificamos que la colonia pertenezca a ESTE ayuntamiento (Seguridad crítica)
    $sql = "SELECT * FROM ColoniaFelina WHERE idColoniaFelina = ? AND idAyuntamiento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idColonia, $idAyuntamiento);
    $stmt->execute();
    $res = $stmt->get_result();
    $datos = $res->fetch_assoc();
    
    if (!$datos) die("Colonia no encontrada o no tienes permiso.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title><?php echo $datos ? 'Editar' : 'Nueva'; ?> Colonia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><?php echo $datos ? 'Editar Colonia' : 'Registrar Nueva Colonia'; ?></h4>
        </div>
        <div class="card-body">
            
            <form action="colonias_guardar.php" method="POST">
                <input type="hidden" name="idColoniaFelina" value="<?php echo $datos['idColoniaFelina'] ?? ''; ?>">

                <div class="mb-3">
                    <label class="form-label">Nombre de la Colonia:</label>
                    <input type="text" name="nombre" class="form-control" required 
                           value="<?php echo $datos['nombre'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lugar de Referencia (Texto descriptivo):</label>
                    <input type="text" name="lugarReferencia" class="form-control" required 
                           placeholder="Ej: Detrás del polideportivo municipal"
                           value="<?php echo $datos['lugarReferencia'] ?? ''; ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Coordenadas GPS:</label>
                        <input type="text" name="coordenadas" class="form-control" 
                               placeholder="Ej: 39.5712, 2.6460"
                               value="<?php echo $datos['coordenadas'] ?? ''; ?>">
                        <div class="form-text">Copia y pega desde Google Maps (Latitud, Longitud).</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número estimado de gatos:</label>
                        <input type="number" name="numeroGatos" class="form-control" 
                               value="<?php echo $datos['numeroGatos'] ?? '0'; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción / Comentarios:</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo $datos['descripcion'] ?? ''; ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="colonias_listar.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar Datos</button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>