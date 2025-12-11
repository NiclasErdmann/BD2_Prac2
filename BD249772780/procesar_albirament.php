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
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
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

// Iniciar transacción
mysqli_begin_transaction($con);

try {
    // 1. Obtener la fecha actual
    $fechaActual = date('Y-m-d');
    
    // 2. Cerrar el historial anterior (poner fechaIda al registro activo)
    $sqlCerrarHistorial = "UPDATE HISTORIAL 
                          SET fechaIda = ? 
                          WHERE idGato = ? 
                          AND fechaIda IS NULL";
    
    $stmtCerrar = mysqli_prepare($con, $sqlCerrarHistorial);
    mysqli_stmt_bind_param($stmtCerrar, "si", $fechaActual, $idGato);
    mysqli_stmt_execute($stmtCerrar);
    mysqli_stmt_close($stmtCerrar);
    
    // 3. Crear nuevo registro en HISTORIAL (nueva colonia)
    $sqlNuevoHistorial = "INSERT INTO HISTORIAL (fechaLlegada, fechaIda, idGato, idColonia) 
                         VALUES (?, NULL, ?, ?)";
    
    $stmtNuevo = mysqli_prepare($con, $sqlNuevoHistorial);
    mysqli_stmt_bind_param($stmtNuevo, "sii", $fechaActual, $idGato, $idColoniaNueva);
    mysqli_stmt_execute($stmtNuevo);
    mysqli_stmt_close($stmtNuevo);
    
    // 4. Registrar en la tabla ALBIRAMENT
    $sqlAlbirament = "INSERT INTO ALBIRAMENT (fechaVista, idGato, idColonia) 
                     VALUES (?, ?, ?)";
    
    $stmtAlbirament = mysqli_prepare($con, $sqlAlbirament);
    mysqli_stmt_bind_param($stmtAlbirament, "sii", $fechaActual, $idGato, $idColoniaNueva);
    mysqli_stmt_execute($stmtAlbirament);
    mysqli_stmt_close($stmtAlbirament);
    
    // Confirmar transacción
    mysqli_commit($con);
    
    // Redirigir con éxito
    mysqli_close($con);
    header('Location: listar_gatos.php?modo=albirament&success=1');
    exit;
    
} catch (Exception $e) {
    // Revertir cambios si hay error
    mysqli_rollback($con);
    mysqli_close($con);
    die("Error al registrar el albirament: " . $e->getMessage() . " <br><a href='albirament_gato.php'>Volver</a>");
}
?>
