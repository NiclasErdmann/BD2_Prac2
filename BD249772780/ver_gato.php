<?php
session_start();

// Incluir funciones de breadcrumbs
require_once '../header.php';

// Conexión a BD
$con = mysqli_connect("localhost", "root", "", "BD2_Prac2");
if (!$con) {
    die('Error de conexión: ' . mysqli_connect_error());
}

// Verificar sesión
if (!isset($_SESSION['idPersona'])) {
    die('Error: Debes iniciar sesión. <a href="../login.html">Ir al login</a>');
}

// Obtener idGato
$idGato = $_GET['idGato'] ?? '';

if (empty($idGato)) {
    die('Error: No se especificó el gato. <a href="listar_gatos.php?modo=ver">Volver</a>');
}

// Obtener datos del gato
$sqlGato = "SELECT g.idGato, g.nombre, g.numXIP, g.sexo, g.descripcion, g.foto,
                   c.nombre as cementerio
            FROM GATO g
            LEFT JOIN CEMENTERIO c ON g.idCementerio = c.idCementerio
            WHERE g.idGato = $idGato";

$resultGato = mysqli_query($con, $sqlGato);
$gato = mysqli_fetch_assoc($resultGato);

if (!$gato) {
    mysqli_close($con);
    die('Error: Gato no encontrado. <a href="listar_gatos.php?modo=ver">Volver</a>');
}

// Obtener historial de colonias
$sqlHistorial = "SELECT h.idHistorial, h.fechaLlegada, h.fechaIda,
                        c.nombre as nombreColonia, c.lugarReferencia
                 FROM HISTORIAL h
                 INNER JOIN COLONIA_FELINA c ON h.idColonia = c.idColonia
                 WHERE h.idGato = $idGato
                 ORDER BY h.fechaLlegada DESC";

$resultHistorial = mysqli_query($con, $sqlHistorial);

// Obtener incidencias del gato
$sqlIncidencias = "SELECT i.fecha, i.tipo, i.descripcion,
                          CONCAT(p.nombre, ' ', p.apellido) as voluntario
                   FROM INCIDENCIA i
                   INNER JOIN VOLUNTARIO v ON i.idVoluntario = v.idVoluntario
                   INNER JOIN PERSONA p ON v.idPersona = p.idPersona
                   WHERE i.idGato = $idGato
                   ORDER BY i.fecha DESC";

$resultIncidencias = mysqli_query($con, $sqlIncidencias);

//Obtener opciones médicas
$sqlMedico = "SELECT a.*, p.nombre as nombreVet 
              FROM ACCION_INDIVIDUAL a
              JOIN PROFESIONAL pr ON a.idProfesional = pr.idProfesional
              JOIN PERSONA p ON pr.idPersona = p.idPersona
              WHERE a.idGato = $idGato 
              ORDER BY a.fecha DESC";
$resultMedico = mysqli_query($con, $sqlMedico);

// Añadir breadcrumb
addBreadcrumb('Ficha de Gato: ' . $gato['nombre']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de <?php echo htmlspecialchars($gato['nombre']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .ficha-gato {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }
        .ficha-header {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 20px;
        }
        .foto-container {
            width: 100%;
        }
        .gato-foto {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            background-color: #f0f0f0;
        }
        .sin-foto {
            width: 100%;
            height: 300px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        .datos-basicos {
            padding: 10px;
        }
        .datos-basicos h2 {
            margin-top: 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .campo {
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .campo strong {
            display: inline-block;
            width: 120px;
            color: #333;
        }
        .seccion {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }
        .seccion h2 {
            margin-top: 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        .btn-editar-medico {
            background-color: #ffc107;
            color: black;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.9em;
            border: 1px solid #333;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .estado-activo {
            font-weight: bold;
        }
        .btn-volver {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border: 1px solid #333;
        }
        .btn-volver:hover {
            background-color: #555;
        }
        .alert-fallecido {
            background-color: white;
            border: 2px solid #333;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .descripcion-completa {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <div class="container">
        <h1>Ficha de Gato</h1>

        <?php if ($gato['cementerio']): ?>
            <div class="alert-fallecido">
                Este gato ha fallecido y se encuentra en: <?php echo htmlspecialchars($gato['cementerio']); ?>
            </div>
        <?php endif; ?>

        <div class="ficha-gato">
            <div class="ficha-header">
                <div class="foto-container">
                    <?php if ($gato['foto']): ?>
                        <img src="../<?php echo htmlspecialchars($gato['foto']); ?>" 
                             alt="<?php echo htmlspecialchars($gato['nombre']); ?>" 
                             class="gato-foto">
                    <?php else: ?>
                        <div class="sin-foto">Foto no disponible</div>
                    <?php endif; ?>
                </div>

                <div class="datos-basicos">
                    <h2><?php echo htmlspecialchars($gato['nombre']); ?></h2>
                    
                    <div class="campo">
                        <strong>ID:</strong> <?php echo htmlspecialchars($gato['idGato']); ?>
                    </div>
                    
                    <div class="campo">
                        <strong>XIP:</strong> <?php echo $gato['numXIP'] ? htmlspecialchars($gato['numXIP']) : 'Sin XIP'; ?>
                    </div>
                    
                    <div class="campo">
                        <strong>Sexo:</strong> <?php echo $gato['sexo'] ? htmlspecialchars($gato['sexo']) : 'No especificado'; ?>
                    </div>

                    <?php if ($gato['descripcion']): ?>
                        <div class="campo">
                            <strong>Descripción:</strong>
                            <div class="descripcion-completa">
                                <?php echo nl2br(htmlspecialchars($gato['descripcion'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="seccion">
            <h2>Historial Médico</h2>
            <?php if (mysqli_num_rows($resultMedico) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Intervención / Descripción</th>
                            <th>Veterinario</th>
                            <?php if ($_SESSION['idRol'] == 4): // Solo visible para Veterinarios ?>
                                <th>Acción</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($med = mysqli_fetch_assoc($resultMedico)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($med['fecha'])); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($med['descripcion']); ?>
                                    <?php if(!empty($med['autopsia'])): ?>
                                        <br><small style="color:red;"><b>Autopsia:</b> <?php echo htmlspecialchars($med['autopsia']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($med['nombreVet']); ?></td>
                                <?php if (isset($_SESSION['idRol']) && $_SESSION['idRol'] == 4): ?>
                                    <td>
                                        <a href="../BD243468864/editar_historial.php?idAccion=<?php echo $med['idAccion']; ?>" 
                                        class="btn-editar-medico">
                                        Editar
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay historial médico registrado para este gato.</p>
            <?php endif; ?>
        </div>

        <!-- Historial de colonias -->
        <div class="seccion">
            <h2>Historial de Colonias</h2>
            <?php if (mysqli_num_rows($resultHistorial) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Colonia</th>
                            <th>Lugar de Referencia</th>
                            <th>Fecha Llegada</th>
                            <th>Fecha Ida</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($hist = mysqli_fetch_assoc($resultHistorial)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hist['nombreColonia']); ?></td>
                                <td><?php echo $hist['lugarReferencia'] ? htmlspecialchars($hist['lugarReferencia']) : '-'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($hist['fechaLlegada'])); ?></td>
                                <td><?php echo $hist['fechaIda'] ? date('d/m/Y', strtotime($hist['fechaIda'])) : '-'; ?></td>
                                <td <?php echo !$hist['fechaIda'] ? 'class="estado-activo"' : ''; ?>>
                                    <?php echo !$hist['fechaIda'] ? 'Colonia actual' : ''; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay historial de colonias registrado.</p>
            <?php endif; ?>
        </div>

        <!-- Incidencias -->
        <div class="seccion">
            <h2>Incidencias Registradas</h2>
            <?php if (mysqli_num_rows($resultIncidencias) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($inc = mysqli_fetch_assoc($resultIncidencias)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($inc['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($inc['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($inc['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($inc['voluntario']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay incidencias registradas para este gato.</p>
            <?php endif; ?>
        </div>

        <a href="listar_gatos.php?modo=ver" class="btn-volver">Volver al listado</a>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
