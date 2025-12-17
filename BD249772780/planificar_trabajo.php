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

// Obtener idVoluntario del responsable
$idPersona = $_SESSION['idPersona'];
$sqlVol = "SELECT idVoluntario, idGrupoTrabajo FROM VOLUNTARIO WHERE idPersona = $idPersona";
$resultVol = mysqli_query($con, $sqlVol);
$voluntario = mysqli_fetch_assoc($resultVol);

if (!$voluntario || !$voluntario['idGrupoTrabajo']) {
    die('Error: No tienes un grupo de trabajo asignado. <a href="../menu.php">Volver</a>');
}

$idGrupoTrabajo = $voluntario['idGrupoTrabajo'];

// Obtener colonias del grupo
$sqlColonias = "SELECT idColonia, nombre, numeroGatos FROM COLONIA_FELINA WHERE idGrupoTrabajo = $idGrupoTrabajo ORDER BY nombre";
$resultColonias = mysqli_query($con, $sqlColonias);

// Obtener voluntarios del grupo
$sqlVoluntarios = "SELECT v.idVoluntario, p.nombre, p.apellido 
                   FROM VOLUNTARIO v
                   INNER JOIN PERSONA p ON v.idPersona = p.idPersona
                   WHERE v.idGrupoTrabajo = $idGrupoTrabajo
                   ORDER BY p.nombre";
$resultVoluntarios = mysqli_query($con, $sqlVoluntarios);

// Obtener marcas de comida
$sqlMarcas = "SELECT idMarcaComida, nombre, calidad, caracteristicas, pesoPorGato FROM MARCACOMIDA ORDER BY nombre";
$resultMarcas = mysqli_query($con, $sqlMarcas);

// Añadir breadcrumb
addBreadcrumb('Planificar Trabajo');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planificar Trabajo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }
        .formulario {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }
        .campo {
            margin-bottom: 15px;
        }
        .campo label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .campo input,
        .campo select,
        .campo textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
        .campo textarea {
            resize: vertical;
            min-height: 80px;
        }
        .campo-info {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            margin-top: 10px;
            display: none;
        }
        .campo-info.visible {
            display: block;
        }
        .btn-submit {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border: 1px solid #333;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #555;
        }
        .btn-volver {
            display: inline-block;
            margin-top: 20px;
            color: #333;
            text-decoration: none;
        }
        .btn-volver:hover {
            text-decoration: underline;
        }
        .info-calculo {
            background-color: white;
            border: 1px solid #333;
            padding: 15px;
            margin-top: 20px;
        }
        .info-calculo strong {
            color: #333;
        }
    </style>
</head>
<body>
    <?php displayBreadcrumbs(); ?>
    
    <div class="container">
        <h1>Planificar Trabajo de Alimentación</h1>
        <p class="subtitle">Asigna tareas de alimentación a los voluntarios de tu grupo</p>

        <?php if (isset($_GET['error'])): ?>
            <div style="background-color: white; border: 1px solid #333; color: #333; padding: 12px; margin-bottom: 20px;">
                Error: <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['exito'])): ?>
            <div style="background-color: white; border: 1px solid #333; color: #333; padding: 12px; margin-bottom: 20px;">
                Se han creado <?php echo intval($_GET['exito']); ?> trabajos correctamente.
            </div>
        <?php endif; ?>

        <form action="procesar_trabajo.php" method="POST" class="formulario">
            <div class="campo">
                <label for="idColonia">Colonia:</label>
                <select name="idColonia" id="idColonia" required>
                    <option value="">-- Selecciona colonia --</option>
                    <?php while ($col = mysqli_fetch_assoc($resultColonias)): ?>
                        <option value="<?php echo $col['idColonia']; ?>" data-gatos="<?php echo $col['numeroGatos']; ?>">
                            <?php echo htmlspecialchars($col['nombre']); ?> (<?php echo $col['numeroGatos']; ?> gatos)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo">
                <label for="idVoluntario">Voluntario asignado:</label>
                <select name="idVoluntario" id="idVoluntario" required>
                    <option value="">-- Selecciona voluntario --</option>
                    <?php while ($vol = mysqli_fetch_assoc($resultVoluntarios)): ?>
                        <option value="<?php echo $vol['idVoluntario']; ?>">
                            <?php echo htmlspecialchars($vol['nombre'] . ' ' . $vol['apellido']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo">
                <label for="fechaInicio">Fecha inicio:</label>
                <input type="date" name="fechaInicio" id="fechaInicio" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="campo">
                <label for="fechaFin">Fecha fin:</label>
                <input type="date" name="fechaFin" id="fechaFin" required min="<?php echo date('Y-m-d'); ?>">
                <small style="color: #666;">Por defecto se asigna una semana (7 días)</small>
            </div>

            <div class="campo">
                <label for="hora">Hora:</label>
                <input type="time" name="hora" id="hora" required>
            </div>

            <div class="campo">
                <label for="idMarcaComida">Marca de comida:</label>
                <select name="idMarcaComida" id="idMarcaComida" required>
                    <option value="">-- Selecciona marca --</option>
                    <?php while ($marca = mysqli_fetch_assoc($resultMarcas)): ?>
                        <option value="<?php echo $marca['idMarcaComida']; ?>" 
                                data-peso="<?php echo $marca['pesoPorGato']; ?>"
                                data-calidad="<?php echo htmlspecialchars($marca['calidad']); ?>"
                                data-caracteristicas="<?php echo htmlspecialchars($marca['caracteristicas']); ?>">
                            <?php echo htmlspecialchars($marca['nombre']); ?> (<?php echo $marca['calidad']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <div id="infoMarca" class="campo-info"></div>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción (opcional):</label>
                <textarea name="descripcion" id="descripcion" placeholder="Añade detalles sobre el trabajo..."></textarea>
            </div>

            <div class="info-calculo" id="infoCalculo" style="display: none;">
                <strong>Cálculo automático de cantidad:</strong><br>
                <span id="calculoDetalle"></span><br>
                <strong style="font-size: 18px; color: #333;">Total: <span id="cantidadTotal">0</span> gramos</strong>
            </div>

            <button type="submit" class="btn-submit">Asignar Trabajo</button>
        </form>

        <a href="../menu.php" class="btn-volver">Volver al menú</a>
    </div>

    <script>
    // Establecer fechaFin por defecto (7 días después de fechaInicio)
    document.getElementById('fechaInicio').addEventListener('change', function() {
        var fechaInicio = new Date(this.value);
        if (!isNaN(fechaInicio)) {
            var fechaFin = new Date(fechaInicio);
            fechaFin.setDate(fechaFin.getDate() + 7);
            var fechaFinStr = fechaFin.toISOString().split('T')[0];
            document.getElementById('fechaFin').value = fechaFinStr;
            document.getElementById('fechaFin').min = this.value;
        }
    });

    // Calcular cantidad de comida automáticamente
    function calcularCantidad() {
        var coloniaSelect = document.getElementById('idColonia');
        var marcaSelect = document.getElementById('idMarcaComida');
        var infoCalculo = document.getElementById('infoCalculo');
        var calculoDetalle = document.getElementById('calculoDetalle');
        var cantidadTotal = document.getElementById('cantidadTotal');

        if (coloniaSelect.value && marcaSelect.value) {
            var selectedColonia = coloniaSelect.options[coloniaSelect.selectedIndex];
            var selectedMarca = marcaSelect.options[marcaSelect.selectedIndex];
            
            var numeroGatos = parseInt(selectedColonia.getAttribute('data-gatos'));
            var pesoPorGato = parseInt(selectedMarca.getAttribute('data-peso'));
            var totalGramos = numeroGatos * pesoPorGato;

            calculoDetalle.textContent = numeroGatos + ' gatos × ' + pesoPorGato + ' g/gato';
            cantidadTotal.textContent = totalGramos;
            infoCalculo.style.display = 'block';
        } else {
            infoCalculo.style.display = 'none';
        }
    }

    // Mostrar info de marca seleccionada
    document.getElementById('idMarcaComida').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        var infoMarca = document.getElementById('infoMarca');
        
        if (this.value) {
            var calidad = selected.getAttribute('data-calidad');
            var caracteristicas = selected.getAttribute('data-caracteristicas');
            infoMarca.innerHTML = '<strong>Calidad:</strong> ' + calidad + '<br><strong>Características:</strong> ' + caracteristicas;
            infoMarca.classList.add('visible');
        } else {
            infoMarca.classList.remove('visible');
        }
        
        calcularCantidad();
    });

    document.getElementById('idColonia').addEventListener('change', calcularCantidad);
    </script>
</body>
</html>

<?php
mysqli_close($con);
?>
