<?php
// header.php - Cabecera común con breadcrumbs

// Inicializar el historial de navegación si no existe
if (!isset($_SESSION['breadcrumbs'])) {
    $_SESSION['breadcrumbs'] = [];
}

// Verificar si se está navegando desde un breadcrumb
if (isset($_GET['breadcrumb_index'])) {
    $index = (int)$_GET['breadcrumb_index'];
    // Eliminar todos los breadcrumbs después del seleccionado
    $_SESSION['breadcrumbs'] = array_slice($_SESSION['breadcrumbs'], 0, $index);
    
    // Redirigir a la URL sin el parámetro breadcrumb_index
    $url = $_SERVER['PHP_SELF'];
    $query = $_GET;
    unset($query['breadcrumb_index']);
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    header("Location: $url");
    exit;
}

// Función para añadir un breadcrumb
function addBreadcrumb($titulo, $url = null) {
    // Si no se proporciona URL, usar la actual
    if ($url === null) {
        $url = $_SERVER['PHP_SELF'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
    }
    
    // Limpiar la URL actual para comparar
    $urlActual = parse_url($url, PHP_URL_PATH);
    $queryActual = [];
    if (strpos($url, '?') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $queryActual);
    }
    
    // Verificar si ya existe esta página en el historial
    foreach ($_SESSION['breadcrumbs'] as $index => $crumb) {
        $urlCrumb = parse_url($crumb['url'], PHP_URL_PATH);
        $queryCrumb = [];
        if (strpos($crumb['url'], '?') !== false) {
            parse_str(parse_url($crumb['url'], PHP_URL_QUERY), $queryCrumb);
        }
        
        // Si la URL y los parámetros coinciden, eliminar todo lo que está después
        if ($urlCrumb === $urlActual && $queryCrumb == $queryActual) {
            $_SESSION['breadcrumbs'] = array_slice($_SESSION['breadcrumbs'], 0, $index);
            break;
        }
    }
    
    // Añadir el nuevo breadcrumb
    $_SESSION['breadcrumbs'][] = [
        'titulo' => $titulo,
        'url' => $url
    ];
    
    // Limitar a los últimos 10 elementos
    if (count($_SESSION['breadcrumbs']) > 10) {
        array_shift($_SESSION['breadcrumbs']);
    }
}

// Función para limpiar breadcrumbs (útil para el menú principal)
function resetBreadcrumbs() {
    $_SESSION['breadcrumbs'] = [];
}

// Función para mostrar los breadcrumbs
function displayBreadcrumbs() {
    if (empty($_SESSION['breadcrumbs'])) {
        return;
    }
    
    echo '<nav style="background-color: #f5f5f5; padding: 10px; margin-bottom: 20px; border-radius: 5px;">';
    echo '<div style="font-size: 14px;">';
    
    $total = count($_SESSION['breadcrumbs']);
    foreach ($_SESSION['breadcrumbs'] as $index => $crumb) {
        if ($index < $total - 1) {
            // No es el último, hacer enlace con parámetro breadcrumb_index
            $url = $crumb['url'];
            $separator = (strpos($url, '?') !== false) ? '&' : '?';
            $url .= $separator . 'breadcrumb_index=' . ($index + 1);
            
            echo '<a href="' . htmlspecialchars($url) . '" style="color: #0066cc; text-decoration: none;">';
            echo htmlspecialchars($crumb['titulo']);
            echo '</a>';
            echo ' <span style="color: #666;"> / </span> ';
        } else {
            // Es el último, mostrarlo sin enlace
            echo '<strong>' . htmlspecialchars($crumb['titulo']) . '</strong>';
        }
    }
    
    echo '</div>';
    echo '</nav>';
}
?>
