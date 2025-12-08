<?php
session_start();

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión
$idAyuntamiento = $_SESSION['idAyuntamiento'] ?? null;
if (!$idAyuntamiento) {
    die('Error: no se detectó ayuntamiento en la sesión.');
}

// Obtener datos del formulario
$accion = $_POST['accion'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$idResponsable = !empty($_POST['idResponsable']) ? (int)$_POST['idResponsable'] : null;

// Validar datos obligatorios
if (empty($nombre)) {
    die('Error: el nombre del grupo es obligatorio.');
}

if ($accion === 'crear') {
    // Crear nuevo grupo
    $sql = "INSERT INTO GRUPO_TRABAJO (nombre, descripcion, idResponsable, idAyuntamiento) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $nombre, $descripcion, $idResponsable, $idAyuntamiento);
    
    if (mysqli_stmt_execute($stmt)) {
        $nuevoId = mysqli_insert_id($con);
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        
        // Redirigir a la página de información del nuevo grupo
        header("Location: info_grupoTrabajo.php?id=$nuevoId");
        exit;
    } else {
        die('Error al crear el grupo: ' . mysqli_error($con));
    }
    
} elseif ($accion === 'editar') {
    // Editar grupo existente
    $idGrupoTrabajo = $_POST['idGrupoTrabajo'] ?? null;
    
    if (!$idGrupoTrabajo) {
        die('Error: ID de grupo no proporcionado.');
    }
    
    $sql = "UPDATE GRUPO_TRABAJO 
            SET nombre = ?, descripcion = ?, idResponsable = ?
            WHERE idGrupoTrabajo = ? AND idAyuntamiento = ?";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssiii", $nombre, $descripcion, $idResponsable, $idGrupoTrabajo, $idAyuntamiento);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        
        // Redirigir a la página de información del grupo
        header("Location: info_grupoTrabajo.php?id=$idGrupoTrabajo");
        exit;
    } else {
        die('Error al actualizar el grupo: ' . mysqli_error($con));
    }
    
} else {
    die('Error: acción no válida.');
}
?>
