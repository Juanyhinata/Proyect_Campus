<?php
// includes/paths.php
// RUTAS ABSOLUTAS → funciona desde cualquier profundidad

define('ROOT_PATH', dirname(__DIR__));  // Sube un nivel desde includes/ → raíz del proyecto


// Tus CSS están directamente en la raíz
define('CSS_PATH', ROOT_PATH);          // ← aquí están tus .css
// Rutas útiles
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEW_PATH', ROOT_PATH . '/rol');  // porque las vistas están en rol/admin/view, rol/cliente/view, etc.
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');





?>