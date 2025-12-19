<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: albirament_gato.php');
    exit;
}

// Verificar sesión
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Recibir datos del formulario
$idGato = $_POST['idGato'] ?? '';
$idColoniaNueva = $_POST['idColoniaNueva'] ?? '';

// Validar datos
if (empty($idGato) || empty($idColoniaNueva)) {
    die('Error: Faltan datos obligatorios. <a href="albirament_gato.php">Volver</a>');
}

// Obtener la fecha actual
$fechaActual = date('Y-m-d');

// NOTA: El trigger tr_ActualizarHistorial_Albirament se encarga automáticamente de:
// 1. Cerrar el historial anterior (UPDATE HISTORIAL SET fechaIda)
// 2. Crear nuevo registro en HISTORIAL con la nueva colonia
// Solo necesitamos insertar en ALBIRAMENT y el trigger hace el resto

$sqlAlbirament = "INSERT INTO ALBIRAMENT (fechaVista, idGato, idColonia) 
                 VALUES ('$fechaActual', $idGato, $idColoniaNueva)";

try {
    // Iniciar transacción
    $con->begin_transaction();
    
    $resultado = mysqli_query($con, $sqlAlbirament);
    if (!$resultado) {
        throw new Exception(mysqli_error($con));
    }
    
    // Confirmar transacción
    $con->commit();
    mysqli_close($con);
    header('Location: listar_gatos.php?modo=albirament&success=1');
    exit;
    
} catch (\Throwable $error) {
    // Revertir cambios en caso de error
    $con->rollback();
    mysqli_close($con);
    die("Error al registrar el albirament: " . $error->getMessage() . " <br><a href='albirament_gato.php'>Volver</a>");
}
?>
