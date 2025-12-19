<?php
// header.php - Cabecera común con breadcrumbs

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar el historial de navegación si no existe
if (!isset($_SESSION['breadcrumbs'])) {
    $_SESSION['breadcrumbs'] = [];
}

// Verificar si se está navegando desde un breadcrumb
if (isset($_GET['breadcrumb_index'])) {
    $index = (int)$_GET['breadcrumb_index'];

    // Proteger valor mínimo
    if ($index < 1) {
        $index = 1;
    }

    // Recortar breadcrumbs para dejar como último el seleccionado
    $_SESSION['breadcrumbs'] = array_slice($_SESSION['breadcrumbs'], 0, $index);

    // Redirigir a la URL del breadcrumb seleccionado (el último tras el slice)
    $target = end($_SESSION['breadcrumbs']);
    $targetUrl = isset($target['url']) ? $target['url'] : $_SERVER['PHP_SELF'];

    // Asegurarnos de quitar el parámetro breadcrumb_index si quedó en la URL de destino
    $parsed = parse_url($targetUrl);
    $base = $parsed['path'] ?? $targetUrl;
    $params = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $params);
        unset($params['breadcrumb_index']);
    }
    $redirectUrl = $base;
    if (!empty($params)) {
        $redirectUrl .= '?' . http_build_query($params);
    }

    header("Location: $redirectUrl");
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
    
    // Verificar si ya existe un breadcrumb con el MISMO TÍTULO
    // Si existe, eliminar todo desde ese punto y más allá
    foreach ($_SESSION['breadcrumbs'] as $index => $crumb) {
        if ($crumb['titulo'] === $titulo) {
            // Encontramos un breadcrumb con el mismo título
            // Mantener solo los breadcrumbs hasta este (sin incluirlo)
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
