<?php
session_start();

// 2025111303principal.php?user=ana&password=123
$user = $_GET['user'] ?? '';
$pasword = $_GET['password'] ?? '';

// Conexión a BD (ajusta usuario/contraseña si procede)
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

/*
$consulta= "select titol,enlace 
                from usuari 
                join rol on usuari.esunrol = rol.rol
                join potfer on potfer.rol=rol.rol
                join privilegis on privilegis.idprivilegi = potfer.privilegi
where usuari.username='".$user."'";
*/



// 1) Verificar credenciales en PERSONA
$sqlUser = sprintf("SELECT idPersona, nombre FROM PERSONA WHERE usuario='%s' AND contrasena='%s'",
    mysqli_real_escape_string($con, $user),
    mysqli_real_escape_string($con, $pasword)
);
$resUser = mysqli_query($con, $sqlUser);

if (!$resUser || mysqli_num_rows($resUser) == 0) {
    echo '<h2>Usuario o contraseña incorrectos</h2>';
    echo '<a href="login.html">Volver</a>';
    mysqli_close($con);
    exit;
}

$rowUser = mysqli_fetch_assoc($resUser);
$idPersona = $rowUser['idPersona'];
$_SESSION['usuario'] = $user;
$_SESSION['nombre'] = $rowUser['nombre'];
$_SESSION['idPersona'] = $idPersona;

// 2) Obtener idAyuntamiento (si es voluntario o adminayu)
$sqlAyu = sprintf("SELECT COALESCE(v.idAyuntamiento, a.idAyuntamiento) AS idAyuntamiento
    FROM PERSONA p
    LEFT JOIN VOLUNTARIO v ON p.idPersona = v.idPersona
    LEFT JOIN ADMINAYU a ON p.idPersona = a.idPersona
    WHERE p.idPersona = %d LIMIT 1", (int)$idPersona);

$resAyu = mysqli_query($con, $sqlAyu);
if ($resAyu && mysqli_num_rows($resAyu) > 0) {
    $rowAyu = mysqli_fetch_assoc($resAyu);
    $_SESSION['idAyuntamiento'] = $rowAyu['idAyuntamiento'];
} else {
    $_SESSION['idAyuntamiento'] = null;
}

// 3) Obtener funciones asociadas al usuario (vía sus roles)
$sqlFuncs = sprintf("SELECT f.nombre, f.ruta
    FROM PER_ROL pr
    JOIN ROL r ON pr.idRol = r.idRol
    JOIN PUEDEHACER ph ON r.idRol = ph.idRol
    JOIN FUNCION f ON ph.idFuncion = f.idFuncion
    WHERE pr.idPersona = %d",
    (int)$idPersona
);

$resFuncs = mysqli_query($con, $sqlFuncs);

echo "<h2>Bienvenido " . htmlspecialchars($_SESSION['nombre']) . "</h2>";
echo "<p>Páginas disponibles:</p>";

if ($resFuncs && mysqli_num_rows($resFuncs) > 0) {
    while ($f = mysqli_fetch_assoc($resFuncs)) {
        // Si ruta no existe en BD, construir una ruta simple a partir del nombre
        $ruta = !empty($f['ruta']) ? $f['ruta'] : 'estela/' . strtolower(str_replace(' ', '_', $f['nombre'])) . '.php';
        // Si la ruta apunta a .html pero existe la versión .php, usarla
        if (substr($ruta, -5) === '.html') {
            $possiblePhp = substr($ruta, 0, -5) . '.php';
            if (file_exists(__DIR__ . '/' . $possiblePhp)) {
                $ruta = $possiblePhp;
            }
        }
        echo '<a href="' . htmlspecialchars($ruta) . '">' . htmlspecialchars($f['nombre']) . '</a><br>';
    }
} else {
    echo '<p>(No hay funciones asignadas)</p>';
}

mysqli_close($con);
?>
