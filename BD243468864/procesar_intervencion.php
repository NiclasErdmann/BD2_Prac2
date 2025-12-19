<?php
session_start();
// 1. Conexión a la base de datos
$con = mysqli_connect("localhost", "root", "", "BD201");

if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

// 2. Recoger datos del formulario (vienen por POST)
$idCampaña = $_POST['idCampana'];
$idGato = $_POST['idGato'];
$idProfesional = $_POST['idProfesional'];
$descripcion = mysqli_real_escape_string($con, $_POST['descripcion']);
$autopsia = mysqli_real_escape_string($con, $_POST['autopsia']);
$comentario = mysqli_real_escape_string($con, $_POST['comentario']);
$fechaActual = date('Y-m-d');

// 3. Insertar en la tabla ACCION_INDIVIDUAL (según tu SQL)
$sqlAccion = "INSERT INTO ACCION_INDIVIDUAL (fecha, descripcion, autopsia, comentario, idGato, idProfesional, idCampaña) 
              VALUES ('$fechaActual', '$descripcion', '$autopsia', '$comentario', $idGato, $idProfesional, $idCampaña)";

if (mysqli_query($con, $sqlAccion)) {
    
    // 4. Lógica de fallecimiento:
    // Si el veterinario ha escrito algo en el campo autopsia, asumimos que el gato ha muerto.
    // Según tu SQL, el gato tiene una relación con CEMENTERIO (idCementerio).
    if (!empty($autopsia)) {
        // Asignamos el cementerio por defecto (ID 1) si hay autopsia. 
        // Esto marca al gato como fallecido en vuestro modelo.
        $sqlUpdateGato = "UPDATE GATO SET idCementerio = 1 WHERE idGato = $idGato";
        mysqli_query($con, $sqlUpdateGato);
    }

    // Redirigir al listado con un mensaje de éxito
    header("Location: listar_campanas.php?success=1");
} else {
    echo "Error al registrar la acción médica: " . mysqli_error($con);
}

mysqli_close($con);
?>