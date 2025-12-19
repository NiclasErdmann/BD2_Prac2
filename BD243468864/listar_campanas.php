<?php
session_start();
require_once '../header.php'; // Gestión de breadcrumbs
$con = mysqli_connect("localhost", "root", "", "BD201");

// Consultar campañas activas con detalles del centro y la colonia
$sql = "SELECT c.idCampaña, c.idColonia, c.descripcion, c.fechaInicio, c.fechaFin, 
               cv.nombre as centro, col.nombre as colonia, t.tipoCampaña
        FROM CAMPAÑA_INTERVENCION c
        JOIN CENTRO_VETERINARIO cv ON c.idCentroVet = cv.idCentroVet
        JOIN COLONIA_FELINA col ON c.idColonia = col.idColonia
        JOIN TIPO t ON c.idTipo = t.idTipo
        WHERE c.fechaFin IS NULL OR c.fechaFin >= CURDATE()";

$res = mysqli_query($con, $sql);
addBreadcrumb('Campañas Activas');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Campañas Veterinarias</title>
    <style>
        body, table {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f4f4f4; color: #333; }
        .btn-accion { background: #007bff; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold;}
        .btn-accion:hover {background-color: #0056b3;}
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    <div style="padding: 20px;">
        <h2>Campañas de Intervención Activas</h2>
        <table>
            <tr>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Colonia</th>
                <th>Inicio</th>
                <th>Acciones</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['tipoCampaña']); ?></td>
                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($row['colonia']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['fechaInicio'])); ?></td>
                <td>
                    <?php if (isset($_SESSION['idRol']) && $_SESSION['idRol'] == 4): // SOLO VETERINARIOS ?>
                        <a href="registrar_intervencion.php?idCamp=<?php echo $row['idCampaña']; ?>&idCol=<?php echo $row['idColonia']; ?>" class="btn-accion">
                            Registrar Intervención
                        </a>
                    <?php else: ?>
                        <span style="color: #666; font-style: italic;">Solo lectura</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>