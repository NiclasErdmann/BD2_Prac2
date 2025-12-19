<?php
session_start();

// Si la sesión no está iniciada, redirigir al formulario de login
if (!isset($_SESSION['usuario']) || !isset($_SESSION['idPersona'])) {
    header('Location: login.html');
    exit;
}

// Incluir funciones de breadcrumbs
require_once 'header.php';

// Resetear breadcrumbs en el menú principal
resetBreadcrumbs();

// Añadir breadcrumb del menú con ruta dinámica
$scriptPath = $_SERVER['SCRIPT_NAME'];
$projectBase = substr($scriptPath, 0, strpos($scriptPath, 'BD201') + 9);
addBreadcrumb('Menú Principal', $projectBase . '/menu.php');

$con = mysqli_connect('localhost', 'root', '', 'BD201');
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

$idPersona = (int) $_SESSION['idPersona'];

// Recuperar funciones disponibles para el usuario según su rol
$sqlFuncs = sprintf(
    "SELECT f.nombre, f.ruta
     FROM PER_ROL pr
     JOIN ROL r ON pr.idRol = r.idRol
     JOIN PUEDEHACER ph ON r.idRol = ph.idRol
     JOIN FUNCION f ON ph.idFuncion = f.idFuncion
     WHERE pr.idPersona = %d",
    $idPersona
);

$resFuncs = mysqli_query($con, $sqlFuncs);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú principal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        h2 { margin-bottom: 10px; }
        ul { list-style: none; padding: 0; }
        li { margin: 8px 0; }
        a { color: #0b5cab; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .logout { margin-top: 24px; display: inline-block; }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <h2>Bienvenido <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
    <p>Funciones disponibles:</p>
    <ul>
        <?php
        if ($resFuncs && mysqli_num_rows($resFuncs) > 0) {
            while ($f = mysqli_fetch_assoc($resFuncs)) {
                $ruta = !empty($f['ruta'])
                    ? $f['ruta']
                    : 'BD249482420/' . strtolower(str_replace(' ', '_', $f['nombre'])) . '.php';

                // Si la ruta termina en .html pero existe la .php, usar la .php
                if (substr($ruta, -5) === '.html') {
                    $possiblePhp = substr($ruta, 0, -5) . '.php';
                    if (file_exists(__DIR__ . '/' . $possiblePhp)) {
                        $ruta = $possiblePhp;
                    }
                }

                echo '<li><a href="' . htmlspecialchars($ruta) . '">' . htmlspecialchars($f['nombre']) . '</a></li>';
            }
        } else {
            echo '<li>(No hay funciones asignadas)</li>';
        }
        ?>
    </ul>

    <a class="logout" href="login.html">Cerrar sesión</a>
</body>
</html>
<?php
mysqli_close($con);
?>
