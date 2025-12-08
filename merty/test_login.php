<?php
session_start();
// Simulamos que el usuario con idVoluntario = 1 está logueado
$_SESSION['idVoluntario'] = 1;
echo "<h2>Sesión iniciada correctamente (modo prueba)</h2>";
echo "<p>idVoluntario: " . $_SESSION['idVoluntario'] . "</p>";
echo "<p><a href='incidencias_nueva.php'>Ir a registrar incidencia</a></p>";
?>
