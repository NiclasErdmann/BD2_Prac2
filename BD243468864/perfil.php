<?php
session_start();
require_once '../header.php'; // Para usar los breadcrumbs

// 1. Conexión a la base de datos
$con = mysqli_connect("localhost", "root", "", "BD201");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// 2. Verificar que hay una sesión activa
if (!isset($_SESSION['idPersona'])) {
    header('Location: ../login.html');
    exit;
}

$idPersona = $_SESSION['idPersona'];

// 3. Obtener los datos del usuario actual 
$sql = "SELECT nombre, apellido, usuario, email, telefono FROM PERSONA WHERE idPersona = $idPersona";
$res = mysqli_query($con, $sql);
$usuario = mysqli_fetch_assoc($res);

addBreadcrumb('Mi Perfil');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <style>
        .perfil-card { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 2px 2px 10px #eee; }
        .dato { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px f9f9f9 solid; }
        .label { font-weight: bold; color: #555; display: block; }
        .valor { font-size: 1.1em; color: #333; }
        h2 { color: #0b5cab; border-bottom: 2px solid #0b5cab; padding-bottom: 5px; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>

    <div class="perfil-card">
        <h2>Mi Perfil de Usuario</h2>
        
        <div class="dato">
            <span class="label">Nombre completo:</span>
            <span class="valor"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></span>
        </div>

        <div class="dato">
            <span class="label">Nombre de usuario:</span>
            <span class="valor">@<?php echo htmlspecialchars($usuario['usuario']); ?></span>
        </div>

        <div class="dato">
            <span class="label">Correo electrónico:</span>
            <span class="valor"><?php echo htmlspecialchars($usuario['email']); ?></span>
        </div>

        <div class="dato">
            <span class="label">Teléfono de contacto:</span>
            <span class="valor"><?php echo htmlspecialchars($usuario['telefono'] ?: 'No registrado'); ?></span>
        </div>

        <p><small>Para modificar estos datos, contacta con el administrador del ayuntamiento.</small></p>
        <a href="../menu.php" style="color: #0b5cab; text-decoration: none;">← Volver al menú</a>
    </div>
</body>
</html>
<?php mysqli_close($con); ?>